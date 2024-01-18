<?php
include_once 'helper.php';
deviceRequired();

$stmt = $conn->prepare("SELECT `controls` FROM `devices` WHERE `id` = ?");
$stmt->bind_param("i", $device_id);
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows == 0) {
    $res = new stdClass();
    $res->success = false;
    $res->code = 500;
    $res->message = "Database error";
    die(json_encode($res));
}

$result = $result->fetch_assoc();
$controls = $result['controls'];

$res = new stdClass();
$res->success = true;
$res->code = 200;
$res->controls = $controls;
$res->message = "Controls retrieved";
die(json_encode($res));
?>