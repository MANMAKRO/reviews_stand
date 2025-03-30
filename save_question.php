<?php
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($data['question']) || !is_array($data['options']) || count($data['options']) < 2) {
    http_response_code(400);
    exit('Invalid question data');
}

// Sanitize inputs
$newQuestion = [
    'id' => uniqid(),
    'question' => htmlspecialchars(trim($data['question'])),
    'options' => array_map('htmlspecialchars', array_map('trim', $data['options'])),
    'archived' => (bool)($data['archived'] ?? false),
    'created' => date('Y-m-d H:i:s')
];

// Save to file
$questions = json_decode(file_get_contents('questions.json'), true) ?: [];
$questions[] = $newQuestion;
file_put_contents('questions.json', json_encode($questions, JSON_PRETTY_PRINT));

echo "Question saved successfully";
?>