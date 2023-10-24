<?php

// Setup database and set content type to JSON
require_once(__DIR__ . "/../init.php");
header("Content-Type: application/json");

// Set general error
$database_error = "An error occured while trying to finish order.";

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


// Ensure PUT method
if ($_SERVER["REQUEST_METHOD"] !== "PUT" && $_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "PUT request is required."],
    ]);
    return;
}

// Get order ID
$data = json_decode(file_get_contents("php://input"), true);
$order_id = isset($data["orderId"]) ? (int) clean_data($data["orderId"]) : null;
if ($data === null || !$order_id) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "No order specified."],
    ]);
    return;
}

// Check if order exists
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
if (!$select_order_result || count($select_order_result) === 0) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "Order does not exist."],
    ]);
    return;
}

// Ensure it is not completed
if ($select_order_result["complete"]) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "Order is already completed."],
    ]);
    return;
}

// Complete order
$finish_order_query = "UPDATE `Order` SET complete = TRUE, order_date = NOW()
                    WHERE id = :order_id AND username = :username";
try {
    $stmt = $pdo->prepare($finish_order_query);
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

// Return response
echo json_encode([
    "ok" => true,
    "error" => "",
]);
