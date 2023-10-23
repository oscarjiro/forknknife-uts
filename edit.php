<?php

require_once(__DIR__ . "/init.php");

// Get session username and menu ID parameter
$username = $_SESSION["username"];
$is_admin = $_SESSION["is_admin"];
$param_menu_id = isset($_GET["id"]) ? (int) clean_data($_GET["id"]) : null;

// If no menu ID specified or not admin, return to index
if (!$param_menu_id || !$is_admin) {
    header("Location: index.php");
    exit;
}

// Get menu details
$select_query = "SELECT * FROM Menu
                WHERE id = :menu_id";
try {
    $stmt = $pdo->prepare($select_query);
    $stmt->bindParam(":menu_id", $param_menu_id, PDO::PARAM_INT);
    $stmt->execute();
} catch (PDOException $e) {
    header("Location: index.php");
    exit;
}

// Get result
$select_result = $stmt->fetch(PDO::FETCH_ASSOC);

// If empty, redirect to index
if (!$select_result || count($select_result) === 0) {
    header("Location: index.php");
    exit;
}

// If successful, get column values
$original_name = $select_result["name"];
$original_price = $select_result["price"];
$original_description = $select_result["description"];
$original_category = $select_result["category"];
$original_image_name = $select_result["image_name"];

$post_req = $_SERVER["REQUEST_METHOD"] === "POST";
if ($post_req) {
    // Collect POST data
    $name = clean_data($_POST["name"]);
    $price = clean_data($_POST["price"]);
    $description = $_POST["description"] ? clean_data($_POST["description"]) : null;
    $category = $_POST["category"];
    $image_not_empty = isset($_FILES["image"]) && strlen($_FILES["image"]["name"]) > 0;
    $image = $image_not_empty ? $_FILES["image"] : null;

    // Check form validity
    $valid_name = strlen($name) > 0 && strlen($name) <= MENU_NAME_MAX_LENGTH;
    $valid_price = $price && $price > 0;
    $valid_description = strlen($description) > 0 && strlen($description) <= MENU_DESCRIPTION_MAX_LENGTH;
    $valid_category = in_array($category, MENU_CATEGORIES);
    $image_change = $image ? true : false;
    $valid_image_type = $image ? strstr($image["type"], "image/") : null;
    $valid_image_size = $image ? $image["size"] <= MENU_IMAGE_MAX_SIZE : null;
    $valid_image = !$image_change  || ($valid_image_type && $valid_image_size);
    $valid_form = $valid_name && $valid_price && $valid_description && $valid_category && $valid_image;

    // Proceed to insert data if all is valid 
    if ($valid_form) {
        // Boolean
        $query_success = true;

        // Set picture name
        $image_name = $image_change ? $image["name"] : $original_image_name;

        // Insert data
        $update_query = "UPDATE 
                            Menu 
                        SET 
                            name = :name,
                            price = :price,
                            description = :description,
                            category = :category,
                            image_name = :image_name
                        WHERE
                            id = :id";
        try {
            $stmt = $pdo->prepare($update_query);
            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":price", $price, PDO::PARAM_INT);
            $stmt->bindParam(":description", $description, PDO::PARAM_STR);
            $stmt->bindParam(":category", $category, PDO::PARAM_STR);
            $stmt->bindParam(":image_name", $image_name, PDO::PARAM_STR);
            $stmt->bindParam(":id", $param_menu_id, PDO::PARAM_INT);
            $stmt->execute();

            // If image is changed
            if ($image_change) {
                // Remove old picture and save new one
                unlink(__DIR__ . "/static/menu_images/$original_image_name");
                move_uploaded_file($image["tmp_name"], __DIR__ . "/static/menu_images/$image_name");
            }
            // Redirect to home without cache
            header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1
            header("Pragma: no-cache"); // HTTP 1.0
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
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
    <?= head("Edit menu \"$original_name\"") ?>
    <script src="static/scripts/edit.js" type="module"></script>
</head>

<body>
    <!-- Navbar -->
    <?= navbar(true, null, true) ?>

    <!-- Main -->
    <main class="form-main opacity-0">
        <!-- Form -->
        <form id="editForm" action="edit.php?id=<?= $param_menu_id ?>" method="post" enctype="multipart/form-data">
            <!-- Heading -->
            <h1 class="form-header">
                Edit <u class="font-light"><?= $original_name ?></u>.
            </h1>

            <!-- Name -->
            <div class="input-ctr">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" spellcheck="false" value="<?= $original_name ?>">
                <?= ($post_req && !$valid_name) ? $name_error_element : "" ?>
            </div>

            <!-- Price -->
            <div class="input-ctr">
                <label for="price">Price</label>
                <input type="number" step="0.01" id="price" name="price" value="<?= $original_price ?>">
                <?= ($post_req && !$valid_price) ? $price_error_element : "" ?>
            </div>

            <!-- Description -->
            <div class="input-ctr space-y-3">
                <label for="description">Description</label>
                <div id="descriptionContainer" class="relative">
                    <textarea name="description" id="description" rows="3" spellcheck="false"><?= $original_description ?></textarea>
                    <div class="textarea-counter"><span id="textareaCount"><?= strlen($original_description) ?></span>/<?= MENU_DESCRIPTION_MAX_LENGTH ?></div>
                </div>
                <?= ($post_req && !$valid_description) ? $description_error_element : "" ?>
            </div>

            <!-- Image -->
            <div class="input-ctr">
                <label for="image">Image</label>
                <div class="prev-pict-ctr">
                    <div id="imagePreviewContainer" class="prev-pict group">
                        <div id="imageUploadText" class="prev-pict-upload-btn opacity-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="prev-pict-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15m0-3l-3-3m0 0l-3 3m3-3V15" />
                            </svg>
                            <div class="prev-pict-text">
                                Upload image
                            </div>
                        </div>
                        <img id="imagePreview" src="static/menu_images/<?= $original_image_name ?>" alt="" class="prev-pict-img">
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
                            <button id="categoryChoice<?= to_camel_case($category) ?>" type="button" data-value="<?= $category ?>" class="button-black<?= $category === $original_category ? "-active" : "" ?>">
                                <?= $category ?>
                            </button>
                    <?php
                        }
                    }
                    ?>
                </div>
                <input type="text" id="category" name="category" class="hidden" value="<?= $original_category ?>">
                <?= ($post_req && !$valid_category) ? $category_error_element : "" ?>
            </div>

            <!-- Submit -->
            <button type="submit" class="button-black-active">Edit menu</button>

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