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

function store_answer_for_question($mysqli, $attempt_id, $question_id, $selected_option_id)
{
  // Keep one stored answer per question by replacing any previous row.
  $delete_stmt = require_statement($mysqli->prepare('DELETE FROM answers WHERE attempt_id = ? AND question_id = ?'), $mysqli, 'Delete stored answer');
  $delete_stmt->bind_param('ii', $attempt_id, $question_id);
  if (!$delete_stmt->execute()) {
    die('Could not clear previous answer: ' . $mysqli->error);
  }

  if ($selected_option_id === null) {
    $insert_stmt = require_statement($mysqli->prepare('INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES (?, ?, NULL)'), $mysqli, 'Insert unanswered question');
    $insert_stmt->bind_param('ii', $attempt_id, $question_id);
  } else {
    $insert_stmt = require_statement($mysqli->prepare('INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES (?, ?, ?)'), $mysqli, 'Insert stored answer');
    $insert_stmt->bind_param('iii', $attempt_id, $question_id, $selected_option_id);
  }

  if (!$insert_stmt->execute()) {
    die('Could not store answer: ' . $mysqli->error);
  }
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

// Load the quiz progress for this quiz into the session.
// The current question index is the source of truth for navigation.
if (!isset($_SESSION['quiz_progress'])) {
  $_SESSION['quiz_progress'] = [];
}

if (!isset($_SESSION['quiz_progress'][$quiz_id])) {
  $question_ids_stmt = require_statement($mysqli->prepare('SELECT id FROM questions WHERE quiz_id = ? ORDER BY id ASC'), $mysqli, 'Question id fetch');
  $question_ids_stmt->bind_param('i', $quiz_id);
  $question_ids_stmt->execute();
  $question_ids_result = $question_ids_stmt->get_result();

  $question_ids = [];
  while ($row = $question_ids_result->fetch_assoc()) {
    $question_ids[] = (int)$row['id'];
  }

  if (empty($question_ids)) {
    die('No questions found for this quiz.');
  }

  $_SESSION['quiz_progress'][$quiz_id] = [
    'attempt_id' => $attempt_id,
    'current_index' => 0,
    'question_ids' => $question_ids,
    'answers' => []
  ];
} else {
  // Keep the existing attempt linked to the quiz if the session already has it.
  $_SESSION['quiz_progress'][$quiz_id]['attempt_id'] = $attempt_id;
}

$progress = &$_SESSION['quiz_progress'][$quiz_id];
$question_ids = $progress['question_ids'];
$current_index = (int)($progress['current_index'] ?? 0);
$total_questions = count($question_ids);

if ($current_index >= $total_questions) {
  header('Location: submit_quiz.php?quiz_id=' . $quiz_id);
  exit;
}

$current_question_id = (int)$question_ids[$current_index];

// Handle navigation first so every click saves the current answer before moving on.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $posted_question_id = (int)($_POST['question_id'] ?? 0);
  $action = $_POST['action'] ?? 'next';

  // Reject replayed or out-of-order submissions from browser history.
  if ($posted_question_id !== $current_question_id) {
    header('Location: start_quiz.php?quiz_id=' . $quiz_id);
    exit;
  }

  $selected_option_id = isset($_POST['selected_option_id']) && $_POST['selected_option_id'] !== ''
    ? (int)$_POST['selected_option_id']
    : null;

  // Save the answer immediately after clicking Next or Submit.
  store_answer_for_question($mysqli, $attempt_id, $current_question_id, $selected_option_id);
  $progress['answers'][$current_question_id] = $selected_option_id;

  if ($action === 'submit' || $current_index >= $total_questions - 1) {
    // Final question or timer expiration: send the attempt to the submit handler.
    header('Location: submit_quiz.php?quiz_id=' . $quiz_id);
    exit;
  }

  // Move forward only. Going back is intentionally not supported.
  $progress['current_index'] = $current_index + 1;
  header('Location: start_quiz.php?quiz_id=' . $quiz_id);
  exit;
}

// Load the current question and its options only.
$question_stmt = require_statement($mysqli->prepare('SELECT id, question_text FROM questions WHERE id = ? AND quiz_id = ? LIMIT 1'), $mysqli, 'Current question fetch');
$question_stmt->bind_param('ii', $current_question_id, $quiz_id);
$question_stmt->execute();
$current_question = $question_stmt->get_result()->fetch_assoc();

if (!$current_question) {
  die('Question not found.');
}

$options_stmt = require_statement($mysqli->prepare('SELECT id, option_text FROM options WHERE question_id = ? ORDER BY id ASC'), $mysqli, 'Option fetch');
$options_stmt->bind_param('i', $current_question_id);
$options_stmt->execute();
$options = $options_stmt->get_result();
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
    .question-count{margin:0 0 14px 0;color:#6b7280;font-size:14px}
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

    <!-- One-question-at-a-time form. The server decides which question is current. -->
    <form method="post" action="start_quiz.php?quiz_id=<?=htmlspecialchars($quiz_id)?>" id="quizForm">
      <input type="hidden" name="quiz_id" value="<?=htmlspecialchars($quiz_id)?>">
      <input type="hidden" name="question_id" value="<?=htmlspecialchars($current_question_id)?>">
      <input type="hidden" name="action" id="quizAction" value="next">

      <div class="question">
        <!-- Show only the current question for this session step. -->
        <p class="question-count">Question <?=($current_index + 1)?> of <?=$total_questions?></p>
        <h3 class="question-title"><?=htmlspecialchars($current_question['question_text'])?></h3>

        <?php while ($option = $options->fetch_assoc()): ?>
          <label class="option">
            <input type="radio" name="selected_option_id" value="<?=$option['id']?>">
            <?=htmlspecialchars($option['option_text'])?>
          </label>
        <?php endwhile; ?>
      </div>

      <div class="submit-row">
        <?php if ($current_index >= $total_questions - 1): ?>
          <button type="submit" onclick="return confirmSubmitQuiz();">Submit Quiz</button>
        <?php else: ?>
          <button type="submit" onclick="document.getElementById('quizAction').value='next';">Next</button>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <script>
    // Remaining seconds are calculated in PHP using start_time + duration - current_time.
    let remaining = <?= (int)$remaining_time ?>;
    const timerEl = document.getElementById('timer');
    const form = document.getElementById('quizForm');
    const actionInput = document.getElementById('quizAction');
    // Set this only for intentional form submits so refresh/close still warns.
    let suppressUnloadWarning = false;

    // Ask for confirmation only when the student taps the final Submit button.
    function confirmSubmitQuiz() {
      const confirmed = window.confirm('Are you sure you want to submit the quiz now?');

      if (confirmed) {
        suppressUnloadWarning = true;
        actionInput.value = 'submit';
      }

      return confirmed;
    }

    // Keep only one answer per question in the UI and highlight the selected option.
    document.querySelectorAll('.option input[type="radio"]').forEach((radio) => {
      radio.addEventListener('change', () => {
        document.querySelectorAll('.option').forEach((label) => label.classList.remove('selected'));
        radio.closest('.option').classList.add('selected');
      });

      if (radio.checked) {
        radio.closest('.option').classList.add('selected');
      }
    });

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
        // Auto submit when time ends. The current answer is saved before final grading.
        suppressUnloadWarning = true;
        actionInput.value = 'submit';
        form.submit();
      }
    }, 1000);

    // Mark any intentional quiz submission so the unload warning does not fire.
    form.addEventListener('submit', () => {
      suppressUnloadWarning = true;
    });

    // Warn only for refresh, tab close, or accidental navigation away.
    // This warning is intentionally disabled for the Next button flow.
    window.onbeforeunload = function () {
      if (suppressUnloadWarning) {
        return;
      }

      return 'If you leave this page, your quiz may be submitted or interrupted.';
    };
  </script>
</body>
</html>
