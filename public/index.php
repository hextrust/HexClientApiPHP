<?php
require "../bootstrap.php";
require '../src/Controller/HexClientController.php';
use Src\Controller\HexClientController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
$req_body = file_get_contents('php://input');
$req_body = json_decode($req_body,true);

// all of our endpoints start with /person
// everything else results in a 404 Not Found
if ($uri[1] !== 'person') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$requestMethod = $_SERVER["REQUEST_METHOD"];
$hexClientRequestMethod = isset($req_body["method"]) ? $req_body["method"] : "POST";
$hexClientPath = isset($req_body["path"]) ? $req_body["path"] : "";

$controller = new HexClientController($requestMethod, json_encode($req_body, true));
$controller->processRequest();