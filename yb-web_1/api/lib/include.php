<?php

define("DbType", "mysql");
define("DbAddress", "127.0.0.1");
define("DbPort", "3306");
define("DbName", "ybzcdb");
define("DbUsername", "root");
define("DbPassword", "veda");
define("DbCharset","utf8");
define("DbTablePrefix", "");
//define("RSA_PUBLIC_KEY_PATH", dirname($_SERVER["DOCUMENT_ROOT"])."/web_user_login_keys/rsa_public_key.pem");


include_once __DIR__ . "/Common.php";
include_once __DIR__ . "/../application/common.php";
include_once __DIR__ . "/Database.php";
include_once __DIR__ . "/Shell.php";

header("Content-Type: text/plain; charset=utf-8");
header("access-control-allow-origin: *");