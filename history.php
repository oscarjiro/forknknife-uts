<?php

require_once(__DIR__ . "/init.php");

// Get session username and admin
$username = $_SESSION["username"];
$is_admin = $_SESSION["is_admin"];

// If admin, throw back to index
if ($is_admin) {
    header("Location: index.php");
    exit;
}

$query_success = true;
$all_orders_result = ["ongoing" => [], "completed" => []];

// Get all orders
$get_all_orders_query = "SELECT * FROM `Order`
                        WHERE username = :username";
try {
    $stmt = $pdo->prepare($get_all_orders_query);
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);
    $stmt->execute();
} catch (PDOException $e) {
    $query_success = false;
    $database_error = $e->getMessage();
}

$get_all_orders_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$non_empty_orders = $get_all_orders_result && count($get_all_orders_result) > 0;
if ($non_empty_orders) {
    foreach ($get_all_orders_result as $order) {
        // Create order result array
        $order_result = [
            "order_id" => $order["id"],
            "order_date" => $order["order_date"],
            "total_price" => 0,
            "total_quantity" => 0,
            "details" => [],
        ];

        // Get all order details
        $get_details_query = "SELECT * FROM OrderDetails
                            WHERE order_id = :order_id";
        try {
            $stmt = $pdo->prepare($get_details_query);
            $stmt->bindParam(":order_id", $order["id"], PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            $query_success = false;
            $database_error = $e->getMessage();
        }
        $get_details_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($get_details_result as $detail) {
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
                $query_success = false;
                $database_error = $e->getMessage();
            }

            // Get query result
            $select_item_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $subtotal = $select_item_result["price"] * $quantity;

            // Append result
            $order_result["total_price"] += $subtotal;
            $order_result["total_quantity"] += $quantity;
            $order_result["details"][] = [
                "id" => $menu_id,
                "name" => $select_item_result["name"],
                "description" => $select_item_result["description"],
                "category" => $select_item_result["category"],
                "price" => $select_item_result["price"],
                "quantity" => $quantity,
                "subtotal" => $subtotal,
                "image_name" => $select_item_result["image_name"],
                "order_details_id" => $order_details_id,
            ];
        }

        // Append order result array to all orders array
        $all_orders_result[$order["complete"] ? "completed" : "ongoing"][] = $order_result;
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?= head("Order History") ?>
    <script src="static/scripts/history.js" type="module"></script>
</head>

<body>
    <!-- Navbar -->
    <?= navbar(true, "history", false) ?>

    <!-- Main -->
    <main class="flex flex-col w-full space-y-10 opacity-0">
        <!-- Heading -->
        <section class="greeting">Order <span class="serif tracking-tighter font-bold italic">history</span></section>

        <!-- Database error -->
        <?php
        if (!$query_success) {
            echo system_error($database_error, ERROR["general"]);
            echo "
                        </main>
                    </body>

                </html>
            ";
        }
        ?>

        <!-- Active order -->
        <section class="space-y-3">
            <div class="text-general-header text-upperwide font-light">Ongoing order</div>
            <div class="flex flex-col space-y-4 w-full">
                <?php
                if (count($all_orders_result["ongoing"]) === 0) {
                ?>
                    <div class="text-sm font-light py-4
                                min-[300px]:text-base
                                min-[350px]:text-lg
                                min-[400px]:text-xl
                                min-[500px]:text-2xl">
                        No ongoing order. <a href="index.php" class="text-link">View our menu</a> to start ordering.
                    </div>
                <?php
                } else {
                ?>
                    <div class="flex 
                            space-y-4
                            flex-col min-[1000px]:flex-row min-[1000px]:space-y-0
                            min-[1000px]:items-center min-[1000px]:justify-between overflow-hidden
                            w-full rounded-xl bg-[rgb(var(--green-rgb))] 
                            text-[rgb(var(--white-rgb))]
                            p-4
                            min-[350px]:p-6 
                            min-[400px]:p-8
                            min-[500px]:p-10
                            min-[600px]:p-12
                            selection:bg-[rgb(var(--white-rgb))] 
                            selection:text-[rgb(var(--green-rgb))]">
                        <div class="space-y-4">
                            <div>
                                <div class="text-upperwide text-general-body">Total bill</div>
                                <div class="text-upperwide font-bold text-general-header"><?= to_currency($all_orders_result["ongoing"][0]["total_price"]) ?></div>
                            </div>
                            <div>
                                <div class="text-upperwide text-general-body">Total items</div>
                                <div class="text-upperwide font-bold text-general-header"><?= $all_orders_result["ongoing"][0]["total_quantity"] ?> item<?= $all_orders_result["ongoing"][0]["total_quantity"] > 1 ? "s" : "" ?></div>
                            </div>
                        </div>
                        <a href="checkout.php" class="flex items-center space-x-2 group">
                            <div class="text-general-header font-light smooth group-hover:opacity-80 group-hover:tracking-wide">View Checkout</div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="big-icon-general smooth group-hover:rotate-[-90deg] group-hover:opacity-80">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                            </svg>
                        </a>
                    </div>
                <?php
                }
                ?>
            </div>
        </section>

        <hr class="border-[rgb(var(--fg-rgb))]">

        <!-- Completed orders -->
        <section class="space-y-3">
            <div class="text-general-header text-upperwide font-light">Completed orders</div>
            <div class="flex flex-col space-y-4 w-full">
                <?php
                if (count($all_orders_result["completed"]) === 0) {
                ?>
                    <div class="text-sm font-light py-4
                                min-[300px]:text-base
                                min-[350px]:text-lg
                                min-[400px]:text-xl
                                min-[500px]:text-2xl">
                        No completed orders.
                    </div>
                    <?php
                } else {
                    foreach ($all_orders_result["completed"] as $completed_order) {
                    ?>
                        <div id="order<?= $completed_order["order_id"] ?>" class="flex 
                                    space-y-4
                                    flex-col 
                                    overflow-hidden
                                    w-full rounded-xl bg-[rgb(var(--fg-rgb))] 
                                    text-[rgb(var(--bg-rgb))]
                                    p-4
                                    min-[350px]:p-6 
                                    min-[400px]:p-8
                                    min-[500px]:p-10
                                    min-[600px]:p-12
                                    selection:bg-[rgb(var(--bg-rgb))] 
                                    selection:text-[rgb(var(--fg-rgb))]">
                            <div class="space-y-4">
                                <div>
                                    <div class="text-upperwide text-general-body">Total bill</div>
                                    <div class="text-upperwide font-bold text-general-header"><?= to_currency($completed_order["total_price"]) ?></div>
                                </div>
                                <div>
                                    <div class="text-upperwide text-general-body">Total items</div>
                                    <div class="text-upperwide font-bold text-general-header"><?= $completed_order["total_quantity"] ?> item<?= $completed_order["total_quantity"] > 1 ? "s" : "" ?></div>
                                </div>
                                <div>
                                    <div class="text-upperwide text-general-body">Order date</div>
                                    <div class="text-upperwide font-bold text-general-header"><?= date("j F Y g:i A", strtotime($completed_order["order_date"])) ?></div>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                }
                ?>
            </div>
        </section>
    </main>
</body>

</html>