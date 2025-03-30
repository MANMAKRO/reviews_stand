<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Authentication
$username = 'admin';
$password = 'password';
if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] !== $username || 
    $_SERVER['PHP_AUTH_PW'] !== $password) {
    header('WWW-Authenticate: Basic realm="Restricted Area"');
    header('HTTP/1.0 401 Unauthorized');
    exit('Unauthorized access');
}

// Validate input
if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('Missing question ID');
}

// File paths
$questionsFile = __DIR__ . '/questions.json';

// Load questions
if (!file_exists($questionsFile)) {
    http_response_code(500);
    exit('Questions file not found');
}

$questions = json_decode(file_get_contents($questionsFile), true);
if ($questions === null) {
    http_response_code(500);
    exit('Invalid questions data');
}

// Find question
$questionId = $_GET['id'];
$found = false;

foreach ($questions as &$q) {
    if ((string)$q['id'] === (string)$questionId) {
        $q['archived'] = true;
        $found = true;
        break;
    }
}

if (!$found) {
    http_response_code(404);
    exit('Question not found');
}

// Save changes
$result = file_put_contents($questionsFile, json_encode($questions, JSON_PRETTY_PRINT));
if ($result === false) {
    http_response_code(500);
    exit('Failed to save changes');
}

// Return success
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>