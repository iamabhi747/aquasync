<?php
include_once 'helper.php';
requires(['username', 'password']);

// Check Credentials
$stmt = $conn->prepare("SELECT `id` FROM `users` WHERE `email` = ? AND `password` = ?");
$hash = hash('sha256', $password);
$stmt->bind_param("ss", $username, $hash);
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows == 0) {
    $res = new stdClass();
    $res->success = false;
    $res->code = 401;
    $res->message = "Invalid username or password";
    die(json_encode($res));
}

// Create session Token
$id = $result->fetch_assoc()['id'];
$token = hash('sha256', $id . $username . $hash . time());

// Update token in database
$stmt = $conn->prepare("UPDATE `users` SET `token` = ? WHERE `id` = ?");
$stmt->bind_param("si", $token, $id);
if (!$stmt->execute())
{
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
$res->message = "Login successful";
die(json_encode($res));
?>