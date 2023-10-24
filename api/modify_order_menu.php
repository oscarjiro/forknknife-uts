<?php

// Setup database and set content type to JSON
require_once(__DIR__ . "/../init.php");
header("Content-Type: application/json");

// Set general error
$database_error = "An error occured while trying to modify a menu item.";

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
        "error" => ["message" => "Only users can delete a menu item."],
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

// Get OrderDetailsId and action
$data = json_decode(file_get_contents("php://input"), true);
$order_details_id = isset($data["orderDetailsId"]) ? (int) clean_data($data["orderDetailsId"]) : null;
$action = isset($data["action"]) ? clean_data($data["action"]) : null;
if ($data === null || !$order_details_id | !$action) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "No order menu or action specified."],
    ]);
    return;
}

// Ensure valid action
if ($action !== "add" && $action !== "minus" && $action !== "delete") {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "Unidentified action specified."],
    ]);
    return;
}

// Check if OrderDetails exists
$select_order_details_query = "SELECT * FROM OrderDetails 
                            WHERE id = :order_details_id";
try {
    $stmt = $pdo->prepare($select_order_details_query);
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
$select_order_details_result = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$select_order_details_result || count($select_order_details_result) === 0) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "Ordered menu does not exist."],
    ]);
    return;
}

// Get quantity
$quantity = $select_order_details_result["quantity"];

// Add quantity by 1 if action is add
if ($action === "add") {
    $add_query = "UPDATE OrderDetails 
                SET quantity = quantity + 1
                WHERE id = :order_details_id";
    try {
        $stmt = $pdo->prepare($add_query);
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

// Reduce quantity by if action is minus and quantity is greater than 1
else if ($quantity > 1 && $action === "minus") {
    $minus_query = "UPDATE OrderDetails 
                    SET quantity = quantity - 1
                    WHERE id = :order_details_id";
    try {
        $stmt = $pdo->prepare($minus_query);
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

// Delete OrderDetails otherwise 
else {
    $delete_order_details_query = "DELETE FROM OrderDetails 
                                WHERE id = :order_details_id";
    try {
        $stmt = $pdo->prepare($delete_order_details_query);
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

    // Check if there are still OrderDetails within the same order
    $order_id = $select_order_details_result["order_id"];
    $check_order_details_query = "SELECT * FROM OrderDetails 
                                WHERE order_id = :order_id";
    try {
        $stmt = $pdo->prepare($check_order_details_query);
        $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
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

    // If there are none anymore, delete Order parent
    if (!$check_order_details_result || count($check_order_details_result) === 0) {
        $delete_order_query = "DELETE FROM `Order` 
                            WHERE id = :order_id";
        try {
            $stmt = $pdo->prepare($delete_order_query);
            $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
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
}

// Return response
echo json_encode([
    "ok" => true,
    "error" => "",
]);
