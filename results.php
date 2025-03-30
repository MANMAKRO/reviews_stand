<?php
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

// Read emoji ratings
$votes = array_fill(1, 5, 0);
if (file_exists('results.txt')) {
    $lines = file('results.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $vote = intval(trim($line));
        if ($vote >= 1 && $vote <= 5) $votes[$vote]++;
    }
}
$totalVotes = array_sum($votes);

// Read questions and responses
$questions = [];
if (file_exists('questions.json')) {
    $questions = json_decode(file_get_contents('questions.json'), true) ?: [];
}

$questionData = [];
foreach ($questions as $q) {
    $responses = [];
    $responseFile = "q_{$q['id']}.json";
    if (file_exists($responseFile)) {
        $responses = json_decode(file_get_contents($responseFile), true) ?: [];
    }
    
    // Calculate stats
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
    
    $questionData[] = [
        'id' => $q['id'],
        'question' => $q['question'],
        'options' => $q['options'],
        'archived' => $q['archived'] ?? false,
        'stats' => $stats
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Feedback Results</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: Arial, sans-serif; padding: 50px; }
    .container { max-width: 1200px; margin: 0 auto; }
    .chart-container { background: white; padding: 20px; margin-bottom: 40px; }
    .question-card { background: #f8f9fa; padding: 20px; margin: 20px 0; }
    .response-bar { height: 20px; background: #007bff; margin: 5px 0; }
    .archived { opacity: 0.6; }
    #questionForm { display: none; margin: 20px 0; padding: 20px; background: #e9ecef; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Feedback Results</h1>
    
    <!-- Emoji Ratings -->
    <div class="chart-container">
      <h2>Experience Ratings</h2>
      <canvas id="emojiChart"></canvas>
      <p>Total Votes: <?= $totalVotes ?></p>
    </div>

    <!-- Question Management -->
    <h2>Question Management</h2>
    <button onclick="toggleQuestionForm()">Add New Question</button>
    
    <div id="questionForm">
      <input type="text" id="newQuestion" placeholder="Enter question" style="width: 300px; padding: 8px; margin: 10px 0;"><br>
      <div id="optionsContainer">
        <input type="text" class="optionInput" placeholder="Option 1" style="margin: 5px; padding: 5px;">
        <input type="text" class="optionInput" placeholder="Option 2" style="margin: 5px; padding: 5px;">
      </div>
      <button onclick="addOption()" style="margin: 10px; padding: 5px 10px;">+ Add Option</button>
      <button onclick="saveQuestion()" style="padding: 8px 16px; background: #28a745; color: white;">Save Question</button>
    </div>

    <!-- Active Questions -->
    <div class="question-results">
      <h2>Active Questions</h2>
      <?php foreach ($questionData as $q): ?>
        <?php if(!$q['archived']): ?>
          <div class="question-card">
            <h3><?= htmlspecialchars($q['question']) ?></h3>
            <p>Total Responses: <?= $q['stats']['total'] ?> 
              (<?= $q['stats']['unanswered'] ?> unanswered)</p>
            
            <?php foreach ($q['options'] as $index => $option): ?>
              <div>
                <?= htmlspecialchars($option) ?>: 
                <?= $q['stats']['answers'][$index] ?> 
                (<?= $q['stats']['total'] > 0 
                  ? round(($q['stats']['answers'][$index]/$q['stats']['total'])*100, 1)
                  : 0 ?>%)
                <div class="response-bar" 
                     style="width: <?= ($q['stats']['answers'][$index]/max($q['stats']['total'], 1))*100 ?>%">
                </div>
              </div>
            <?php endforeach; ?>
            
            <button onclick="archiveQuestion('<?= $q['id'] ?>')" style="margin-top: 10px;">Archive</button>
			
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <a href="archived.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none;">
      View Archived Questions
    </a>
  </div>

  <script>
    // Emoji Chart
    const emojiCtx = document.getElementById('emojiChart').getContext('2d');
    new Chart(emojiCtx, {
      type: 'bar',
      data: {
        labels: ['😀 1', '🙂 2', '😐 3', '🙁 4', '😢 5'],
        datasets: [{
          label: 'Votes',
          data: [<?= implode(',', $votes) ?>],
          backgroundColor: ['#28a745', '#7cb342', '#ffc107', '#fd7e14', '#dc3545']
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true, ticks: { precision: 0 } }
        }
      }
    });

    // Question Management
    function toggleQuestionForm() {
      const form = document.getElementById('questionForm');
      form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }

    function addOption() {
      const newInput = document.createElement('input');
      newInput.type = 'text';
      newInput.className = 'optionInput';
      newInput.placeholder = 'Option ' + (document.querySelectorAll('.optionInput').length + 1);
      newInput.style.margin = '5px';
      newInput.style.padding = '5px';
      document.getElementById('optionsContainer').appendChild(newInput);
    }

    function saveQuestion() {
      const questionText = document.getElementById('newQuestion').value;
      const options = Array.from(document.querySelectorAll('.optionInput'))
                          .map(input => input.value.trim())
                          .filter(value => value !== '');

      if (!questionText || options.length < 2) {
        alert('Please enter a question and at least 2 valid options');
        return;
      }

      fetch('save_question.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
          question: questionText,
          options: options,
          archived: false
        })
      }).then(response => {
        if (response.ok) {
          location.reload();
        } else {
          alert('Failed to save question');
        }
      });
    }

    function archiveQuestion(id) {
    if (confirm('Are you sure you want to archive this question?')) {
        fetch(`archive_question.php?id=${encodeURIComponent(id)}`, {
            credentials: 'include',
            headers: {
                'Authorization': 'Basic ' + btoa('admin:password')
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => { throw new Error(text) });
            }
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Archive failed: ' + error.message);
        });
    }
}
</script>
</body>
</html>