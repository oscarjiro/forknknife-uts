<?php

require_once(__DIR__ . "/init.php");

// Get session username and is admin
$username = $_SESSION["username"];
$is_admin = $_SESSION["is_admin"];

// If not admin, throw back to index
if (!$is_admin) {
    header("Location: index.php");
    exit;
}

$post_req = $_SERVER["REQUEST_METHOD"] === "POST";
if ($post_req) {
    // Collect POST data
    $name = clean_data($_POST["name"]);
    $price = clean_data($_POST["price"]);
    $description = clean_data($_POST["description"]);
    $category = $_POST["category"];
    $image_not_empty = isset($_FILES["image"]) && strlen($_FILES["image"]["name"]) > 0;
    $image = $image_not_empty ? $_FILES["image"] : null;

    // Check form validity
    $valid_name = strlen($name) > 0 && strlen($name) <= MENU_NAME_MAX_LENGTH;
    $valid_price = $price && $price > 0;
    $valid_description = strlen($description) > 0 && strlen($description) <= MENU_DESCRIPTION_MAX_LENGTH;
    $valid_category = in_array($category, MENU_CATEGORIES);
    $valid_image_type = $image ? strstr($image["type"], "image/") : null;
    $valid_image_size = $image ? $image["size"] <= MENU_IMAGE_MAX_SIZE : null;
    $valid_image = $valid_image_type && $valid_image_size;
    $valid_form = $valid_name && $valid_price && $valid_description && $valid_category && $valid_image;

    // Proceed to insert data if all is valid 
    if ($valid_form) {
        // Boolean
        $query_success = true;

        // Insert data
        $insert_query = "INSERT INTO Menu 
                            (name, price, description, category, image_name)
                        VALUES 
                            (:name, :price, :description, :category, :image_name)";
        try {
            $stmt = $pdo->prepare($insert_query);
            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":price", $price, PDO::PARAM_STR);
            $stmt->bindParam(":description", $description, PDO::PARAM_STR);
            $stmt->bindParam(":category", $category, PDO::PARAM_STR);
            $stmt->bindParam(":image_name", $image["name"], PDO::PARAM_STR);
            $stmt->execute();

            // Save picture and return to index
            move_uploaded_file($image["tmp_name"], __DIR__ . "/static/menu_images/{$image["name"]}");
            header("Location: index.php");
        } catch (PDOException $e) {
            $query_success = false;
            $database_error = $e->getMessage();
        }
    } else {
        $name_error_element = error_message(
            is_empty($name)
                ? empty_error("Menu name")
                : ERROR["menu_name"],
            "name"
        );
        $price_error_element = error_message(
            is_empty($price)
                ? empty_error("Menu price")
                : ERROR["menu_price"],
            "price"
        );
        $description_error_element = error_message(
            is_empty($description)
                ? empty_error("Menu description")
                : ERROR["menu_description"],
            "description"
        );
        $category_error_element = error_message(
            is_empty($category)
                ? empty_error("Menu category")
                : ERROR["menu_category"],
            "category"
        );
        $image_error_element = error_message(
            !$image_not_empty
                ? empty_error("Menu image")
                : (!$valid_image_type
                    ? ERROR["image_type"]
                    : ERROR["image_size"]),
            "image"
        );
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?= head("Add a menu item") ?>
    <script src="static/scripts/add.js" type="module"></script>
</head>

<body>
    <!-- Navbar -->
    <?= navbar(true, "add", true) ?>

    <!-- Main -->
    <main class="form-main opacity-0">
        <!-- Form -->
        <form id="addForm" action="add.php" method="post" enctype="multipart/form-data">
            <!-- Heading -->
            <h1 class="form-header">
                Add a menu item.
            </h1>

            <!-- Name -->
            <div class="input-ctr">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" spellcheck="false">
                <?= ($post_req && !$valid_name) ? $name_error_element : "" ?>
            </div>

            <!-- Price -->
            <div class="input-ctr">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" step="0.01">
                <?= ($post_req && !$valid_price) ? $price_error_element : "" ?>
            </div>

            <!-- Description -->
            <div class="input-ctr space-y-3">
                <label for="description">Description</label>
                <div id="descriptionContainer" class="relative">
                    <textarea name="description" id="description" rows="3" spellcheck="false"></textarea>
                    <div class="textarea-counter"><span id="textareaCount">0</span>/<?= MENU_DESCRIPTION_MAX_LENGTH ?></div>
                </div>
                <?= ($post_req && !$valid_description) ? $description_error_element : "" ?>
            </div>

            <!-- Image -->
            <div class="input-ctr">
                <label for="image">Image</label>
                <div class="prev-pict-ctr">
                    <div id="imagePreviewContainer" class="prev-pict group">
                        <div id="imageUploadText" class="prev-pict-upload-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="prev-pict-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15m0-3l-3-3m0 0l-3 3m3-3V15" />
                            </svg>
                            <div class="prev-pict-text">
                                Upload image
                            </div>
                        </div>
                        <img id="imagePreview" src="" alt="" class="prev-pict-img hidden">
                    </div>
                    <div id="imageName" class="prev-pict-name hidden"></div>
                    <?= ($post_req && !$valid_image) ? $image_error_element : "" ?>
                </div>
                <input id="image" name="image" type="file">
            </div>

            <!-- Current category -->
            <div class="input-ctr">
                <label for="category">Category</label>
                <div id="categoryChoices" class="grid grid-cols-1 gap-2 min-[600px]:grid-cols-3 min-[600px]:gap-4">
                    <?php
                    foreach (MENU_CATEGORIES as $category) {
                        if ($category !== "Completed") {
                    ?>
                            <button id="categoryChoice<?= to_camel_case($category) ?>" type="button" data-value="<?= $category ?>" class="button-black">
                                <?= $category ?>
                            </button>
                    <?php
                        }
                    }
                    ?>
                </div>
                <input type="text" id="category" name="category" class="hidden">
                <?= ($post_req && !$valid_category) ? $category_error_element : "" ?>
            </div>

            <!-- Submit -->
            <button type="submit" class="button-black-active">Add menu</button>

            <!-- Form error message -->
            <?=
            ($post_req && $valid_form && !$query_success) ?
                ("<div class=\"text-center\">" .
                    error_message(ERROR["general"], "form") .
                    "</div>"
                ) : ""
            ?>
        </form>
    </main>
</body>