<?php

// Set default timezone
date_default_timezone_set("Asia/Jakarta");

// Get route
function get_route()
{
    return basename($_SERVER["PHP_SELF"]);
}

// Redirect to error page
function redirect_error()
{
    if (get_route() !== "error.php") header("Location: error.php");
}

// Check if authenticated
function is_authenticated()
{
    return
        isset($_SESSION["is_authenticated"]) && $_SESSION["is_authenticated"]
        && isset($_SESSION["username"]) && $_SESSION["username"]
        && isset($_SESSION["is_admin"]);
}

// Logout
function logout()
{
    $route = get_route();
    session_destroy();
    if ($route !== "index.php" && !in_array($route, UNAUTHENTICATED_ROUTES) && !in_array($route, API_ROUTES)) {
        header("Location: index.php");
        exit;
    } else {
        session_start();
    }
}

// Clean data
function clean_data($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data);
}

// Check if empty string
function is_empty($string)
{
    return strlen($string) === 0;
}

// Empty error message
function empty_error($name)
{
    $name = ucfirst($name);
    return "$name must be filled.";
}

// Convert to camel case
function to_camel_case($inputString)
{
    // Replace invalid characters with underscores
    $inputString = preg_replace('/[^a-zA-Z0-9]+/', '_', $inputString);

    // Replace spaces with underscores
    $inputString = str_replace(' ', '_', $inputString);

    // Convert to lowercase and remove underscores between words
    $inputString = strtolower($inputString);
    $inputString = str_replace('_', '', ucwords($inputString, '_'));
    return lcfirst($inputString);
}

// Greet according to time of day
function greet()
{
    $current_hour = date("H");
    if ($current_hour >= 5 && $current_hour < 12) {
        $greeting = "Wake up to delicious flavors";
    } elseif ($current_hour >= 12 && $current_hour < 18) {
        $greeting = "Lunchtime elegance awaits";
    } elseif ($current_hour >= 18 && $current_hour < 24) {
        $greeting = "Dine under the stars";
    } else {
        $greeting = "Wishing you a restful night";
    }
    return $greeting;
}

// Convert to currency
function to_currency($price)
{
    $formatted_price = number_format($price, 2, ',', '.');

    return 'IDR ' . $formatted_price;
}
