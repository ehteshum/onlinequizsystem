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
  // Reuse the existing attempt start time when student returns to the quiz.
  $existing_attempt_stmt = require_statement($mysqli->prepare('SELECT start_time FROM attempts WHERE id = ? AND user_id = ? AND quiz_id = ? LIMIT 1'), $mysqli, 'Existing attempt lookup');
  $existing_attempt_stmt->bind_param('iii', $attempt_id, $_SESSION['user_id'], $quiz_id);
  $existing_attempt_stmt->execute();
  $existing_attempt = $existing_attempt_stmt->get_result()->fetch_assoc();

  if ($existing_attempt && !empty($existing_attempt['start_time'])) {
    $start_time = $existing_attempt['start_time'];
  } else {
    // Fallback for unexpected data mismatch: start a fresh attempt.
    $start_time = date('Y-m-d H:i:s');
    $attempt_stmt = require_statement($mysqli->prepare('INSERT INTO attempts (user_id, quiz_id, score, start_time) VALUES (?, ?, 0, ?)'), $mysqli, 'Attempt recreation');
    $attempt_stmt->bind_param('iis', $_SESSION['user_id'], $quiz_id, $start_time);
    if (!$attempt_stmt->execute()) {
      die('Could not create attempt: ' . mysqli_error($mysqli));
    }
    $attempt_id = $attempt_stmt->insert_id;
    $_SESSION['attempt_id'] = $attempt_id;
  }
} else {
    // Create a new attempt and store the attempt id in session
    $start_time = date('Y-m-d H:i:s');
  $attempt_stmt = require_statement($mysqli->prepare('INSERT INTO attempts (user_id, quiz_id, score, start_time) VALUES (?, ?, 0, ?)'), $mysqli, 'Attempt creation');
    $attempt_stmt->bind_param('iis', $_SESSION['user_id'], $quiz_id, $start_time);
    if (!$attempt_stmt->execute()) {
        die('Could not create attempt: ' . mysqli_error($mysqli));
    }
    $attempt_id = $attempt_stmt->insert_id;
    $_SESSION['attempt_id'] = $attempt_id;
    $_SESSION['attempt_quiz_id'] = $quiz_id;
}

  // Calculate remaining time in seconds:
  // remaining_time = (start_time + duration) - current_time
  $start_timestamp = strtotime($start_time);
  $duration_seconds = ((int)$quiz['duration']) * 60;
  $end_timestamp = $start_timestamp + $duration_seconds;
  $current_timestamp = time();
  $remaining_time = $end_timestamp - $current_timestamp;

  // Do not allow negative values.
  if ($remaining_time < 0) {
    $remaining_time = 0;
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
    .wrap{max-width:900px;margin:30px auto;background:#fff;padding:22px;border-radius:10px;box-shadow:0 4px 14px rgba(0,0,0,.08)}
    .top-actions{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:8px}
    .top-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .timer{padding:8px 12px;border:1px solid #f0c6c6;border-radius:999px;background:#fff4f4;font-size:15px;font-weight:bold;color:#b00020;white-space:nowrap}
    .question{margin:16px 0;padding:16px;border:1px solid #e8ebef;border-radius:10px;background:#fafbfc}
    .question-title{margin:0 0 12px 0;font-size:18px;line-height:1.4;color:#1f2937}
    .option{display:flex;align-items:center;gap:10px;margin:10px 0;padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;cursor:pointer;transition:all .15s ease}
    .option:hover{border-color:#cbd5e1;background:#f8fafc}
    .option input{margin:0;accent-color:#2563eb}
    .option.selected{border-color:#2563eb;background:#eff6ff;box-shadow:inset 0 0 0 1px #2563eb}
    .submit-row{margin-top:18px;display:flex;justify-content:flex-end}
    button{padding:11px 20px;border:0;border-radius:6px;background:#2563eb;color:#fff;cursor:pointer;font-weight:600}
    button:hover{background:#1d4ed8}
    .btn{display:inline-block;padding:8px 12px;border-radius:6px;text-decoration:none;color:#fff;background:#6c757d}
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <div class="top-actions">
      <h2 style="margin:0"><?=htmlspecialchars($quiz['title'])?></h2>
      <div class="top-right">
        <div class="timer" id="timer"></div>
        <a class="btn" href="quizzes.php">Back to Quizzes</a>
      </div>
    </div>

    <form method="post" action="submit_quiz.php" id="quizForm">
      <input type="hidden" name="quiz_id" value="<?=htmlspecialchars($quiz_id)?>">

      <?php $index = 1; while ($question = $questions->fetch_assoc()): ?>
        <div class="question">
          <!-- Display each question with its options -->
          <h3 class="question-title"><?=$index?>. <?=htmlspecialchars($question['question_text'])?></h3>
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

      <div class="submit-row">
        <button type="submit">Submit Quiz</button>
      </div>
    </form>
  </div>

  <script>
    // Remaining seconds are calculated in PHP using start_time + duration - current_time.
    let remaining = <?= (int)$remaining_time ?>;
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

    // Highlight selected option within each question card
    document.querySelectorAll('.question').forEach((questionCard) => {
      const radios = questionCard.querySelectorAll('input[type="radio"]');
      radios.forEach((radio) => {
        radio.addEventListener('change', () => {
          questionCard.querySelectorAll('.option').forEach((label) => label.classList.remove('selected'));
          if (radio.checked) {
            radio.closest('.option').classList.add('selected');
          }
        });

        if (radio.checked) {
          radio.closest('.option').classList.add('selected');
        }
      });
    });
  </script>
</body>
</html>
