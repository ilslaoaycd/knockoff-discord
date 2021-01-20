<?php
    session_start();
    ob_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user'])) {
        echo "logout";
        die();
    }
    
    $username = $_SESSION['user']['name'];
    $conf = json_decode(file_get_contents("./settings.json"), true);
    $rooms = json_decode(file_get_contents("./rooms.json"), true);
    
    $table = $_GET['r'];
    //database setup
    require "../Medoo.php";
    use Medoo\Medoo;

    $database = new Medoo([
        'database_type' => $conf["dbtype"],
        'database_name' => $conf["chatdb"],
        'server' => $conf["server"],
        'username' => $conf["dbusername"],
        'password' => $conf["dbpassword"]
    ]);
    // End database setup

    function findinarray($str, $array) {if(is_array($array)) {foreach ($array as $item) {if (strpos($str, $item) !== false) {return true;}}}}

    if ($rooms[$table]['type'] == "private") {
        $users = new Medoo([
            'database_type' => $conf["dbtype"],
            'database_name' => $conf["userdb"],
            'server' => $conf["server"],
            'username' => $conf["dbusername"],
            'password' => $conf["dbpassword"]
        ]);

        $roles = $users->get("users", "roles", ["username" => $username]);

        if (!findinarray($roles, $rooms[$table]['allow'])) {
            echo "modsonly";
            die();
        }
    }

    // -=-=-=-=-=-=-=-
    // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
    // -=-=-=-=-=-=-=-
    
    $fresh = $database->max($table, "time", ["type[!]" => 'deleted']);
    if (isset($_GET['recent'])) {
        $recent = $_GET['recent'];

        if ($fresh > $recent) {
            // Get all values greater than $recent
            $data = $database->select($table, [
                "type",
                "name", 
                "roles",
                "message", 
                "time",
                "id",
                "embeds"
            ], [
                "time[>]" => $recent,
                "type[!]" => 'deleted'
            ]);
        } else {
            echo "null";
            die();
        }
    } else if (isset($_GET['before']) && isset($_GET['limit'])) {
        $time = $_GET['before'];
        if ($time == "last") {
            $time = $fresh+1;
        }
        $limit = $_GET['limit'];
        // return [limit] amount of message data [before] time
        $data = $database->select($table, [
            "type",
            "name", 
            "roles",
            "message", 
            "time",
            "id",
            "embeds"
        ], [
            "ORDER" => ["time" => "DESC"],
            "time[<]" => $time,
            'LIMIT' => $limit,
            "type[!]" => 'deleted'
        ]);
    } else {
        echo "{Error: No Valid Parameters Set in $" . "_GET}";
        die();
    }

    echo json_encode($data);
    die();
?>