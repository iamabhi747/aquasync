<?php
include_once 'helper.php';
deviceRequired();
requires(['moisture', 'waterlevel']);

// get analytics
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
$analytics = json_decode($analytics);

// update analytics
$now = time();

// waterlevel
$waterlevel_instance = $analytics->waterlevel_instance;
$waterlevel_day = $analytics->waterlevel_day;

if (count($waterlevel_instance) == 0)
{
    // create new waterlevel_instance with time
    $waterlevel_instance[] = array("time" => $now, "value" => $waterlevel);
}
else if (date("d/m/Y", $now) == date("d/m/Y", $waterlevel_instance[0]->time))
{
    // append waterlevel to waterlevel_instance with time
    $waterlevel_instance[] = array("time" => $now, "value" => $waterlevel);
}
else
{
    // summarize and move waterlevel_instance to waterlevel_day and create new empty waterlevel_instance
    // make value to be array of 24 values containing average of each hour
    $value = array();
    $i = 0;
    $l = count($waterlevel_instance);
    for ($hour=1; $hour<=24; $hour++)
    {
        $sum   = 0;
        $count = 0;
        while ($i < $l && date("H", $waterlevel_instance[$i]->time) == $hour)
        {
            $sum += $waterlevel_instance[$i]->value;
            $count++;
            $i++;
        }
        if ($count > 0)
            $value[] = $sum / $count;
        else
            $value[] = 0;
    }

    $waterlevel_day[] = array("day" => date("d/m/Y", $waterlevel_instance[0]->time), "value" => $value);
    $waterlevel_instance = array();
}


// moisture
$moisture_instance = $analytics->moisture_instance;
$moisture_day = $analytics->moisture_day;

if (count($moisture_instance) == 0)
{
    // create new moisture_instance with time
    $moisture_instance[] = array("time" => $now, "value" => $moisture);
}
else if (date("d/m/Y", $now) == date("d/m/Y", $moisture_instance[0]->time))
{
    // append moisture to moisture_instance with time
    $moisture_instance[] = array("time" => $now, "value" => $moisture);
}
else
{
    // summarize and move moisture_instance to moisture_day and create new empty moisture_instance
    // make value to be array of 24 values containing average of each hour
    $value = array();
    $i = 0;
    $l = count($moisture_instance);
    for ($hour=1; $hour<=24; $hour++)
    {
        $sum   = 0;
        $count = 0;
        while ($i < $l && date("H", $moisture_instance[$i]->time) == $hour)
        {
            $sum += $moisture_instance[$i]->value;
            $count++;
            $i++;
        }
        if ($count > 0)
            $value[] = $sum / $count;
        else
            $value[] = 0;
    }

    $moisture_day[] = array("day" => date("d/m/Y", $moisture_instance[0]->time), "value" => $value);
    $moisture_instance = array();
}

// update analytics
$analytics->waterlevel_instance = $waterlevel_instance;
$analytics->waterlevel_day = $waterlevel_day;
$analytics->moisture_instance = $moisture_instance;
$analytics->moisture_day = $moisture_day;

$analytics = json_encode($analytics);

$stmt = $conn->prepare("UPDATE `devices` SET `analytics` = ? WHERE `id` = ?");
$stmt->bind_param("si", $analytics, $device_id);
if (!$stmt->execute())
{
    $res = new stdClass();
    $res->success = false;
    $res->code = 500;
    $res->message = "Database error";
    die(json_encode($res));
}

$res = new stdClass();
$res->success = true;
$res->code = 200;
$res->message = "Analytics updated";
die(json_encode($res));
?>