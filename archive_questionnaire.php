<?php
$id = $_GET['id'];
$questionnaires = json_decode(file_get_contents('questionnaires.json'), true);
foreach ($questionnaires as &$q) {
    if ($q['id'] == $id) $q['archived'] = true;
}
file_put_contents('questionnaires.json', json_encode($questionnaires));
?>