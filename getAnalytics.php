<?php
include_once 'helper.php';
deviceRequired();

$stmt = $conn->prepare("SELECT `analytics` FROM `devices` WHERE `id` = ?");
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
$analytics = $result['analytics'];

$res = new stdClass();
$res->success = true;
$res->code = 200;
$res->analytics = $analytics;
$res->message = "Analytics retrieved";
die(json_encode($res));
?>

<!--
    Analytics structure
    {
        "waterlevel_instance": [
            {
                "time": "12312131313",
                "value": 0.5
            }
        ],
        "waterlevel_day": [
            {
                "day": "01/01/2024",
                "value": [0.5, 0.6, 0.7, 0.8, 0.9, 0.8, 0.7]
            }
        ],
        "moisture_instance": [
            {
                "time": "12312131313",
                "value": 0.5
            }
        ],
        "moisture_day": [
            {
                "day": "01/01/2024",
                "value": [0.5, 0.6, 0.7, 0.8, 0.9, 0.8, 0.7]
            }
        ]
    }
-->