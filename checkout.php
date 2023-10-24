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

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?= head("Checkout") ?>
    <script src="static/scripts/checkout.js" type="module"></script>
</head>

<body>
    <!-- Navbar -->
    <?= navbar(true, "checkout", false) ?>

    <!-- Main -->
    <main class="flex flex-col w-full space-y-10 opacity-0">
        <!-- Heading -->
        <section class="greeting">Ready to <span class="serif tracking-tighter italic font-bold">checkout?</span></section>

        <!-- Items -->
        <section id="menuItemsContainer" class="space-y-4">
            All items here
        </section>

        <!-- Total -->
        <div class="p-4
                    min-[300px]:p-5 
                    min-[350px]:p-6 
                    min-[400px]:p-8 
                    min-[500px]:p-10
                    min-[600px]:p-12 
                    space-y-8
                    flex flex-col
                    rounded-xl 
                    bg-[rgb(var(--green-rgb))] text-[rgb(var(--bg-rgb))]
                    selection:bg-[rgb(var(--bg-rgb))] selection:text-[rgb(var(--green-rgb))]">
            <div class="flex flex-col space-y-4 min-[1000px]:space-y-0 min-[1000px]:flex-row min-[1000px]:space-x-36">
                <div>
                    <div class="text-upperwide text-general-body">Total bill</div>
                    <div id="totalPrice" class="text-upperwide text-general-header font-bold">IDR 0.00</div>
                </div>
                <div>
                    <div class="text-upperwide text-general-body">Total items</div>
                    <div id="totalQuantity" class="text-upperwide text-general-header font-bold">0 item</div>
                </div>
            </div>
            <button id="confirmCheckout" class="button-white border-2 text-general-header min-[600px]:text-3xl p-4 hover:text-[rgb(var(--green-rgb))]">Confirm Checkout</button>
        </div>
    </main>
</body>

</html>