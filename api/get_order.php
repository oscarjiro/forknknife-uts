<?php

// Setup database and set content type to JSON
require_once(__DIR__ . "/../init.php");
header("Content-Type: application/json");

// Set general error
$database_error = "An error occured while trying order a menu item.";
$default_result = [
    "orderId" => null,
    "totalPrice" => 0,
    "totalQuantity" => 0,
    "details" => [],
    "orderDate" => null,
];

// Ensure authenticated
if (!is_authenticated()) {
    echo json_encode([
        "ok" => false,
        "result" => $default_result,
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
        "result" => $default_result,
        "error" => ["message" => "Only users can order a menu item."],
    ]);
    return;
}

// Ensure GET method
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode([
        "ok" => false,
        "result" => $default_result,
        "error" => ["message" => "GET request is required."],
    ]);
    return;
}

// Get order ID
$order_id = isset($_GET["id"]) ? (int) clean_data($_GET["id"]) : null;
$order_date = null;

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
    } catch (PDOException $e) {
        echo json_encode([
            "ok" => false,
            "result" => $default_result,
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
            "result" => $default_result,
            "error" => ["message" => "Cannot order from a completed order"],
        ]);
        return;
    }
    $order_date = $select_order_result["order_date"];
} else {
    $order_exists = false;
}

// If order does not exist, get a new, incomplete one
if (!$order_exists) {
    $select_incomplete_order = "SELECT * FROM `Order` 
                            WHERE complete = FALSE
                            AND username = :username";
    try {
        $stmt = $pdo->prepare($select_incomplete_order);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();
    } catch (PDOException $e) {
        echo json_encode([
            "ok" => false,
            "result" => $default_result,
            "error" => [
                "scope" => $database_error,
            ],
        ]);
        return;
    }

    // If none, then return
    $select_incomplete_order_result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$select_incomplete_order_result || count($select_incomplete_order_result) === 0) {
        echo json_encode([
            "ok" => true,
            "result" => $default_result,
            "error" => "",
        ]);
        return;
    }

    // Otherwise, obtain order ID
    $order_id = $select_incomplete_order_result["id"];
    $order_date = $select_incomplete_order_result["order_date"];
}

// Get all order details from the order
$select_details_query = "SELECT * FROM OrderDetails 
                    WHERE order_id = :order_id";
try {
    $stmt = $pdo->prepare($select_details_query);
    $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
    $stmt->execute();
} catch (PDOException $e) {
    echo json_encode([
        "ok" => false,
        "result" => $default_result,
        "error" => [
            "scope" => $database_error,
            "message" => $e->getMessage()
        ],
    ]);
    return;
}

// Get result
$select_details_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$final_result = ["orderId" => $order_id, "totalPrice" => 0, "totalQuantity" => 0, "details" => [], "orderDate" => $order_date];

// Iterate through result
foreach ($select_details_result as $detail) {
    // Get each key
    $order_details_id = $detail["id"];
    $menu_id = $detail["menu_id"];
    $quantity = $detail["quantity"];

    // Query menu details
    $select_item_query = "SELECT * FROM Menu
                        WHERE id = :menu_id";
    try {
        $stmt = $pdo->prepare($select_item_query);
        $stmt->bindParam(":menu_id", $menu_id, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        echo json_encode([
            "ok" => false,
            "result" => ["orderId" => $order_id, "totalPrice" => 0, "totalQuantity" => 0, "details" => [], "orderDate" => $order_date],
            "error" => [
                "scope" => $database_error,
                "message" => $e->getMessage()
            ],
        ]);
        return;
    }

    // Get query result
    $select_item_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $subtotal = $select_item_result["price"] * $quantity;

    // Append result
    $final_result["totalPrice"] += $subtotal;
    $final_result["totalQuantity"] += $quantity;
    $final_result["details"][] = [
        "id" => $menu_id,
        "name" => $select_item_result["name"],
        "description" => $select_item_result["description"],
        "category" => $select_item_result["category"],
        "price" => $select_item_result["price"],
        "quantity" => $quantity,
        "subtotal" => $subtotal,
        "imageName" => $select_item_result["image_name"],
        "orderDetailsId" => $order_details_id,
    ];
}
// Return response
echo json_encode([
    "ok" => true,
    "result" => $final_result,
    "error" => "",
]);
