<?php

// Setup database and set content type to JSON
require_once(__DIR__ . "/../init.php");
header("Content-Type: application/json");

// Ensure GET method
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "GET request is required."],
    ]);
    return;
}
$is_admin = isset($_SESSION["is_admin"]) ? $_SESSION["is_admin"] : false;

// Get all tasks
try {
    $select_all_menu_query = "SELECT 
                                id,
                                name,
                                description,
                                category,
                                price,
                                image_name
                            FROM 
                                Menu
                            ORDER BY 
                                CASE
                                    WHEN category = 'Appetizers' THEN 1
                                    WHEN category = 'Main course' THEN 2
                                    WHEN category = 'Sandwiches & Burgers' THEN 3
                                    WHEN category = 'Soups & Salads' THEN 4
                                    WHEN category = 'Sides' THEN 5
                                    WHEN category = 'Desserts' THEN 6
                                    WHEN category = 'Beverages' THEN 7
                                    WHEN category = 'Coffee & Tea' THEN 8
                                    ELSE 9 
                                END";

    $stmt = $pdo->prepare($select_all_menu_query);
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

// Fetch all results
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return JSON data
echo json_encode([
    "ok" => true,
    "result" => $result,
    "isAdmin" => $is_admin,
    "isAuthenticated" => is_authenticated(),
    "error" => "",
]);
