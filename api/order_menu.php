<?php

// Setup database and set content type to JSON
require_once(__DIR__ . "/../init.php");
header("Content-Type: application/json");

// Set general error
$database_error = "An error occured while trying to order a menu item.";

// Ensure authenticated
if (!is_authenticated()) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "User must be logged in."],
    ]);
    return;
}

// Ensure not admin
$username = $_SESSION["username"];
$is_admin = $_SESSION["is_admin"];
if ($is_admin) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "Only users can order a menu item."],
    ]);
    return;
}

// Ensure POST method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "POST request is required."],
    ]);
    return;
}

// Get menu ID, quantity, and order ID
$data = json_decode(file_get_contents("php://input"), true);
$menu_id = isset($data["menuId"]) ? (int) clean_data($data["menuId"]) : null;
$quantity = isset($data["quantity"]) ? (int) clean_data($data["quantity"]) : null;
$order_id = isset($data["orderId"]) ? (int) clean_data($data["orderId"]) : null;
if ($data === null || !$menu_id || !$quantity) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "No menu or quantity specified to order."],
    ]);
    return;
}

// Check if menu exists
$select_menu_query = "SELECT * FROM Menu 
                    WHERE id = :menu_id";
try {
    $stmt = $pdo->prepare($select_menu_query);
    $stmt->bindParam(":menu_id", $menu_id, PDO::PARAM_INT);
    $stmt->execute();
} catch (Exception $e) {
    echo json_encode([
        "ok" => false,
        "error" => [
            "scope" => $database_error,
            "message" => $e->getMessage()
        ],
    ]);
    return;
}
$select_menu_result = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$select_menu_result || count($select_menu_result) === 0) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "Menu does not exist."],
    ]);
    return;
}

// Check if order exists
if ($order_id) {
    $select_order_query = "SELECT * FROM `Order` 
                        WHERE id = :order_id
                        AND username = :username";
    try {
        $stmt = $pdo->prepare($select_order_query);
        $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();
    } catch (Exception $e) {
        echo json_encode([
            "ok" => false,
            "error" => [
                "scope" => $database_error,
                "message" => $e->getMessage()
            ],
        ]);
        return;
    }
    $select_order_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $order_exists = $select_order_result && count($select_order_result) > 0;

    // If order exists, ensure it is not completed
    if ($order_exists && $select_order_result["complete"]) {
        echo json_encode([
            "ok" => false,
            "error" => ["message" => "Cannot order from a completed order"],
        ]);
        return;
    }
} else {
    $order_exists = false;
}


// If order does not exist, find existing incomplete order
if (!$order_exists) {
    $find_order_query = "SELECT * FROM `Order`
                        WHERE complete = FALSE";
    try {
        $stmt = $pdo->prepare($find_order_query);
        $stmt->execute();
    } catch (Exception $e) {
        echo json_encode([
            "ok" => false,
            "error" => [
                "scope" => $database_error,
                "message" => $e->getMessage()
            ],
        ]);
        return;
    }
    $find_order_result = $stmt->fetch(PDO::FETCH_ASSOC);

    // If found, continue from existing order
    if ($find_order_result && count($find_order_result) > 0) {
        $order_id = $find_order_result["id"];
    }

    // Otherwise, create new order
    else {
        $insert_order_query = "INSERT INTO `Order` (username)
                            VALUES (:username)";
        try {
            $stmt = $pdo->prepare($insert_order_query);
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt->execute();
            $order_id = $pdo->lastInsertId();
        } catch (Exception $e) {
            echo json_encode([
                "ok" => false,
                "error" => [
                    "scope" => $database_error,
                    "message" => $e->getMessage()
                ],
            ]);
            return;
        }
    }
}

// Check if there have been an existing OrderDetails with the same menu
$check_order_details_query = "SELECT * FROM OrderDetails
                            WHERE order_id = :order_id AND menu_id = :menu_id";
try {
    $stmt = $pdo->prepare($check_order_details_query);
    $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
    $stmt->bindParam(":menu_id", $menu_id, PDO::PARAM_INT);
    $stmt->execute();
} catch (Exception $e) {
    echo json_encode([
        "ok" => false,
        "error" => [
            "scope" => $database_error,
            "message" => $e->getMessage()
        ],
    ]);
    return;
}
$check_order_details_result = $stmt->fetch(PDO::FETCH_ASSOC);

// If no result, create a new OrderDetails with specified menu item and quantity
if (!$check_order_details_result || count($check_order_details_result) === 0) {
    $insert_order_details_query = "INSERT INTO OrderDetails (order_id, menu_id, quantity)
                                VALUES (:order_id, :menu_id, :quantity)";
    try {
        $stmt = $pdo->prepare($insert_order_details_query);
        $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
        $stmt->bindParam(":menu_id", $menu_id, PDO::PARAM_INT);
        $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->execute();
    } catch (Exception $e) {
        echo json_encode([
            "ok" => false,
            "error" => [
                "scope" => $database_error,
                "message" => $e->getMessage()
            ],
        ]);
        return;
    }
}

// Otherwise, update from existing OrderDetails
else {
    $order_details_id = $check_order_details_result["id"];
    $update_order_details_query = "UPDATE OrderDetails
                                SET quantity = quantity + :quantity
                                WHERE id = :order_details_id";
    try {
        $stmt = $pdo->prepare($update_order_details_query);
        $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->bindParam(":order_details_id", $order_details_id, PDO::PARAM_INT);
        $stmt->execute();
    } catch (Exception $e) {
        echo json_encode([
            "ok" => false,
            "error" => [
                "scope" => $database_error,
                "message" => $e->getMessage()
            ],
        ]);
        return;
    }
}

// Return response
echo json_encode([
    "ok" => true,
    "error" => "",
]);
