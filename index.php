<?php

require_once(__DIR__ . "/init.php");

// Get session username
$is_authenticated = is_authenticated();
$username = (isset($_SESSION["username"]) && $_SESSION["username"]) ? $_SESSION["username"] : null;
$is_admin = isset($_SESSION["is_admin"]) ? $_SESSION["is_admin"] : false;

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?= head(($is_admin ? "Admin " : "") . "Home") ?>
    <script src="static/scripts/index.js" type="module"></script>
</head>

<body>
    <!-- Navbar -->
    <?= navbar($is_authenticated, "index", $is_admin) ?>

    <!-- Main -->
    <main class="flex flex-col w-full space-y-10 opacity-0">
        <!-- Slogan -->
        <div class="flex flex-col justify-center space-y-12 w-full relative px-8 min-[450px]:px-12 py-6 min-[400px]:py-12 min-[500px]:py-16 min-[600px]:py-20 overflow-hidden rounded-xl">
            <div class="w-full h-full absolute top-0 left-0 z-[-1]">
                <img src="static/index.jpg" alt="Fork & Knife Concept Art" class="w-full h-full object-cover brightness-[0.6]">
            </div>
            <div class="nav-logo-text block greeting text-[rgb(var(--white-rgb))]">
                <?php
                if (!$is_authenticated) {
                ?>
                    indulge in the <br> art of <span class="serif italic font-extralight tracking-tighter">flavor</span>
                <?php
                } else {
                ?>
                    <span class=""> <?= greet() ?>,</span> <span class="serif italic font-extralight tracking-tighter"><?= $username ?></span>
                <?php
                }
                ?>
            </div>
            <div class="flex flex-col space-y-4 min-[700px]:flex-row min-[700px]:space-y-0 min-[700px]:space-x-4 min-[700px]:items-center min-[600px]:text-base min-[500px]:text-sm text-xs">
                <?php
                if (!$is_authenticated) {
                ?>
                    <a href="login.php">
                        <button class="button-white-active w-full min-[700px]:w-[200px] min-[900px]:w-[300px]">
                            Start ordering
                        </button>
                    </a>
                    <a href="#menu">
                        <button class="button-white w-full min-[700px]:w-[200px] min-[900px]:w-[300px]">
                            View menu
                        </button>
                    </a>
                <?php
                } else {
                ?>
                    <a href="#menu">
                        <button class="button-white w-full min-[700px]:w-[400px] min-[900px]:w-[600px]">
                            View menu
                        </button>
                    </a>
                <?php
                }
                ?>
            </div>
        </div>

        <?php
        if ($is_authenticated && $is_admin) {
        ?>
            <!-- Add -->
            <a href="add.php" class="add group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="add-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <div class="add-text">Add new menu</div>
            </a>
        <?php
        }
        ?>

        <!-- Section -->
        <section id="menu">
            <!-- Section Titles -->
            <div class="section-title-ctr">
                <div class="section-title-bg"></div>
                <div class="section-title-item-ctr">
                    <div id="allSection" class="section-title-item-active group" data-value="true">
                        <div id="allSectionCount" class="section-title-count-active">0</div>
                        <div class="section-title-text">All items</div>
                    </div>
                    <?php
                    foreach (MENU_CATEGORIES as $category) {
                    ?>
                        <div id="<?= to_camel_case($category) . "Section" ?>" class="section-title-item group" data-value="false">
                            <div id="<?= to_camel_case($category) ?>SectionCount" class="section-title-count">0</div>
                            <div class="section-title-text"><?= $category ?></div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>

            <!-- Menu -->
            <div id="menuContainer" class="menu-container">
            </div>
        </section>
    </main>
</body>

</html>