<?php
include_once 'helper.php';
requires(['token']);
loginRequired();

// Check if device already exists
$stmt = $conn->prepare("SELECT `id` FROM `devices` WHERE `token` = ?");
$stmt->bind_param("s", $token);
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // Update authto of device to current username
    $stmt = $conn->prepare("UPDATE `devices` SET `authto` = ? WHERE `token` = ?");
    $stmt->bind_param("ss", $username, $token);
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
        $res->message = "Device added";
        die(json_encode($res));
    }

    // Clear privious analytics
    // TODO
}
else
{
    // Insert new device
    $devicename = $username . "'s Device";
    $controls = "{}";
    $analytics = "{\"waterlevel_instance\":[],\"waterlevel_day\":[],\"moisture_instance\":[],\"moisture_day\":[]}";
    $stmt = $conn->prepare("INSERT INTO `devices` (`id`, `name`, `token`, `controls`, `analytics`, `authto`) VALUES (NULL, ?, ?, '', '', ?)");
    $stmt->bind_param("sss", $devicename, $token, $username);
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
        $res->message = "Device added";
        die(json_encode($res));
    }

    // Create Analytics file
    // TODO 
}
?>