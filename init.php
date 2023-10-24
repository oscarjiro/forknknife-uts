<?php

// Require dependencies
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/utils.php");
require_once(__DIR__ . "/components.php");

// Start session
session_start();

// Get current route
$route = get_route();

// Reject access to this route
if ($route === "init.php") {
    header("Location: index.php");
}

// Connect to MySQL server
try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    redirect_error();
    $error_scope = "An error occured during connecting to database server.";
    return;
}

// Check if database exists
$check_db_query = "SELECT SCHEMA_NAME 
                FROM INFORMATION_SCHEMA.SCHEMATA
                WHERE SCHEMA_NAME = :db_name";
try {
    $stmt = $pdo->prepare($check_db_query);
    $stmt->bindValue(":db_name", DB_NAME, PDO::PARAM_STR);
    $stmt->execute();
} catch (PDOException $e) {
    redirect_error();
    $error_scope = "An error occured during setting up database.";
    return;
}

// Create database if it does not exist
if (!$db_exists = $stmt->rowCount() > 0) {
    $create_db_query = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    try {
        $stmt = $pdo->prepare($create_db_query);
        $stmt->execute();
    } catch (PDOException $e) {
        redirect_error();
        $error_scope = "An error occured during setting up database.";
        return;
    }
}

// Reconnect to the database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    redirect_error();
    $error_scope = "An error occured during connecting to database.";
    return;
}

// Create table User if it does not exist
$create_user_query = "CREATE TABLE IF NOT EXISTS User (
                        username VARCHAR(" . USERNAME_MAX_LENGTH . ") PRIMARY KEY NOT NULL,
                        email VARCHAR(" . EMAIL_MAX_LENGTH . ") NOT NULL UNIQUE,
                        first_name VARCHAR(" . FIRST_NAME_MAX_LENGTH . ") NOT NULL,
                        last_name VARCHAR(" . LAST_NAME_MAX_LENGTH . ") NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        is_admin BOOLEAN NOT NULL DEFAULT FALSE,
                        gender CHAR(1) NOT NULL CHECK (gender IN ('M', 'F')),
                        reset_token_hash VARCHAR(64) UNIQUE,
                        reset_token_expires_at DATETIME,
                        CONSTRAINT validate_username CHECK (
                            username REGEXP :username_regexp
                        ),
                        CONSTRAINT validate_email CHECK (
                            email REGEXP :email_regexp
                        )
                    )";
try {
    $stmt = $pdo->prepare($create_user_query);
    $stmt->bindValue(":username_regexp", trim(USERNAME_REGEXP, "/"), PDO::PARAM_STR);
    $stmt->bindValue(":email_regexp", trim(EMAIL_REGEXP, "/"), PDO::PARAM_STR);
    $stmt->execute();
} catch (PDOException $e) {
    redirect_error();
    $error_scope = "An error occured during creating table User.";
    return;
}

// Create table Menu
$valid_categories_list = array_map(function ($category) {
    return "'$category'";
}, MENU_CATEGORIES);
$valid_categories_string = implode(", ", $valid_categories_list);
$create_menu_query = "CREATE TABLE IF NOT EXISTS Menu (
                        id INTEGER PRIMARY KEY AUTO_INCREMENT,
                        name VARCHAR(" . MENU_NAME_MAX_LENGTH . ") NOT NULL,
                        description VARCHAR(" . MENU_DESCRIPTION_MAX_LENGTH . ") NOT NULL,
                        category VARCHAR(25) NOT NULL CHECK (category IN (" . $valid_categories_string . ")),
                        price DECIMAL(10, 2) NOT NULL CHECK (price > 0),
                        image_name VARCHAR(255) NOT NULL UNIQUE
                    )";
try {
    $stmt = $pdo->prepare($create_menu_query);
    $stmt->execute();
} catch (PDOException $e) {
    redirect_error();
    $error_scope = "An error occured during creating table Menu.";
    return;
}

// Create table Order
$create_order_query = "CREATE TABLE IF NOT EXISTS `Order` (
                        id INTEGER PRIMARY KEY AUTO_INCREMENT,
                        username VARCHAR(" . USERNAME_MAX_LENGTH . ") NOT NULL,
                        order_date DATETIME NOT NULL DEFAULT NOW(),
                        complete BOOLEAN NOT NULL DEFAULT FALSE,
                        CONSTRAINT fk_order_user FOREIGN KEY (username) REFERENCES User (username) ON DELETE CASCADE
                    )";
try {
    $stmt = $pdo->prepare($create_order_query);
    $stmt->execute();
} catch (PDOException $e) {
    redirect_error();
    $error_scope = "An error occured during creating table Order.";
    return;
}

// Create table OrderDetails
$create_orderdetails_query = "CREATE TABLE IF NOT EXISTS OrderDetails (
                                    id INTEGER PRIMARY KEY AUTO_INCREMENT,
                                    order_id INTEGER NOT NULL,
                                    menu_id INTEGER NOT NULL,
                                    quantity INTEGER NOT NULL CHECK (quantity > 0),
                                    CONSTRAINT fk_orderdetails_order FOREIGN KEY (order_id) REFERENCES `Order` (id) ON DELETE CASCADE,
                                    CONSTRAINT fk_orderdetails_menu FOREIGN KEY (menu_id) REFERENCES Menu (id) ON DELETE CASCADE
                            )";
try {
    $stmt = $pdo->prepare($create_orderdetails_query);
    $stmt->execute();
} catch (PDOException $e) {
    redirect_error();
    $error_scope = "An error occured during creating table OrderDetails.";
    return;
}

// Throw to login if not authenticated
if (!is_authenticated()) logout();

// Otherwise, ensure user exists
else {
    try {
        $check_user_query = "SELECT * FROM User
                            WHERE username = :username";
        $stmt = $pdo->prepare($check_user_query);
        $stmt->bindParam(":username", $_SESSION["username"], PDO::PARAM_STR);
        $stmt->execute();
    } catch (PDOException $e) {
        redirect_error();
        $error_scope = "An error occured during authenticating user.";
        return;
    }

    // If user does not exist, throw to login
    if ($stmt->rowCount() === 0) logout();
}

// If authenticated but in non authenticated page, throw to index
if (is_authenticated() && in_array($route, UNAUTHENTICATED_ROUTES)) {
    header("Location: index.php");
    exit;
}
