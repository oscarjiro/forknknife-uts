<?php

// Setup database and set content type to JSON
require_once(__DIR__ . "/../init.php");
header("Content-Type: application/json");

// Redirect if not POST method
if ($_SERVER["REQUEST_METHOD"] !== "POST" || is_authenticated()) {
    header("Location: /webprog/uts-lab/index.php");
    exit;
}

// Get posted email
$data = json_decode(file_get_contents("php://input"), true);
$email = isset($data["email"]) ? clean_data($data["email"]) : null;
if ($data === null || !$email) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => "No email specified."],
    ]);
    return;
}

// Check if user with email exists
$select_query = "SELECT * FROM User
                WHERE email = :email";
try {
    $stmt = $pdo->prepare($select_query);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
} catch (Exception $e) {
    echo json_encode([
        "ok" => false,
        "error" => [
            "scope" => "An error occured. Please try again.",
            "message" => $e->getMessage()
        ],
    ]);
    return;
}

// If user does not exist, simply return with no problem
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$result || count($result) === 0) {
    echo json_encode([
        "ok" => true,
        "error" => "",
    ]);
    return;
}

// Generate token
$token = bin2hex(random_bytes(16));
$token_hashed = hash("sha256", $token);
$token_expiry = date("Y-m-d H:i:s", time() + TOKEN_EXPIRY_LIMIT);

// Insert generated token and expiry time to user
$update_token_query = "UPDATE User
                    SET 
                        reset_token_hash = :reset_token_hash,
                        reset_token_expires_at = :reset_token_expires_at
                    WHERE
                        email = :email";
try {
    $stmt = $pdo->prepare($update_token_query);
    $stmt->bindParam(":reset_token_hash", $token_hashed, PDO::PARAM_STR);
    $stmt->bindParam(":reset_token_expires_at", $token_expiry, PDO::PARAM_STR);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
} catch (PDOException $e) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => $e->getMessage(), "scope" => "An error occured. Please try again."],
    ]);
    return;
}

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/../vendor/autoload.php";

// Set up SMTP
$mail = new PHPMailer(true);
$mail->isSMTP();

$mail->SMTPAuth = true;
$mail->Host = SMTP_HOST;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = SMTP_PORT;
$mail->Username = SMTP_USER;
$mail->Password = SMTP_PASSWORD;

$mail->isHtml(true);

// Configure mail
$mail->setFrom(SMTP_USER);
$mail->addAddress($email);
$mail->Subject = "fork & knife | Password Reset for {$result["username"]}";
$mail->Body = <<<END

Click <a href="http://localhost/webprog/uts-lab/reset-password.php?token=$token">here</a> to reset your password.

END;

// Send mail
try {
    $mail->send();
} catch (Exception $e) {
    echo json_encode([
        "ok" => false,
        "error" => ["message" => $mail->ErrorInfo, "scope" => "An error occured. Please try again."],
    ]);
    return;
}

echo json_encode([
    "ok" => true,
    "error" => "",
]);
