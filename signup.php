<?php
include_once 'helper.php';
requires(['name', 'email', 'password']);

// Check user exists
$stmt = $conn->prepare("SELECT `id` FROM `users` WHERE `email` = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows != 0) {
    $res = new stdClass();
    $res->success = false;
    $res->code = 409;
    $res->message = "User already exists";
    die(json_encode($res));
}

// Create user
$hash  = hash('sha256', $password);
$token = hash('sha256', $email . $hash . time());
$stmt  = $conn->prepare("INSERT INTO `users` (`name`, `email`, `password`, `token`) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hash, $token);
if (!$stmt->execute()) {
    $res = new stdClass();
    $res->success = false;
    $res->code = 500;
    $res->message = "Database error";
    die(json_encode($res));
}

// Return token
$res = new stdClass();
$res->success = true;
$res->code = 200;
$res->token = $token;
$res->message = "Signup successful";
die(json_encode($res));
?>