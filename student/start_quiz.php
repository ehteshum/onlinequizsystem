<?php
// student/start_quiz.php
// Load a quiz, create an attempt and display questions with a timer
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

function require_statement($stmt, $mysqli, $context)
{
  if ($stmt === false) {
    die($context . ' failed: ' . $mysqli->error . '. If this is a missing table, import the SQL file for that table first.');
  }

  return $stmt;
}

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
if ($quiz_id <= 0) {
    die('Invalid quiz id.');
}

// Fetch quiz information
$stmt = require_statement($mysqli->prepare('SELECT id, title, duration FROM quizzes WHERE id = ? LIMIT 1'), $mysqli, 'Quiz lookup');
$stmt->bind_param('i', $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
if (!$quiz) {
    die('Quiz not found.');
}

// Prevent multiple active attempts for the same quiz in the current session
if (!empty($_SESSION['attempt_id']) && !empty($_SESSION['attempt_quiz_id']) && (int)$_SESSION['attempt_quiz_id'] === $quiz_id) {
    $attempt_id = (int)$_SESSION['attempt_id'];
} else {
    // Create a new attempt and store the attempt id in session
    $start_time = date('Y-m-d H:i:s');
  $attempt_stmt = require_statement($mysqli->prepare('INSERT INTO attempts (user_id, quiz_id, score, start_time) VALUES (?, ?, 0, ?)'), $mysqli, 'Attempt creation');
    $attempt_stmt->bind_param('iis', $_SESSION['user_id'], $quiz_id, $start_time);
    if (!$attempt_stmt->execute()) {
        die('Could not create attempt.');
    }
    $attempt_id = $attempt_stmt->insert_id;
    $_SESSION['attempt_id'] = $attempt_id;
    $_SESSION['attempt_quiz_id'] = $quiz_id;
}

// Fetch questions and options for the quiz
$questions_sql = 'SELECT id, question_text FROM questions WHERE quiz_id = ? ORDER BY id ASC';
$qstmt = require_statement($mysqli->prepare($questions_sql), $mysqli, 'Question fetch');
$qstmt->bind_param('i', $quiz_id);
$qstmt->execute();
$questions = $qstmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=htmlspecialchars($quiz['title'])?></title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5;margin:0}
    .wrap{max-width:900px;margin:30px auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
    .timer{position:sticky;top:0;background:#fff;padding:12px 0;font-size:20px;font-weight:bold;color:#b00020;border-bottom:1px solid #eee;z-index:10}
    .question{margin:18px 0;padding:14px;border:1px solid #eee;border-radius:8px}
    .option{display:block;margin:8px 0}
    button{padding:10px 16px;border:0;border-radius:6px;background:#007bff;color:#fff;cursor:pointer}
    .top-actions{display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap}
    .btn{display:inline-block;padding:8px 12px;border-radius:6px;text-decoration:none;color:#fff;background:#6c757d}
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <div class="top-actions">
      <h2 style="margin:0"><?=htmlspecialchars($quiz['title'])?></h2>
      <a class="btn" href="quizzes.php">Back to Quizzes</a>
    </div>

    <!-- Timer uses quiz duration from database and auto-submits when time ends -->
    <div class="timer" id="timer"></div>

    <form method="post" action="submit_quiz.php" id="quizForm">
      <input type="hidden" name="quiz_id" value="<?=htmlspecialchars($quiz_id)?>">

      <?php $index = 1; while ($question = $questions->fetch_assoc()): ?>
        <div class="question">
          <!-- Display each question with its options -->
          <h3 style="margin-top:0"><?=$index?>. <?=htmlspecialchars($question['question_text'])?></h3>
          <?php
            $ostmt = require_statement($mysqli->prepare('SELECT id, option_text FROM options WHERE question_id = ? ORDER BY id ASC'), $mysqli, 'Option fetch');
            $ostmt->bind_param('i', $question['id']);
            $ostmt->execute();
            $options = $ostmt->get_result();
          ?>
          <?php while ($option = $options->fetch_assoc()): ?>
            <label class="option">
              <input type="radio" name="answers[<?=$question['id']?>]" value="<?=$option['id']?>">
              <?=htmlspecialchars($option['option_text'])?>
            </label>
          <?php endwhile; ?>
        </div>
      <?php $index++; endwhile; ?>

      <button type="submit">Submit Quiz</button>
    </form>
  </div>

  <script>
    // Convert duration in minutes to seconds and count down from there
    let remaining = <?=((int)$quiz['duration']) * 60?>;
    const timerEl = document.getElementById('timer');
    const form = document.getElementById('quizForm');

    function renderTimer() {
      const minutes = Math.floor(remaining / 60);
      const seconds = remaining % 60;
      timerEl.textContent = 'Time Left: ' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    }

    renderTimer();
    const interval = setInterval(() => {
      remaining -= 1;
      renderTimer();
      if (remaining <= 0) {
        clearInterval(interval);
        // Auto submit when time ends
        form.submit();
      }
    }, 1000);

    // Basic anti-cheat: warn if user tries to refresh or leave the page.
    // Limitation: this only discourages leaving; it does not fully prevent cheating.
    window.onbeforeunload = function () {
      return 'If you leave this page, your quiz may be submitted or interrupted.';
    };
  </script>
</body>
</html>
