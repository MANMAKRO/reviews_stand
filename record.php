<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['vote'])) {
    $vote = intval($_POST['vote']);
    if ($vote < 1 || $vote > 5) exit("Invalid vote");
    
    $file = 'results.txt';
    file_put_contents($file, $vote . "\n", FILE_APPEND);
    echo "Vote recorded";
} else {
    echo "No vote provided";
}
?>