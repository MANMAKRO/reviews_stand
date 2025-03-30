<?php
header('Content-Type: application/json');

$questions = json_decode(file_get_contents('questions.json'), true) ?: [];
$activeQuestions = array_filter($questions, fn($q) => !$q['archived']);

if (empty($activeQuestions)) {
    echo json_encode(null);
    exit;
}

$randomQuestion = $activeQuestions[array_rand($activeQuestions)];
echo json_encode($randomQuestion);
?>