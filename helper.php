<?php
header('Content-Type: application/json; charset=utf-8');
include_once 'database.php';

if ($conn->connect_error) {
    $res = new stdClass();
    $res->success = false;
    $res->code = 500;
    $res->message = "Database connection failed";
    die(json_encode($res));
}

function requires($args) {
    foreach ($args as $arg) {
        if (!isset($_GET[$arg])) {
            $res = new stdClass();
            $res->success = false;
            $res->code = 404;
            $res->message = "Missing argument '" . $arg . "'";
            die(json_encode($res));
        }
        $GLOBALS[$arg] = $_GET[$arg];
    }
}

function loginRequired() {
    requires(['auth']);

    $stmt = $GLOBALS['conn']->prepare("SELECT `id`,`email` FROM `users` WHERE `token` = ?");
    $stmt->bind_param("s", $GLOBALS['auth']);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        $res = new stdClass();
        $res->success = false;
        $res->code = 401;
        $res->message = "Authentication failed";
        die(json_encode($res));
    }

    $result = $result->fetch_assoc();
    $GLOBALS['user_id'] = $result['id'];
    $GLOBALS['username'] = $result['email'];

}

function deviceRequired() {
    requires(['token']);

    $stmt = $GLOBALS['conn']->prepare("SELECT `id` FROM `devices` WHERE `token` = ?");
    $stmt->bind_param("s", $GLOBALS['token']);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        $res = new stdClass();
        $res->success = false;
        $res->code = 401;
        $res->message = "Device not found";
        die(json_encode($res));
    }

    $GLOBALS['device_id'] = $result->fetch_assoc()['id'];
}

function loginORDeviceRequired() {
    if (isset($_GET['auth'])) {
        loginRequired();
        $GLOBALS['login'] = true;
    }
    else if (isset($_GET['token'])) {
        deviceRequired();
        $GLOBALS['login'] = false;
    }
    else {
        $res = new stdClass();
        $res->success = false;
        $res->code = 404;
        $res->message = "Missing argument 'auth' or 'token'";
        die(json_encode($res));
    }
}

?>