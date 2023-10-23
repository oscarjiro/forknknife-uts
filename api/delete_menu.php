<?php

// Setup database and set content type to JSON
require_once(__DIR__ . "/../init.php");
header("Content-Type: application/json");

// Ensure authenticated
if (!is_authenticated()) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "User must be logged in."],
    ]);
    return;
}

// Ensure admin
$is_admin = $_SESSION["is_admin"];
if (!$is_admin) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "Only admins can delete a menu item."],
    ]);
    return;
}

// Ensure DELETE method
if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "DELETE request is required."],
    ]);
    return;
}

// Get menu ID
$data = json_decode(file_get_contents("php://input"), true);
$menu_id = isset($data["menuId"]) ? clean_data($data["menuId"]) : null;
if ($data === null || !$menu_id) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "No menu specified to delete."],
    ]);
    return;
}

// Check if menu exists
try {
    $select_menu_query = "SELECT * FROM Menu 
                        WHERE id = :menu_id";
    $stmt = $pdo->prepare($select_menu_query);
    $stmt->bindParam(":menu_id", $menu_id, PDO::PARAM_INT);
    $stmt->execute();
} catch (Exception $e) {
    echo json_encode([
        "ok" => false,
        "error" => [
            "scope" => "An error occured while trying to delete menu.",
            "message" => $e->getMessage()
        ],
    ]);
    return;
}

// Ensure menu exists
if (count($result = $stmt->fetch(PDO::FETCH_ASSOC)) === 0) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "Menu does not exist."],
    ]);
    return;
}

// Delete menu
try {
    $delete_menu_query = "DELETE FROM Menu 
                        WHERE id = :menu_id";
    $stmt = $pdo->prepare($delete_menu_query);
    $stmt->bindParam(":menu_id", $menu_id, PDO::PARAM_INT);
    $stmt->execute();
} catch (Exception $e) {
    echo json_encode([
        "ok" => false,
        "error" => [
            "scope" => "An error occured while trying to delete task.",
            "message" => $e->getMessage()
        ],
    ]);
    return;
}

echo json_encode([
    "ok" => true,
    "error" => "",
]);
