<?php
header('Content-Type: application/json');
$questionnaires = json_decode(file_get_contents('questionnaires.json'), true);
$active = array_filter($questionnaires, fn($q) => !$q['archived']);
echo json_encode(array_values($active));
?>