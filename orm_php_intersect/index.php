<?php
include_once 'GetData.php';

// Set appropriate headers for JSON API
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

$method = $_SERVER['REQUEST_METHOD'];

// Handle preflight requests
if ($method == "OPTIONS") {
    die();
}

// Initialize API and get data
$api = new GetData();
$data = $api->getAll();

// Return JSON response with proper formatting
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);