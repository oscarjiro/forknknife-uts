<?php

// Reject access to this route
$route = basename($_SERVER["PHP_SELF"]);
if ($route === "config.php") {
    header("Location: index.php");
}

// Database 
define("DB_NAME", "forknknife_utslec_group3");
define("DB_HOST", "localhost");
define("DB_USERNAME", "root");
define("DB_PASSWORD", "");

// SMTP server
define("SMTP_HOST", "smtp.gmail.com");
define("SMTP_PORT", 587);
define("SMTP_USER", "oscarjiroj@gmail.com");
define("SMTP_PASSWORD", "skfvjpwjhkgvrdjj");

// ReCAPTCHA
define("RECAPTCHA_SITE_KEY", "6LeUHbsoAAAAABNc2yLiVLsWC7KeB1t1hz2xoWEA");
define("RECAPTCHA_SECRET_KEY", "6LeUHbsoAAAAADFlCv3QLHQu8jps_4JcJoA-dcDY");

// User constraints
define("USERNAME_MIN_LENGTH", 5);
define("USERNAME_MAX_LENGTH", 25);
define("USERNAME_REGEXP", "/^(?!.*[.]{2,})[a-z\d_\.]{" . (USERNAME_MIN_LENGTH - 1) . "," . (USERNAME_MAX_LENGTH - 1) . "}[a-z\d_]$/");
define("EMAIL_MAX_LENGTH", 255);
define("EMAIL_REGEXP", "/(?:[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/");
define("FIRST_NAME_MAX_LENGTH", 25);
define("LAST_NAME_MAX_LENGTH", 25);
define("PASSWORD_MIN_LENGTH", 8);
define("PASSWORD_REGEXP", "/^(?=.*[A-Z])(?=.*\d)(?=.*[~`!@#\$%^&*()_\-+={[}\]|:;\"'<,>.?\/]).{" . PASSWORD_MIN_LENGTH . ",}$/");
define("TOKEN_EXPIRY_LIMIT", 60 * 10);

// Menu constraints
define("MENU_NAME_MAX_LENGTH", 50);
define("MENU_DESCRIPTION_MAX_LENGTH", 150);
define("MENU_IMAGE_MAX_SIZE", 2 * 1024 * 1024);
define("MENU_CATEGORIES", [
    "Appetizers",
    "Main Courses",
    "Sandwiches & Burgers",
    "Soups & Salads",
    "Sides",
    "Desserts",
    "Beverages",
    "Coffee & Tea",
    "Alcoholic Drinks",
]);

// Order constraints
define("ISO_8601_DATE_REGEXP", "/^\d{4}-\d{2}-\d{2}$/");

// Error array
define("ERROR", [
    "username" => "Username must be between " . USERNAME_MIN_LENGTH . " and " . USERNAME_MAX_LENGTH . " characters inclusive and can only contain alphabets, numbers, underscores, and periods.",
    "first_name" => "First name must be at most " . FIRST_NAME_MAX_LENGTH . " characters long.",
    "last_name" => "Last name must be at most " . LAST_NAME_MAX_LENGTH . " characters long.",
    "email" => "Please provide a valid email address.",
    "image_type" => "Uploaded file must be an image.",
    "image_size" => "Uploaded image should not exceed " . MENU_IMAGE_MAX_SIZE / (1024 ** 2) . " MB.",
    "password" => "Password must be at least " . PASSWORD_MIN_LENGTH . " characters and must contain at least one uppercase letter, number, and special character.",
    "confirm_password" => "Password does not match.",
    "menu_name" => "Menu name must be at most " . MENU_NAME_MAX_LENGTH . " characters long.",
    "menu_price" => "Price must be larger than 0.",
    "menu_description" => "Menu description must be at most " . MENU_DESCRIPTION_MAX_LENGTH . " characters long.",
    "menu_category" => "Invalid menu category.",
    "gender" => "Gender must be either male or female.",
    "general" => "An error occured. Please try again.",
    "recaptcha" => "Please verify that you are not a robot.",
]);

// Routes
define("UNAUTHENTICATED_ROUTES", ["login.php", "register.php", "forgot-password.php", "reset-password.php"]);
define("API_ROUTES", ["delete_menu.php", "get_menu.php", "request_password_reset.php"]);
