<?php
include_once 'helper.php';
requires(['controls']);
loginRequired();
deviceRequired();

// Check controls are valid
// TODO: Check controls are valid

// Check user has access to device
$stmt = $conn->prepare("SELECT `id` FROM `devices` WHERE `id` = ? AND `authto` = ?");
$stmt->bind_param("is", $device_id, $username);
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows == 0) {
    $res = new stdClass();
    $res->success = false;
    $res->code = 401;
    $res->message = "You do not have access to this device";
    die(json_encode($res));
}
else
{
    // Set controls of device
    $stmt = $conn->prepare("UPDATE `devices` SET `controls` = ? WHERE `id` = ?");
    $stmt->bind_param("si", $controls, $device_id);
    if (!$stmt->execute())
    {
        $res = new stdClass();
        $res->success = false;
        $res->code = 500;
        $res->message = "Database error";
        die(json_encode($res));
    }
    else
    {
        $res = new stdClass();
        $res->success = true;
        $res->code = 200;
        $res->message = "Controls set";
        die(json_encode($res));
    }
}
?>