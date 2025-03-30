<?php
// ... authentication code remains the same ...

$questions = json_decode(file_get_contents('questions.json'), true) ?: [];
$archivedQuestions = array_filter($questions, fn($q) => $q['archived']);

// Load response data for archived questions
$archivedData = [];
foreach ($archivedQuestions as $q) {
    $filename = "q_{$q['id']}.json";
    $responses = file_exists($filename) ? json_decode(file_get_contents($filename), true) : [];
    
    $stats = [
        'total' => count($responses),
        'answers' => array_fill(0, count($q['options']), 0),
        'unanswered' => 0
    ];
    
    foreach ($responses as $response) {
        if ($response['answer'] === null) {
            $stats['unanswered']++;
        } else {
            $stats['answers'][$response['answer']]++;
        }
    }
    
    $archivedData[] = [
        'question' => $q['question'],
        'options' => $q['options'],
        'stats' => $stats
    ];
}
?>
<!-- Add this in the body -->
<div class="container">
  <a href="results.php" class="back-link">← Back to Main Results</a>
  <h1>Archived Questions</h1>
  
  <?php foreach ($archivedData as $q): ?>
    <div class="question-card archived">
      <h3><?= htmlspecialchars($q['question']) ?></h3>
      <p>Total Responses: <?= $q['stats']['total'] ?> (<?= $q['stats']['unanswered'] ?> unanswered)</p>
      
      <?php foreach ($q['options'] as $index => $option): ?>
        <div>
          <?= htmlspecialchars($option) ?>: 
          <?= $q['stats']['answers'][$index] ?> 
          (<?= $q['stats']['total'] > 0 ? number_format(($q['stats']['answers'][$index]/$q['stats']['total'])*100, 1) : 0 ?>%)
          <div class="response-bar" style="width: <?= ($q['stats']['answers'][$index]/max($q['stats']['total'], 1))*100 ?>%"></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div>