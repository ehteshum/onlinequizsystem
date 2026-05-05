<?php
// teacher/add_question.php
session_start();
// Only teachers
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
if ($quiz_id <= 0) {
    die('Invalid quiz id.');
}

// Optional: verify quiz belongs to this teacher
$stmt = $mysqli->prepare('SELECT id, title FROM quizzes WHERE id = ? AND created_by = ? LIMIT 1');
$stmt->bind_param('ii', $quiz_id, $_SESSION['user_id']);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
if (!$quiz) {
    die('Quiz not found or access denied.');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = trim($_POST['question_text'] ?? '');
    $correct_index = (int)($_POST['correct'] ?? 0);
    $options = [];
    for ($i=1;$i<=4;$i++) {
        $options[$i] = trim($_POST['option_'.$i] ?? '');
    }
    if ($question_text === '') $errors[] = 'Question text is required.';
    foreach ($options as $opt) if ($opt === '') $errors[] = 'All options are required.';
    if ($correct_index < 1 || $correct_index > 4) $errors[] = 'Select a correct answer.';

    if (empty($errors)) {
        // Insert question
        $qstmt = $mysqli->prepare('INSERT INTO questions (quiz_id, question_text, question_type) VALUES (?, ?, ?)');
        $qtype = 'mcq';
        $qstmt->bind_param('iss', $quiz_id, $question_text, $qtype);
        if ($qstmt->execute()) {
            $question_id = $qstmt->insert_id;
            // Insert options
            $ostmt = $mysqli->prepare('INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)');
            foreach ($options as $index => $text) {
                $is_correct = ($index === $correct_index) ? 1 : 0;
                $ostmt->bind_param('isi', $question_id, $text, $is_correct);
                $ostmt->execute();
            }
            header('Location: manage_questions.php?quiz_id=' . $quiz_id);
            exit;
        } else {
            $errors[] = 'Database error inserting question.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Question to <?=htmlspecialchars($quiz['title'])?></title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5}
    .wrap{width:640px;margin:30px auto;background:#fff;padding:20px;border-radius:6px}
    textarea,input{width:100%;padding:8px;margin:6px 0;border:1px solid #ccc;border-radius:4px}
    .row{display:flex;gap:10px}
    .col{flex:1}
    button{padding:10px 14px;background:#007bff;color:#fff;border:0;border-radius:4px}
    .errors{color:#b00020}
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <h2>Add Question to: <?=htmlspecialchars($quiz['title'])?></h2>
    <?php if (!empty($errors)): ?>
      <div class="errors"><?=htmlspecialchars(implode("\n", $errors))?></div>
    <?php endif; ?>
    <form method="post" action="">
      <label>Question</label>
      <textarea name="question_text" rows="3" required><?=htmlspecialchars($_POST['question_text'] ?? '')?></textarea>
      <div class="row">
        <div class="col">
          <label>Option 1</label>
          <input type="text" name="option_1" value="<?=htmlspecialchars($_POST['option_1'] ?? '')?>" required>
        </div>
        <div class="col">
          <label>Option 2</label>
          <input type="text" name="option_2" value="<?=htmlspecialchars($_POST['option_2'] ?? '')?>" required>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <label>Option 3</label>
          <input type="text" name="option_3" value="<?=htmlspecialchars($_POST['option_3'] ?? '')?>" required>
        </div>
        <div class="col">
          <label>Option 4</label>
          <input type="text" name="option_4" value="<?=htmlspecialchars($_POST['option_4'] ?? '')?>" required>
        </div>
      </div>
      <label>Correct Answer</label>
      <select name="correct" required>
        <option value="">-- Select correct option --</option>
        <option value="1" <?php if(($_POST['correct'] ?? '')==='1') echo 'selected'; ?>>Option 1</option>
        <option value="2" <?php if(($_POST['correct'] ?? '')==='2') echo 'selected'; ?>>Option 2</option>
        <option value="3" <?php if(($_POST['correct'] ?? '')==='3') echo 'selected'; ?>>Option 3</option>
        <option value="4" <?php if(($_POST['correct'] ?? '')==='4') echo 'selected'; ?>>Option 4</option>
      </select>
      <div style="margin-top:10px">
        <button type="submit">Add Question</button>
        <a style="margin-left:10px" href="quizzes.php">Cancel</a>
      </div>
    </form>
  </div>
</body>
</html>
