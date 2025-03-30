<?php
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['questionId']) || !isset($data['answer'])) {
    http_response_code(400);
    exit('Invalid data');
}

$filename = "q_{$data['questionId']}.json";
$responses = [];

if (file_exists($filename)) {
    $responses = json_decode(file_get_contents($filename), true) ?: [];
}

$responses[] = [
    'timestamp' => $data['timestamp'],
    'vote' => $data['vote'],
    'answer' => $data['answer']
];

file_put_contents($filename, json_encode($responses, JSON_PRETTY_PRINT));
echo "Response recorded";
?>