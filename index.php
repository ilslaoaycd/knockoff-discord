<?php 
session_start();
ob_start();
if (!isset($_SESSION['user'])) {
    header("Location: /login/?f=/chat");
    die();
}
$conf = json_decode(file_get_contents("./settings.json"), true);
$rooms = json_decode(file_get_contents("./rooms.json"), true);

require "../Medoo.php";
use Medoo\Medoo;

function findinarray($str, $array) {
    if(is_array($array)) {
        foreach ($array as $item) {
            if (strpos($str, $item) !== false) {
                return true;
            }
        }
    }
}

$users = new Medoo([
    'database_type' => $conf["dbtype"],
    'database_name' => $conf["userdb"],
    'server' => $conf["server"],
    'username' => $conf["dbusername"],
    'password' => $conf["dbpassword"]
]);

$roles = $users->get("users", "roles", ["username" => $_SESSION['user']['name']]);
?>
<!DOCTYPE html>
<html lang="en">
<!--
 ____________________
/ Cows Say Moo too.  \
\ Dev @isaachlloyd   /
 -------------------
        \   ^__^
         \  (oo)\_______
            (__)\       )\/\
                ||----w |
                ||     ||
-->
<head>
    <link rel="stylesheet" href="/style.css" />
    <meta name="google-site-verification" content="f_oWvk3n9DvqaxkT1WBM1Cl1obz0F09-zFwB25etnzA" />
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-144207380-1"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag() {dataLayer.push(arguments);}gtag("js", new Date());gtag("config", "UA-144207380-1");</script>
    <link href="https://fonts.googleapis.com/css?family=Amatic+SC&display=swap" rel="stylesheet" />
    <title>JTC - Chat</title>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicons/favicon-16x16.png">
    <link href="roles.css" rel="stylesheet" />
    <link rel="manifest" href="/assets/favicons/site.webmanifest">
    <link rel="mask-icon" href="/assets/favicons/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="/assets/favicons/favicon.ico">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="/assets/favicons/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <meta charset="UTF-8">
    <meta name="description" content="The chat page for jeff's website. Definatly not a discord knockoff!">
    <meta name="keywords" content="Chat, cows, chat, jtc chat">
    <style>

        body {background: var(--bg-dark);}

        img {overflow:hidden;}

        .mcontent {
            position: relative;
        }

        .mcontent img {
            background-color: #757575;
        }

        .rooms {
            width: 280px;
            height: 100%;
            position: absolute;
            left: 0px;
            top: 0px;
            bottom: 0px;
            border-right: 1px solid var(--border-puny);
            border-bottom: 5px solid var(--theme-color);
        }

        .area {
            width: calc(100% - 280px);
            height: 100%;
            position: absolute;
            right: 0px;
            top: 0px;
            bottom: 0px;
        }

        header {
            width: 100%;
            background: var(--bg-light);
            height: 45px;
            border-bottom: 1px solid var(--border-strong);
        }

        .rooms header a {
            cursor: pointer;
            color: white;
        }

        .rooms header {
            font-family: "Amatic SC";
            text-align: center;
            font-size: 35px;
            color: white;
            letter-spacing: 2px;
            overflow-y: hidden;
        }

        .rooms header span {
            display: inline-block;
            font-size: 20px;
            color: var(--theme-color);
            transform: rotate(90deg);
            letter-spacing: normal;
            margin-left: -3px;
        }

        .area header {
            font-family: arial;
            position: relative;
        }

        .roomtitle {
            color: #b7b5b5;
            font-size: 30px;
            position: absolute;
            top: 50%;
            left: 0px;
            transform: translateY(-50%);
            margin-left: 15px;
        }

        .room {
            width: 100%;
            color: lightgrey;
            cursor: pointer;
            -webkit-transition-duration: 0.4s;
            transition-duration: 0.4s;
            box-shadow: inset 5px 0px var(--border-puny);
        }

        .room, .room * {
            cursor: pointer;
        }

        .notallowed, .notallowed * {
            cursor: not-allowed !important;
            opacity: 0.7;
            background-color: initial !important;
            box-shadow: initial !important;
        }

        .room:hover {
            box-shadow: inset 5px 0px var(--theme-color);
        }

        .room.current {
            box-shadow: inset 5px 0px var(--highlight);
            background-color: #282828a3;
        }

        .room:hover {
            background-color: #2b2b2b;
        }

        .room:focus {
            background: var(--bg-light);
        }

        .roominfo {
            width: 100%;
            padding: 10px;
            padding-left: 15px;
        }

        .room .online {
            font-size: 10px;
            color: #919191;
        }

        .private, .spectate {
            background-position: calc(100% - 20px) center;
            background-size: 20px;
            background-repeat: no-repeat;
        }

        .private {
             background-image: url("/assets/images/icons/lock.png?w=30");
        }

        .spectate {
             background-image: url("/assets/images/icons/binoculars.png?w=30");
        }

        .area footer {
            height: 45px;
            position: absolute;
            left: 0px;
            bottom: 0px;
            right: 0px;
            width: 100%;
            background: var(--bg-dark);
        }

        .relative {
            width: 100%;
            height: 100%;
            position: relative;
        }

        footer .control {
            width: 40px;
            height: 40px;
            margin: 0px 5px;
            cursor: pointer;
            background-repeat: no-repeat;
            background-position: center;
            background-size: 25px;
            border-radius: 50%;
            display: inline;
            z-index: 1;
            filter: invert(1);
            opacity: 0.7;
            -webkit-transition-duration: 0.4s;
            transition-duration: 0.4s;
        }

        footer .control * {
            -webkit-transition-duration: 0.4s;
            transition-duration: 0.4s;
        }

        .control:hover {
            background-color: var(--bg-light);
            filter: invert(0);
            opacity: 1;
        }

        .control.emoji {
            background-image: url("/assets/images/icons/smile.png?w=100");
            float: left;
        }

        .control.send {
            background-image: url("/assets/images/icons/send.png?w=100");
            float: right;
        }

        .control.emoji:hover {
            background-image: url("/assets/images/icons/smile%20(1).png?w=100");
        }

        .control.send:hover {
            background-image: url("/assets/images/icons/send%20(1).png?w=100");
        }

        footer .sendie {
            width: calc(100% - 100px);
            height: 100%;
            top: 0px;
            margin: 0px;
            bottom: 0px;
            left: 50%;
            transform: translateX(-50%);
            display: inline;
            position: absolute;
            z-index: 1;
        }

        .sendie textarea {
            width: 100%;
            height: calc(100% - 3px);
            margin: 0px;
            padding: 10px;
            padding-left: 15px;
            border-radius: 15px;
            resize: none;
            font-family: arial;
            cursor: text;
            background: transparent;
            color: white;
            display: block;
            outline: 0 !important;
        }

        .awkward {
            width: 100%;
            height: calc(100% - 90px);
            overflow: auto;
            scroll-behavior: unset;
            position: relative;
            font-family: Whitney,Helvetica Neue,Helvetica,Arial,sans-serif;
            text-rendering: optimizeLegibility;
        }

        #scrollc::before {
            display: block;
            width: 100%;
            text-align: center;
            font-size: 12px;
            padding: 20px 0px;
            content: "Loading...";
            border-top: 2px solid var(--theme-color);
            color: var(--theme-color);
        }

        /* width */
        ::-webkit-scrollbar {
            width: 10px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            background: #282828; 
        }
        
        /* Handle */
        ::-webkit-scrollbar-thumb {
            background: var(--bg-light); 
            cursor: pointer;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
            background: #555; 
        }

        .message {
            padding: 10px 5px;
            -webkit-transition-duration: 0.4s;
            transition-duration: 0.4s;
            margin: 5px 0px;
        }

        .message:hover {
            background-color: #1f1f1f;
        }

        .message.default {
            color: white;
            border-top-right-radius: 30px;
            border-bottom-right-radius: 30px;
            position: relative;
            margin-right: 10px;
        }

        .message.status {
            padding: 10px;
            margin: 15px 0px;
            text-align: center;
            font-size: 10px !important;
        }

        .message .profimg {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            float: left;
            margin-left: 2px;
        }

        .msgcontain {
            min-height: 100%;
            padding: 0 15px;
        }

        .msgcontain span {
            display: block;
        }

        .message:hover span.username {
            opacity: 1;
        }

        span.username {
            margin-bottom: 5px;
            color: #c6c6c6;
            opacity: 0.7;
            cursor: pointer;
            -webkit-transition-duration: 0.4s;
            transition-duration: 0.4s;
            display: inline;
            font-family: inherit;
        }

        span.username i, i.time {
            color: #787676;
            padding-left: 5px;
            font-size: 10px;
        }

        span.username i {
            display: none;
        }

        .message:hover i {
            display: inline;
        }

        span.msg {
            color: #dadada;
            word-break: break-word;
            font-family: inherit;
            font-size: 1rem;
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }

        .message:hover span.msg {
            color: #fff;
        }

        .message.status .msg {
            color: #9f9f9f !important;
        }

        .username a {
            color: inherit;
            text-decoration: none;
        }

        span.controls {
            position: absolute;
            right: 3px;
            top: 30px;
            transform: translate(-50%, -50%);
            width: 10px;
            height: 20px;
            background-image: url(/assets/images/icons/dots.svg);
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.2;
            display: none;
            cursor: pointer;
            -webkit-transition-duration: 0.2s;
            background-size: auto 15px;
            transition-duration: 0.2s;
        }

        .message:hover span.controls {
            display: block;
        }

        span.controls:hover {
            opacity: 0.6;
        }

        .mod.bot a:after {
            content: "BOT";
            color: white;
            background: #2424b5;
            font-size: 10px;
            padding: 4px 5px 2px;
            border-radius: 4px;
            margin-left: 5px;
            display: inline-block;
            transform: translateY(-2px);
        }

        span.mod, span.bot.mod {
            color: red;
            opacity: 1;
        }

        .message.highlight {
            border-left: 5px solid red;
            background-color: #1f1f1f;
        }

        .message.default:not(.highlight) {
            padding-left: 10px;
        }

        .message.fake {
            background: initial !important;   
        }

        .message.fake div:not(.msgcontain) {
            background: var(--bg-light);
            padding-left: 10px;
        }

        .message.fake div:nth-child(1) {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            float: left;
        }

        .message.fake .msgcontain div {
            background: var(--bg-light);
            height: 15px;
            border-radius: 10px;
            float: none;
            width: 100%;
        }

        .message.fake .msgcontain div:nth-child(2) {
            margin-top: 10px;
            width: 50%;
        }

        div#btb {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #21a3ff;
            background-image: url(/assets/images/icons/angle-down-solid.svg);
            background-repeat: no-repeat;
            background-position: center 2px;
            cursor: pointer;
            opacity: 0.8;
            display: none;
        }

        .msg a {
            color: #5c7ae7;
        }

        .msg a:hover {
            text-decoration: underline;
        }

        div#infom {
            position: fixed;
            min-width: 200px;
            background: var(--bg-light);
            border-radius: 10px;
            z-index: 999999;
            display: none;
        }

        #infom span:nth-child(1) {
            background: #2c2c2c;
            position: relative;
            height: 38px;
            margin-right: 45px;
            overflow-y: hidden;
        }

        #infom span:nth-child(1) img {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            border-radius: 50%;
            width: 25px;
            height: 25px;
            background: var(--bg-light);
            object-fit: cover;
        }

        #infom span:nth-child(1) a {
            color: inherit;
        }

        #infom span:nth-child(1) a:hover {
            text-decoration: underline;
        }

        #infom span:nth-child(3) {
            padding: 10px;
            padding-bottom: 6px;
            border-top: 1px solid var(--border-weak);
            min-height: 40px;
        }

        #infom span {
            width: 100%;
            padding: 10px;
            display: block;
            color: #c5c5c5;
        }

        #infom span span.role {
            display: inline-block;
            width: unset;
            height: unset;
            padding: 4px 5px;
            border-radius: 5px;
            font-size: 10px;
            color: white;
            margin-right: 5px;
            user-select: none;
            min-height: unset;
        }

        #infom span span.role:nth-last-child(1) {
            margin-right: 0px;
        }

        @media only screen and (max-width: 700px) {
            .usermenu {
                display: none;
            }

            .rooms, .area {
                width: 100%;
            }

            .rooms {
                display: none;
            }

            #loading {
                display: none;
            }
            
            #loading {
                display: none;
            }
        }

        .message error, .message code {
            background: var(--bg-light);
            display: block;
            border-radius: 10px;
            max-width: 400px;
            width: 100%;
            margin-top: 10px;
        }

        .message error, .message code {
            padding: 10px;
            font-family: monospace, consolas;
            color: white;
            font-size: 15px;
            word-break: break-word;
        }

        .msg img {
            border-radius: 2px;
            display: block;
            margin-top: 10px;
            cursor: pointer;
            -webkit-transition-duration: 0.3s;
            transition-duration: 0.3s;
            object-fit: cover;
            max-width: <?php echo $conf['maximgwidth']; ?>px;
            max-height: <?php echo $conf['maximgheight']; ?>px;
        }

        @media only screen and (max-width: <?php echo $conf['maximgwidth']; ?>px) {
            .msg img {
                width: 100%;
                min-width: unset !important;
                min-height: unset !important;
            }
        }

        /* .msg img[src$=".png"] */

        .msg img:hover {
            border-radius: 0px;
        }

        .message code pre {
            margin: 0px;
            width: 100%;
            display: block;
        }

        .message error {
            font-family: consolas;
        }

        a.linkbox {
            display: block;
            background: #2d2d2d;
            margin: 10px 0px;
            border-radius: 10px;
            max-width: 400px;
            cursor: pointer;
            -webkit-transition-duration: 0.4s;
            transition-duration: 0.4s;
            text-decoration: none!important;
            position: relative;
        }

        a.linkbox:hover {
            filter: brightness(1.3);
        }

        a.linkbox:hover .linkbox img {
            filter: brightness(0.7);
        }

        .linkbox span {
            padding: 10px 80px 10px 13px;
            cursor: pointer;
            overflow: hidden;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: pre;
        }

        .linkbox span:nth-child(1) {
            background: var(--bg-light);
            color: white;
        }

        .linkbox img {
            position: absolute;
            top: 50%;
            right: 0px;
            transform: translate(-50%, -50%);
            background: transparent;
            width: 32px;
            height: 32px;
            min-height: 32px;
            border-radius: 0px;
            cursor: pointer;
            filter: none !important;
            margin: 0px;
            border-radius: 5px;
            background: transparent !important;
        }

        @keyframes pop {
            0% {
                transform: translate(-50%, -50%) scale(0);
                display: none;
            }
            1% {display: block;}
            80% {
                transform: translate(-50%, -50%) scale(1.2);
            }
            100% {
                transform: translate(-50%, -50%) scale(1);
            }
        }

        #uploadm {
            position: fixed;
            width: 100vw;
            height: 100vh;
            z-index: 999999;
            backdrop-filter: blur(2px);
            background-color: rgba(0, 0, 0, 0.7);
            top: 0px;
            left: 0px;
            right: 0px;
            bottom: 0px;
            display: none;
        }

        #uploadm .main-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(1);
            background: var(--bg-dark);
            color: white;
            max-width: 400px;
            border-radius: 10px;
            width: calc(100% - 40px);
            animation-name: pop;
            animation-duration: 0.4s;
        }

        .main-modal .main-header {
            width: 100%;
            display: block;
            background: var(--bg-light);
            font-size: 16px;
            border-top: 3px solid var(--theme-color);
        }

        .main-header span {
            margin: 10px;
            display: block;
        }

        .main-modal progress {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            display: block;
            height: 5px;
            -webkit-transition-duration: 0.4s;
            transition-duration: 0.4s;
        }

        .main-modal progress::-webkit-progress-bar {
            background-color: var(--border-strong);
        }

        .main-modal progress::-webkit-progress-value {
            background-color: var(--highlight);
            -webkit-transition-duration: 1s;
            transition-duration: 1s;
        }

        .main-modal img {
            margin: 10px;
            width: calc(100% - 20px);
            border-radius: 5px;
            max-height: 55vh;
            min-height: 20vh;
            object-fit: contain;
        }

        .main-modal button {
            margin: 0px 10px 10px;
            background: transparent;
            font-size: 16px;
            padding: 5px 10px;
            border-radius: 30px;
            -webkit-transition-duration: 0.4s;
            transition-duration: 0.4s;
            cursor: pointer;
        }

        .main-modal button:focus {
            box-shadow: 0 0 0px 2px var(--bg-dark), 0 0 0px 3px var(--highlight);
        }

        .main-modal button:hover {
            box-shadow: unset;
        }

        .main-modal button[send] {
            border: 2px solid var(--theme-color);
            color: var(--theme-color);
        }

        .main-modal button[cancel] {
            color: var(--highlight);
            border: 2px solid var(--highlight);
            float: right;
        }

        .main-modal button[disabled] {
            cursor: not-allowed;
            opacity: 0.4;
        }

        /*.main-modal button:hover {
            box-shadow: inset 0 0 4px #0000008a;
        }*/

        .main-modal button[send]:hover {
            background: var(--theme-color);
            color: white;
        }

        .main-modal button[cancel]:hover {
            background: var(--highlight);
            color: white;
        }

        .main-modal button[disabled]:hover {
            background: initial;
            color: var(--theme-color);
        }

        @media only screen and (max-width: 500px) {
            body div#infom {
                display: none;
                width: calc(100% - 40px);
                left: 0px !important;
                right: 0px !important;
                margin: auto;
            }
        }

        .msg img, .main-modal img {
            --checkerboard: #3d3d3d;
            background-image: linear-gradient(45deg, var(--checkerboard) 25%, transparent 25%), linear-gradient(-45deg, var(--checkerboard) 25%, transparent 25%), linear-gradient(45deg, transparent 75%, var(--checkerboard) 75%), linear-gradient(-45deg, transparent 75%, var(--checkerboard) 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            background-color: #2c2c2c;
        }

        .datebreak {
            display: block;
            text-align: center;
            box-shadow: inset 0px 10px var(--bg-dark), inset 0px 10.5px var(--border-weak);
            margin: 0px 10px;
            user-select: none;
        }

        .datebreak span {
            display: inline;
            margin: auto;
            background: var(--bg-dark);
            color: #484848;
            padding: 0px 5px;
            font-size: 10px;
        }

        .yt-embed {
            margin-top: 10px;
            background: #2d2d2d;
            border-radius: 10px;
            max-width: 400px;
            width: 100%;
        }

        .yt-embed span, .yt-embed i {
            padding: 10px;
            background: var(--bg-light);
            display: block !important;
        }

        .yt-embed i {
            padding-bottom: 0px;
            font-size: 15px;
            color: #d3d3d3;
            padding-left: 35px;
            background-repeat: no-repeat;
            background-size: 20px 20px;
            background-position: 10px 8px;
            background-image: url(https://www.google.com/s2/favicons?domain=www.youtube.com&amp;sz=32);");
        }

        .yt-embed iframe {
            margin: 10px;
            border-radius: 5px;
            width: calc(100% - 20px);
            margin-bottom: 5px;
            height: 213px;
        }
    </style>
</head>
<body>
<div id="infom" class="infom">
    <span><a></a><img alt="small"></span>
    <span></span>
    <span></span>
</div>
<div id="uploadm" onclick="uploadMDone('close')">
    <div class="main-modal" onclick="event.stopPropagation();">
    <div class="main-header"><span>Photo Upload</span></div>
    <progress id="upldp" value="0" max="100"></progress>
    <img id="uploadimg">
    <button send id="cancelsend" disabled="true" onclick="uploadMDone()">Send</button>
    <button cancel onclick="uploadMDone('close')">Cancel</button>
    </div>
</div>
<!--<div id="loading"><img width="50px" src="/assets/images/loading/whitespinner.gif"></img></div>-->
<div class="usermenu">
<?php 
    if (isset($_SESSION['user'])) {
      $href = "/users/" . $_SESSION['user']['name'];
      $src = $href . '/avatar.jpg?w=100';
    } else {
      $href = "/login";
      $src = "/assets/images/avatars/human/humanavatar16.jpg?w=100";
    }
    ?>
    <a title="Profile" href="<?php echo $href; ?>"><img alt="<?php echo $_SESSION['user']['name'];?>'s picture'" class="profile" src="<?php echo $src; ?>"></a>  
    <a title="Chat" href="/chat/"><img alt="webchat icon" src="/assets/images/icons/comment.svg"></a>
    <a title="Classroom Manager" href="/Online/"><img alt="CM Icon" src="/assets/images/icons/online.svg"></a>
    <a title="Games" href="/Games/"><img alt="Games Icon" src="/assets/images/icons/controller.svg"></a>
    <a title="Suggest" href="/Suggest/"><img alt="Suggest Icon" src="/assets/images/icons/bulb.svg"></a>
    <a title="Settings" href="/settings"><img alt="Settings Icon" src="/assets/images/icons/gear.svg"></a>
  </div>
  <div class="mcontent">
        <div class="rooms" id="rmrooms">
            <header>
                <a href="/" class="noa">Jeff The Cow<span>chat</span></a>
            </header>
            <?php
                foreach ($rooms as $room) {
                    if (!findinarray($roles, $room['allow']) && $room['type'] == "private") {
                        echo '<a href="#" class="notallowed"><div id="'.$room['id'].'" class="room"><div class="roominfo ' . $room['type'] . '"><div>' . $room['name'] .
                    '</div><div class="online">Online Members: <span>0</span></div></div></div></a>';
                    } else {
                        echo '<a href="?r='.$room['id'].'" onclick="roomchange('."'".$room['id']."'".', event)"><div id="'.$room['id'].'" class="room"><div class="roominfo ' . $room['type'] . '"><div>' . $room['name'] .
                    '</div><div class="online">Online Members: <span>0</span></div></div></div></a>';
                    }
                }
            ?>
        </div>
        <div class="area">
            <header>
                <div class="roomtitle" id='roomtitle'>JTC - Chat</div>
            </header>
            <div class="awkward" id="scrollc">
                <!--<div onclick="ca.scrollTop=ca.scrollHeight;this.style.display='none';" id="btb"></div>-->

            </div>
            <footer id="expandable">
                <div class="relative">
                    <div class='control emoji'></div>
                    <div class="sendie">
                        <textarea onkeypress="check(event)" placeholder="Send a message..." maxlength="<?php echo $conf['maxlength']; ?>" id="sendie"></textarea>
                        <label style="display: none;" for="promo">Send Message Area</label>
                    </div>
                    <div class='control send' onclick="send()"></div>
                <div>
            </footer>
        </div>
  </div>
<script src="/script.js"></script>
<script>

    var bc = new BroadcastChannel('sessionstatus'),
        id = 0,
        to = 0,
        highestid = 0,
        lowestid = 0,
        lastid = 0,
        idbefore = true,
        room = "",
        loaded = <?php echo $conf['msgtoload']; ?>,
        offset = 0,
        ca = document.getElementById("scrollc"),
        imagefilename = "",
        canload = true,
        tempvalue = "",
        browse = ["https://www.youtube.com/watch?v=dQw4w9WgXcQ"],
        browsePlacement = 0,
        unread = 0,
        abortablexhr,
        $username = "<?php echo $_SESSION['user']['name']; ?>",
        sendie = document.getElementById("sendie");

    window.document.body.onfocus = function() {unread=0; document.title = "JTC - Chat";}

    Date.prototype.customFormat=function(e){var r,a,t,p,c,l,s,h,u,M,i,n,D,y,m,o,d,g,b,Y,F,S;return a=((r=this.getFullYear())+"").slice(-2),c=(l=this.getMonth()+1)<10?"0"+l:l,p=(t=["January","February","March","April","May","June","July","August","September","October","November","December"][l-1]).substring(0,3),u=(M=this.getDate())<10?"0"+M:M,h=(s=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"][this.getDay()]).substring(0,3),S=M>=10&&M<=20?"th":1==(F=M%10)?"st":2==F?"nd":3==F?"rd":"th",e=e.replace("#YYYY#",r).replace("#YY#",a).replace("#MMMM#",t).replace("#MMM#",p).replace("#MM#",c).replace("#M#",l).replace("#DDDD#",s).replace("#DDD#",h).replace("#DD#",u).replace("#D#",M).replace("#th#",S),0==(y=n=this.getHours())&&(y=24),y>12&&(y-=12),D=y<10?"0"+y:y,i=n<10?"0"+n:n,Y=(b=n<12?"am":"pm").toUpperCase(),m=(o=this.getMinutes())<10?"0"+o:o,d=(g=this.getSeconds())<10?"0"+g:g,e.replace("#hhhh#",i).replace("#hhh#",n).replace("#hh#",D).replace("#h#",y).replace("#mm#",m).replace("#m#",o).replace("#ss#",d).replace("#s#",g).replace("#ampm#",b).replace("#AMPM#",Y)};
    function returnDate(ms, f = "#h#:#mm# #AMPM# #M#/#D#/#YY#") {
        ms = new Date(parseInt(ms));
        return ms.customFormat(f);
    }

    bc.onmessage = function(ev) {if(ev=="logout"){window.location.href="/login/?f=/chat/";}}
    // ^ boadcast channel function for other tabs.
    function getM(g, pos, src) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4) {
                if (this.status == 200) {
                    if (this.responseText != "null" && this.responseText != "logout" && this.responseText != "modsonly") {
                        var pl = JSON.parse(this.responseText, true);

                        if (g.includes("before")) {
                            idbefore = true;
                            if (pl.length+1 < <?php echo $conf['msgtoload']; ?>) {
                                console.log("End");
                            } else {
                                lowestid = pl[pl.length-1]['time'];
                                canload = true;
                            }
                        } else {
                            idbefore = false;
                        }

                        for (var i = 0; i < pl.length; i++) {
                            // 1 day 86400000
                            /*if (i != 0) {
                                lastid = pl[i-1]["time"];
                                if (idbefore) {
                                    if ( ((parseInt(pl[i]["time"]) - lastid) <= -86400000) && lastid > 0) {
                                        console.log("datebreak2");
                                        spawn("datebreak", returnDate(pl[i+2]["time"], "#MMMM# #D#, #YYYY#"));
                                    }
                                } else {
                                    if ( ((lastid - parseInt(pl[i]["time"])) >= 86400000) && lastid > 0) {
                                        console.log("datebreak1");
                                        spawn("datebreak", returnDate(pl[i]["time"], "#MMMM# #D#, #YYYY#"));
                                    }
                                }
                            }*/

                            void 0!==document.getElementById("temp")&&null!=document.getElementById("temp")&&document.getElementById("temp").remove();
                            spawn(pl[i]['type'], pl[i]['name'], pl[i]['roles'], parseMarkdown(pl[i]['message']), returnDate(pl[i]['time']), pl[i]['id'], pos, pl[i]['embeds']);
                            if (pl[i]['time'] > highestid) {
                                highestid = pl[i]['time'];
                            }

                            if (!document.hasFocus()) {
                                unread++;
                                document.title = "(" + unread + ") JTC - Chat";
                            } else {
                                unread = 0;
                                document.title = "JTC - Chat";
                            }
                        }
                    } else if (this.responseText == "logout") {
                        bc.postMessage('logout');
                        window.location.href = "/login/?f=/chat/";
                    } else if (this.responseText == "modsonly") {
                        notAllowed();
                    }
                } else {
                    if (navigator.onLine) {
                        snackbar("Failed to load messages. (" + this.status + ")");
                    } else {
                        snackbar("No Network Connection.");
                    }
                }
            }
        };

        xhttp.open("POST", "get.php?r=" + room + g, true);
        xhttp.send();
    }

    /*sendie.oninput = function() {
        document.getElementById("expandable").style.height = sendie.scrollHeight + "px";
        ca.style.height = "calc(" + ca.style.height + " - " + sendie.scrollHeight + "px)";
    };*/

    ca.onscroll = function(e) {
        if (ca.scrollTop <= <?php echo $conf['loadscrolloffset']; ?> && canload == true) {
            canload = false;
            getM("&before=" + lowestid + "&limit=<?php echo $conf['msgtoload']; ?>", "begin");
        }
    }

    function linkify(text) {
        var urlRegex =/(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
        return text.replace(urlRegex, function(url) {
            return '<a target="_blank" rel="noopener noreferrer" href="' + url + '">' + url + '</a>';
        });
    }

    function ytl(url) {
        var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
        var match = url.match(regExp);
        return (match&&match[7].length==11)? match[7] : false;
    }

    setInterval(function(){
        if (room != "") {
            getM("&recent=" + highestid, "end", "bottom");
        }
    }, <?php echo $conf['updateint']; ?>);


    //ajax stuff
    var sendie = document.getElementById('sendie');

    function spawn(type, name, roles, msg = "", time, id, pos, embeds = "bnVsbA==") {
        if (time == "auto") {time = returnDate(Date.now());}
        var html = "";
        embeds = atob(embeds);
        if (embeds != "null" && embeds != "" && embeds != "[]" && embeds != "{}") {
            link = JSON.parse(embeds);
            link = link[0];
            console.log(link);
            if (link.host != "www.youtube.com" && link.host != "youtube.com" && link.host != "youtu.be") {
                linkbox = '<a class="linkbox" target="_blank" rel="noopener noreferrer" href="'+link.path+'" title="'+link.title+'"><span>'+link.title+'</span><span>'+link.base+'</span><img src="https://www.google.com/s2/favicons?domain='+link.host+'&amp;sz=64"></a>';
            } else {
                linkbox = '<div class="yt-embed"><i>YouTube</i><a target="_blank" rel="noopener noreferrer" href="'+link.path+'"><span>'+link.title+'</span><iframe src="https://www.youtube.com/embed/'+ytl(link.path)+'" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></a></div>';
            }
            msg = linkify(msg) + linkbox;
        }
        msg = msg.replace(/\B@[a-z0-9_-]+/gi, function(str){return "<a href='/users/" + str.replace("@", "") + "/'>" + str + "</a>";});
        if (type == "default") {
            html = '<div jid="'+id+'" class="message default"><img class="profimg" alt="'+name+'" src="/users/'+name+'/avatar.jpg?w=100&h=100&s=10"><div class="msgcontain"><span class="username" style="color:'+roles+';"><a class="hovername">'+name+'</a><i>'+time+'</i></span><span class="msg">'+msg+'</span></div><span class="controls"></span></div>';
        } else if (type == "status") {
            html = '<div jid="'+id+'" class="message status"><span class="msg">'+msg+'<i class="time">'+time+'</i></span></div>';
        } else if (type == "mod") {
            html = '<div jid="'+id+'" class="message default highlight"><img class="profimg" alt="'+name+'" src="/users/'+name+'/avatar.jpg?w=100&h=100&s=10"><div class="msgcontain"><span class="username mod" style="color:red;"><a class="hovername">'+name+'</a><i>'+time+'</i></span><span class="msg">'+msg+'</span></div></div>';
        } else if (type == "bot") {
            html = '<div jid="'+id+'" class="message default highlight"><img class="profimg" alt="'+name+'" src="/users/'+name+'/avatar.jpg?w=100&h=100&s=10"><div class="msgcontain"><span class="username mod bot" style="color:red;"><a class="hovername">'+name+'</a><i>'+time+'</i></span><span class="msg">'+msg+'</span></div></div>';
        } else if (type == "fake") {
            html = '<div id="temp" class="message default" style="opacity:0.7;"><img class="profimg" alt="'+$username+'" src="/users/'+$username+'/avatar.jpg?w=100&h=100&s=10"><div class="msgcontain"><span class="username" style="color:#c6c6c6;"><a class="hovername">'+$username+'</a></span><span class="msg">'+msg+'</span></div><span class="controls"></span></div>';
            document.getElementById("scrollc").insertAdjacentHTML("beforeend", html);
        } else if (type == "img") {
            var dim = msg.split("_");
            embedW = dim[2];
            embedH = dim[3];
            embedMax = Math.max(embedW, embedH);
            es = "";
            if (embedMax >= <?php echo $conf['qmaximgwidth']; ?>) {
                if (embedW == embedMax) {
                    // Width is widest
                    qs = "?w=<?php echo $conf['qmaximgwidth']; ?>";
                } else {
                    // Height is tallest
                    qs = "?h=<?php echo $conf['qmaximgheight']; ?>";
                    es = 'style="min-height: '+<?php echo $conf['maximgheight']; ?>+'px;"';
                }
            } else {
                qs = "?w="+embedW+"&h="+embedH;
                es = 'style="min-width: '+embedW+'px;min-height: '+embedH+'px;"';
            }
            html = '<div jid="'+id+'" class="message default"><img class="profimg" alt="'+name+'" src="/users/'+name+'/avatar.jpg?w=100&h=100&s=10"><div class="msgcontain"><span class="username" style="color:'+roles+';"><a class="hovername">'+name+'</a><i>'+time+'</i></span><span class="msg"><a href="/chat/uploads/'+msg+'" target="_blank" rel="noopener noreferrer"><img '+es+' src="/chat/uploads/'+msg+qs+'&s=10"></a></span></div><span class="controls"></div>';
        } else if (type == "datebreak") {
            html = "<div class='datebreak'><span>" + name + "</span></div>";
            document.getElementById("scrollc").insertAdjacentHTML("afterbegin", html);
        }


        if (pos == "begin") {
            document.getElementById("scrollc").insertAdjacentHTML("afterbegin", html);
        } else if (pos == "end") {
            document.getElementById("scrollc").insertAdjacentHTML("beforeend", html);
        }
        if (ca.scrollHeight-ca.scrollTop < <?php echo $conf['scrolltobottomoffset']; ?>) {ca.scrollTop=ca.scrollHeight;}
    }

    function addToBrowse(cmd) {
        browse.push(cmd);
        if (browse.length > 20) {
            browse.shift();
        }
    }

    function callFromBrowse(direction) {
        if (direction == 1 && browse.length > browsePlacement) {
            browsePlacement++;
        } else if (direction == 0 && browsePlacement > 1) {
            browsePlacement--;
        }

        sendie.value = browse[browse.length - browsePlacement];
    }

    function resetBrowse() {
        browsePlacement = 0;
    }

    sendie.onkeydown = function(e) {
        if (e.keyCode == 38) {
            callFromBrowse(1);
        } else if (e.keyCode == 40) {
            callFromBrowse(0);
        } else {
            resetBrowse();
        }
    }

    function check(e) {
        if (e.keyCode == 13 && (!e.shiftKey)) {
            e.preventDefault();
            send();
        }
    }

    function send() {
        if (sendie.value.length <= <?php echo $conf['maxlength']; ?> && sendie.value.length > 0) {
            tempvalue = sendie.value;
            if (!navigator.onLine) {
                snackbar("Message Failed to Send");
                spawn('bot', 'chat.bot', 'mod', 'Hey, <a href="/users/'+$username+'/">@'+$username+'</a> You may not be connected to the internet. <a onclick="send()">Try again?</a>', "auto", 'auto', 'end');
            } else {
                spawn("fake", 0, 0, parseMarkdown(sendie.value));
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4) {
                        if (this.status == 200) {
                            if (this.responseText != "200") {
                                clearFakes();
                                spawn('bot', 'chat.bot', 'mod', this.responseText, "auto", 'auto', 'end');
                            }   
                        } else {
                            clearFakes();
                            sendie.value = tempvalue;
                            spawn('bot', 'chat.bot', 'mod', 'Hey, <a href="/users/'+$username+'/">@'+$username+'</a> Sorry! Something has gone terribly wrong. <a onclick="send()">Try again?</a><error>HTTP STATUS CODE: '+this.status+'<br>process.php</error>', "auto", 'auto', 'end');
                        }
                    }
                };
                xhttp.open("POST", "process.php?r=" + room, true);
                xhttp.send(sendie.value);
                addToBrowse(sendie.value);
                sendie.value = "";
            }
        } else if (sendie.value.length <= 0) {
            snackbar("Message too short.");
            spawn('bot', 'chat.bot', 'mod', 'Hey, <a href="/users/'+$username+'/">@'+$username+'</a> your message is too short.', "auto", 'auto', 'end');
        } else {
            snackbar("Message too long.");
            spawn('bot', 'chat.bot', 'mod', 'Hey, <a href="/users/'+$username+'/">@'+$username+'</a> your message is too long.', "auto", 'auto', 'end');
        }
    }

    function roomchange(did, e, source) {
        room = did;
        if (e != null) {
            e.preventDefault();
        }
        if (source != "load") {
            historyPush("add", "?r="+did);
        }
        elems = document.getElementById("rmrooms").getElementsByClassName("room");
        for (var i = 0; i < elems.length; i++) {
            elems[i].classList.remove("current");
        }
        if (did != "") {document.getElementById(did).classList.add("current");}
        id = 0;
        to = 0;
        offset = 0;
        canload = true;
        loaded = <?php echo $conf['msgtoload']; ?>;
        tempvalue = "";
        unread = 0;
        ca.innerHTML = "";
        if (did != "") {document.getElementById("roomtitle").innerText = document.getElementById(did).getElementsByTagName("div")[0].getElementsByTagName("div")[0].innerText;}
        getM("&before=last&limit=" + loaded, "begin");
        ca.scrollTop = ca.scrollHeight;
    }

    function clearChat() {
        roomchange("", null, "load");
        historyPush("replace", "chat/");
        document.getElementById("roomtitle").innerText = "JTC - Chat";
    }

    function notAllowed() {
        clearChat();
        ca.innerHTML = "";
    }

    room = getQuery('r');
    if (room != "") {
        roomchange(room, null, "load");
    } else {
        roomchange("<?php echo $conf['defaultroomid'];?>", null);
    }

    function dataURLtoFile(dataurl, filename) {
        var arr = dataurl.split(','),
            mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]), 
            n = bstr.length, 
            u8arr = new Uint8Array(n);
        
        while(n--){
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new File([u8arr], filename, {type:mime});
    }

    function uploadM(data) {
        document.getElementById("upldp").value = 0;
        document.getElementById("upldp").style.height = "5px";
        document.getElementById('uploadimg').src = data;
        document.getElementById('uploadimg').onload = function() {
            document.getElementById("uploadm").getElementsByClassName("main-modal")[0].style.animationName = "pop";
            document.getElementById("uploadm").style.display = "block";
        }

        let fData = new FormData();
        let filetype = data.match(/[^:/]\w+(?=;|,)/)[0];
        var objfile = dataURLtoFile(data, "." + filetype);
        fData.append('img', objfile);

        let xhttp = new XMLHttpRequest();
        abortablexhr = xhttp;
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4) {
                if (this.status == 200) {
                    document.getElementById("upldp").value = 100;
                    document.getElementById("upldp").style.height = "2px";
                    document.getElementById("cancelsend").setAttribute("disabled", "false");
                    document.getElementById("cancelsend").removeAttribute("disabled");
                    document.getElementById("cancelsend").focus();
                    imagefilename = this.responseText;
                    console.log("imgfn" + imagefilename);
                } else {
                    snackbar("Upload Error: " + this.status);
                }
            }
        };
        xhttp.upload.addEventListener('progress', function(e) {
                document.getElementById("upldp").value = (e.loaded / e.total)*100;
        });
        xhttp.open("POST", "imagehandler.php", true);
        xhttp.send(fData);
        document.getElementById("cancelsend").setAttribute("disabled", "true");
    }

    function uploadMDone(method) {
        console.log("Pushed send: " + imagefilename);
        if (method != "close") {
            console.log("sending");
            let xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
            if (this.readyState == 4) {
                if (this.status == 200) {
                    console.log(this.responseText);
                } else {
                    snackbar("Error: " + this.status);
                }
            }
            };
            xhttp.open("POST", "process.php?r=" + room, true);
            xhttp.send("/img " + imagefilename);
        } else {
            abortablexhr.abort();
        }
        document.getElementById("uploadm").getElementsByClassName("main-modal")[0].style.animationName = "unset";
        document.getElementById("uploadm").style.display = "none";
        document.getElementById('uploadimg').src = "";
        document.getElementById("upldp").value = 0;
        document.getElementById("upldp").style.height = "5px";
    }

    function retrieveImageFromClipboardAsBase64(e,t,i){0==e.clipboardData&&"function"==typeof t&&t(void 0);var a=e.clipboardData.items;null==a&&"function"==typeof t&&t(void 0);for(var n=0;n<a.length;n++)if(-1!=a[n].type.indexOf("image")){var o=a[n].getAsFile(),r=document.createElement("canvas"),d=r.getContext("2d"),c=new Image;c.onload=function(){r.width=this.width,r.height=this.height,d.drawImage(c,0,0),"function"==typeof t&&t(r.toDataURL(i||"image/png"))};var f=window.URL||window.webkitURL;c.src=f.createObjectURL(o)}}

    function parseMarkdown(markdownText) {
        const htmlText = markdownText
            .replace(/\*\*(.*)\*\*/gim, '<b>$1</b>')
            .replace(/\*(.*)\*/gim, '<i>$1</i>')
            .replace(/\`\`\`(.*)\`\`\`/gim, '<code>$1</code>')

        return htmlText.trim()
    }

    function clearFakes() {void 0!==document.getElementById("temp")&&null!=document.getElementById("temp")&&document.getElementById("temp").remove();}

    /*window.onload = function() {loadm('stop');setTimeout(function(){ca.style.scrollBehavior="smooth";},2000);}
    window.onerror = function() {loadm('stop');setTimeout(function(){ca.style.scrollBehavior="smooth";},2000);}
    setTimeout(function(){loadm('stop');setTimeout(function(){ca.style.scrollBehavior="smooth";},2000);}, 3000);*/

   var selem = document.getElementById("scrollc");
   selem.scrollTop = selem.scrollHeight - selem.clientHeight;

    function cloneImg(ogimg, destimgbox){destimgbox.innerHTML = ogimg.cloneNode(true);}

    // modal thingy
    let circle = document.getElementById('infom');
    let spans = circle.getElementsByTagName("span");
    var oldel = document;

    function updateM(e) {
        var theorystyleleft = (e.pageX + <?php echo $conf['modaloffset']['x']; ?>);
        var theorystyletop = (e.pageY + <?php echo $conf['modaloffset']['y']; ?>);
        if (theorystyleleft + circle.offsetWidth > window.innerWidth - 20) {
            theorystyleleft = window.innerWidth - circle.offsetWidth - 20;
        }
        if (theorystyletop + circle.offsetHeight > window.innerHeight - 20) {
            theorystyletop = window.innerHeight - circle.offsetHeight - 20;
        }
        circle.style.left = theorystyleleft + 'px';
        circle.style.top = theorystyletop + 'px';
        el = document.elementFromPoint(e.pageX, e.pageY); 
        if (el.classList.contains("hovername")) {
            circle.style.display = "block";
            if (el.innerText != oldel) {
                spans[0].getElementsByTagName("a")[0].innerText = el.innerText;
                spans[0].getElementsByTagName("a")[0].href = "/users/" + el.innerText + "/";
                spans[0].style.color = el.parentElement.style.color;
                spans[0].getElementsByTagName("img")[0].removeAttribute("src");
                spans[0].getElementsByTagName("img")[0].src = "/users/" + el.innerText + "/avatar.jpg?w=25&h=25&s=10";
                spans[1].innerText = "Loading...";
                spans[2].innerText = "";
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        var userdata = JSON.parse(this.responseText);
                        spans[1].innerText = userdata.first + " " + userdata.last;
                        var roles = userdata.roles.split(" "), finalroles = "";
                        for (var i=0; i<roles.length; i++) {
                            if (roles[i] != "none") {
                                finalroles += "<span class='role dark " + roles[i] + "'>" + roles[i] + "</span>";
                            } else {
                                finalroles = "<span class='role dark none'>no roles</span>";
                            }
                        }
                        spans[2].innerHTML = finalroles;
                        oldel = el.innerText;
                    }
                };
                xhttp.open("GET", "/users/" + el.innerText + "/?json", true);
                xhttp.send();
            }
        } else {
            circle.style.display = "none";
        }
    }

    const onMouseMove = (e) =>{
        updateM(e);
    }

    const onUsernameClick = (e) =>{
        updateM(e);
        el = document.elementFromPoint(e.pageX, e.pageY); 
        if (el.classList.contains("hovername") || el.classList.contains("infom")) {
            circle.style.display = "block !important";
            document.removeEventListener('mousemove', onMouseMove, false);
        } else {
            document.addEventListener('mousemove', onMouseMove);
        }
    }
    document.addEventListener('mousemove', onMouseMove, {passive: true});
    document.addEventListener('click', onUsernameClick, {passive: true});
    window.addEventListener("paste", function(e){
        sendie.focus();
        retrieveImageFromClipboardAsBase64(e, function(imageDataBase64){
            if(imageDataBase64){
                uploadM(imageDataBase64);
                sendie.blur();
            }
        });
    }, false);
    document.onkeydown = function(evt) {
        evt = evt || window.event;
        var isEscape = false;
        if ("key" in evt) {
            isEscape = (evt.key === "Escape" || evt.key === "Esc");
        } else {
            isEscape = (evt.keyCode === 27);
        }
        if (isEscape) {
            clearChat();
        }
    };
</script>
</body>
</html>