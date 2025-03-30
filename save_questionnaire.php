<?php
$data = json_decode(file_get_contents('php://input'), true);
$questionnaires = json_decode(file_get_contents('questionnaires.json'), true) ?: [];
$data['id'] = count($questionnaires) + 1;
$questionnaires[] = $data;
file_put_contents('questionnaires.json', json_encode($questionnaires));
?>