<?php
    session_start();
    if (!isset($_SESSION['user'])) {
        die();
    }
    $conf = json_decode(file_get_contents("./settings.json"), true);
    $rooms = json_decode(file_get_contents("./rooms.json"), true);
    //database setup
    require "../Medoo.php";
    use Medoo\Medoo;
    date_default_timezone_set('America/Denver');

    $database = new Medoo([
        'database_type' => $conf["dbtype"],
        'database_name' => $conf["chatdb"],
        'server' => $conf["server"],
        'username' => $conf["dbusername"],
        'password' => $conf["dbpassword"]
    ]);

    $users = new Medoo([
        'database_type' => $conf["dbtype"],
        'database_name' => $conf["userdb"],
        'server' => $conf["server"],
        'username' => $conf["dbusername"],
        'password' => $conf["dbpassword"]
    ]);

    // End database setup
    $status = "";
    $data = array();
    $username = $_SESSION['user']['name'];
    $raws = file_get_contents("php://input");
    $raw = nl2br(htmlspecialchars(trim($raws, " ")), false);   

    $table = $_GET['r'];

    function findinarray($str, $array) {
        if(is_array($array)) {
            foreach ($array as $item) {
                if (strpos($str, $item) !== false) {
                    return true;
                }
            }
        }
    }

    if ($rooms[$table]['type'] == "private") {
        $users = new Medoo([
            'database_type' => $conf["dbtype"],
            'database_name' => $conf["userdb"],
            'server' => $conf["server"],
            'username' => $conf["dbusername"],
            'password' => $conf["dbpassword"]
        ]);

        $roles = $users->get("users", "roles", ["username" => $_SESSION['user']['name']]);

        if (!findinarray($roles, $rooms[$table]['allow'])) {
            echo "<error>403 Moderators Only.</error>";
            die();
        }
    }

    if (strlen($raw) < 1) {
        $status = "Hey, @" . $username . " your message is too short.";
    }

    if (strlen($raw) > $conf["maxlength"]) {
        $status = "Hey, @" . $username . " your message is too long!<error>Exceeded maxlength set: ".$conf["maxlength"].".<br>Length: ".strlen($raw)."</error>";
    }

    function get_title($url){
        $str = file_get_contents($url);
        if (!$str) {
            return parse_url($url)['host'];
        } else {
            $title[1] == "";
            if(strlen($str)>0){
            $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
            preg_match("/\<title\>(.*)\<\/title\>/i",$str,$title); // ignore case
            if ($title[1] == "") {
                return "External Link";
            } else {
                return $title[1];
            }
            }
        }
    }

    function urllink($string) {
        $embeds = [];
        $reg_exUrl = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";
        if(preg_match_all($reg_exUrl, $string, $url)) {
            foreach($url[0] as $newLinks){
                if(strstr( $newLinks, ":" ) === false){
                    $link = 'http://'.$newLinks;
                }else{
                    $link = $newLinks;
                }
                $search  = $newLinks;
                $linkTitle = get_title($link);
                /* $string = str_replace($search, $replace, $string) .
                '<a class="linkbox" href="'.$link.'" title="'.$linkTitle.'"><span>' . mb_strimwidth($linkTitle, 0, 30, '...') . '</span><span>' . parse_url($link)['scheme'] . "://" . parse_url($link)['host'] . '/</span><img src="https://www.google.com/s2/favicons?domain=' . parse_url($link)['host'] . '&sz=64"></a>';
              */$newembed = ["type"=>"link",
                             "path"=>$link, 
                             "base"=>parse_url($link)['scheme'] . "://" . parse_url($link)['host'] . '/',
                             "title"=>$linkTitle,
                             "host"=>parse_url($link)['host']];
                array_push($embeds, $newembed);
            }
            return $embeds;
        }
    }

    function validatecsscolor($color){if(preg_match("/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/",$color)){return true;}else{return false;}}
    $crole = $users->get("users", "roles", ["username" => $username]);

    $allowrole = ["mod", "dev", "R.O.S."];
    $modcommands = ["sudo", "ban", "mod", "bot", "text", "remove"];
    $usercommands = ["color", "img"];

    $str = $raw;
    if (substr($str, 0, 1) === '/') {
        $split = explode(" ", $str);
        $split[0] = str_replace("/", "", $split[0]);
        $implode = $split;
        array_shift($implode);
        if (!isset($split[1]) && $split[0] != "help" && $split[0] != "debug") {
            $status = "/".$split[0]." requires at least one parameter.";
        } else {
            if (findinarray($split[0], $modcommands)) {
                if (findinarray($crole, $allowrole)) {
                    // Syntax: /bot [message]
                    if ($split[0] == "bot") {
                        $implode = implode(" ", $implode);
                        $data = array("type"=>$split[0], "name"=>"chat.bot", "message"=>$implode);
                    } else if ($split[0] == "mod") {
                        $implode = implode(" ", $implode);
                        $data = array("type"=>$split[0], "name"=>$username, "message"=>$implode);
                    } else if ($split[0] == "sudo") {
                        array_shift($implode);
                        $implode = implode(" ", $implode);
                        $data = array("type"=>"default", "name"=>$split[1], "message"=>$implode);
                        $status = "{keep}Shhh! Pretend you are " . $split[1] . ". Act natural!";
                    } else if ($split[0] == "ban") {
                        $banmsg = "@" . $split[1] . "'s account has been suspended by a moderator.";
                        $data =  array("type"=>"status", "message"=>$banmsg, "name"=>"chat.bot");
                        $status = "{keep}@" . $split[1] . " has been banned.";
                    } else if ($split[0] == "text") {
                        $implode = implode(" ", $implode);
                        $data =  array("type"=>"status", "message"=>$implode, "name"=>"chat.bot");
                    } else if ($split[0] == "remove") {
                        $database->update($table, [
                            "type" => "deleted"
                        ], [
                            "id" => intval($split[1])
                        ]);
                        $status = "Message with id '" . $split[1] . "' has been removed.";
                    } else {
                        // Something went wrong.
                        $status = "There was an error.<error>".$split[0]." did not match any known commands.</error>";
                    }
                } else {
                    $status = "You don't have permission to run that command!<error>User ".$username." has entered command /".$split[0].".<br>However, they are lacking the required permissions to run it.</error>";
                }
            } else if (findinarray($split[0], $usercommands)) {
                // Syntax: /color [#hexhex]
                if ($split[0] == "color") {
                    $color = $split[1];
                    if (validatecsscolor($color)) {
                        $status = "Your color has been updated to <span style='display:inline;color:".$color.";'>'".$color."'</span>";
                        $users->update("users", [
                            "chatcolor" => $color
                        ], [
                            "id" => $_SESSION['user']['id']
                        ]);
                    } else {
                        $status = htmlspecialchars($color) . " Was not detected as a hex color. Try picking a color from <a href='https://google.com/search?q=color+picker'>here</a> instead.";
                    }
                } else if ($split[0] == "img") {
                    $data = array("type"=>"img", "name"=>$username, "message"=>$split[1]);
                }
            } else if ($split[0] == "help") {
                $status = "<error><b>Help:</b>".PHP_EOL.
                "- <b>/help [command]</b> - Displays help.".PHP_EOL.
                "- <b>/color [#hexhex]</b> - Sets username color on chat.".PHP_EOL.
                "- <b>/debug</b> - Shows debug options.</error>".
                "<error>Our chat supports **<b>bold</b>** and *<i>itallic</i>* markdown.</error>";
                //mod help
                if (findinarray($crole, $allowrole)) {
                    $status .= "<error><b>Mods:</b>".PHP_EOL.
                            "- <b>/bot [message]</b> - Fakes a bot message.".PHP_EOL.
                            "- <b>/mod [message]</b> - Fakes a mod message.".PHP_EOL.
                            "- <b>/sudo [user] [message]</b> - Fakes any user message.".PHP_EOL.
                            "- <b>/ban [user]</b> - Bans a user.".PHP_EOL.
                            "- <b>/text [message]</b> - Displays a status message in chat.".PHP_EOL.
                            "- <b>/remove [message_id]</b> - Removes a message with an id.</error>";
                }
                $status = nl2br($status);
            } else if ($split[0] == "debug") {
                $temprolecolor = $users->get("users", "chatcolor", ["username" => $username]);
                $status = "<error><b>Debug Menu:</b>".PHP_EOL.
                "Client Ip: " . $_SERVER['REMOTE_ADDR'] .PHP_EOL.
                "Server Ip: " . $_SERVER['SERVER_ADDR'] .PHP_EOL.
                "Host: " . $_SERVER['REMOTE_HOST'] . PHP_EOL.
                "HTTPS: " . $_SERVER['HTTPS'] . PHP_EOL.
                "Username: " . $username.PHP_EOL.
                "Moderator: " . findinarray($crole, $allowrole).PHP_EOL.
                "Color: <span style='color:".$temprolecolor.";'>".$temprolecolor."</span></error>".PHP_EOL.
                "<debug><button onclick='window.location.reload();'>Reload</button><button onclick='location.href=" . '"/logout"' . ";'>Kill Session</button></debug><br><small>End debug statement</small>";
                $status = nl2br($status, false);
            } else {
                $status = "Please enter a valid command.<error>Unknown command: /".$split[0]."</error>";
            }
        }
    } else {
        $data = array("type"=>"default", "name"=>$username, "message"=>$str);
    }

    if ($status == "" || strpos($status, "{keep}") !== false) {
        $database->insert($table, array(
            'type' => $data['type'],
            'name' => $data['name'],
            'roles' => $users->get("users", "chatcolor", ["username" => $data['name']]),
            'message' => $data['message'],
            'time' => intval(microtime(true) * 1000), //date("g:i A m/j/y")
            'id' => uniqid(),
            'embeds' => base64_encode(json_encode(urllink($raw)))
        ));
        if (strpos($status, "{keep}") !== false) {
            $status = str_replace("{keep}", "", $status);
            echo $status;
        } else {
            echo "200";
        }
    } else {
        echo $status;
        die();
    }
    /*
    echo "An error occured while sending your message.<br><small style='width:100%;max-width:300px;padding:15px;background:#333;color:white;font-family:consolas;word-break:break-word;display:block;margin:10px 0px 0px;'><pre style='margin:0px;'>".
            "|- Error Data -|". PHP_EOL.
            print_r($data) . PHP_EOL .
            $errors . PHP_EOL .
            "|- End Error Data -|"
            ."</pre></small>";
            die();
            */
?>