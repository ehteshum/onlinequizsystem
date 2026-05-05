<?php
// teacher/edit_question.php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$question_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$teacher_id = $_SESSION['user_id'];

// Verify question and that the quiz belongs to this teacher
$stmt = $mysqli->prepare('
    SELECT q.id, q.question_text, q.quiz_id, qz.title 
    FROM questions q 
    JOIN quizzes qz ON q.quiz_id = qz.id 
    WHERE q.id = ? AND qz.created_by = ?
');
$stmt->bind_param('ii', $question_id, $teacher_id);
$stmt->execute();
$question = $stmt->get_result()->fetch_assoc();
if (!$question) {
    die('Question not found or access denied.');
}

// Fetch existing options
$opt_stmt = $mysqli->prepare('SELECT id, option_text, is_correct FROM options WHERE question_id = ? ORDER BY id ASC');
$opt_stmt->bind_param('i', $question_id);
$opt_stmt->execute();
$options_res = $opt_stmt->get_result();
$options = [];
$correct_idx = 1;

$i = 1;
while ($opt = $options_res->fetch_assoc()) {
    $options[$i] = $opt;
    if ($opt['is_correct']) {
        $correct_idx = $i;
    }
    $i++;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = trim($_POST['question_text'] ?? '');
    $correct_index = (int)($_POST['correct'] ?? 0);
    $new_options = [];
    
    for ($j=1; $j<=4; $j++) {
        $new_options[$j] = trim($_POST['option_'.$j] ?? '');
        if ($new_options[$j] === '') $errors[] = 'All option fields are required.';
    }
    
    if ($question_text === '') $errors[] = 'Question text is required.';
    if ($correct_index < 1 || $correct_index > 4) $errors[] = 'Select a correct answer.';

    if (empty($errors)) {
        // Update question text
        $upd_q = $mysqli->prepare('UPDATE questions SET question_text = ? WHERE id = ?');
        $upd_q->bind_param('si', $question_text, $question_id);
        $upd_q->execute();
        
        // Update options
        $j = 1;
        foreach ($options as $idx => $old_opt) {
            $is_c = ($j === $correct_index) ? 1 : 0;
            $opt_text = $new_options[$j];
            
            $upd_o = $mysqli->prepare('UPDATE options SET option_text = ?, is_correct = ? WHERE id = ?');
            $upd_o->bind_param('sii', $opt_text, $is_c, $old_opt['id']);
            $upd_o->execute();
            $j++;
        }
        
        header('Location: manage_questions.php?quiz_id=' . $question['quiz_id']);
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Question</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5}
    .wrap{width:640px;margin:30px auto;background:#fff;padding:20px;border-radius:6px}
    textarea,input{width:100%;padding:8px;margin:6px 0;border:1px solid #ccc;border-radius:4px;box-sizing:border-box}
    .row{display:flex;gap:10px}
    .col{flex:1}
    button{padding:10px 14px;background:#007bff;color:#fff;border:0;border-radius:4px;cursor:pointer;width:100%;font-size:16px;margin-top:10px}
    .errors{color:#b00020}
    .back{display:block;margin-top:15px;text-align:center;text-decoration:none;color:#007bff}
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <h2>Edit Question (Quiz: <?=htmlspecialchars($question['title'])?>)</h2>
    <?php if (!empty($errors)): ?>
      <div class="errors"><?=implode("<br>", array_map('htmlspecialchars', $errors))?></div>
    <?php endif; ?>
    <form method="post" action="">
      <label>Question text</label>
      <textarea name="question_text" rows="3" required><?=htmlspecialchars($_POST['question_text'] ?? $question['question_text'])?></textarea>
      
      <div class="row">
        <div class="col">
          <label>Option 1</label>
          <input type="text" name="option_1" value="<?=htmlspecialchars($_POST['option_1'] ?? $options[1]['option_text'])?>" required>
        </div>
        <div class="col">
          <label>Option 2</label>
          <input type="text" name="option_2" value="<?=htmlspecialchars($_POST['option_2'] ?? $options[2]['option_text'])?>" required>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <label>Option 3</label>
          <input type="text" name="option_3" value="<?=htmlspecialchars($_POST['option_3'] ?? $options[3]['option_text'])?>" required>
        </div>
        <div class="col">
          <label>Option 4</label>
          <input type="text" name="option_4" value="<?=htmlspecialchars($_POST['option_4'] ?? $options[4]['option_text'])?>" required>
        </div>
      </div>
      
      <label>Correct Answer</label>
      <?php $selected_correct = (int)($_POST['correct'] ?? $correct_idx); ?>
      <select name="correct" required style="width:100%;padding:8px;margin:6px 0;border:1px solid #ccc;border-radius:4px">
        <option value="1" <?=($selected_correct===1)?'selected':''?>>Option 1</option>
        <option value="2" <?=($selected_correct===2)?'selected':''?>>Option 2</option>
        <option value="3" <?=($selected_correct===3)?'selected':''?>>Option 3</option>
        <option value="4" <?=($selected_correct===4)?'selected':''?>>Option 4</option>
      </select>
      
      <button type="submit">Update Question</button>
    </form>
    <a href="manage_questions.php?quiz_id=<?=$question['quiz_id']?>" class="back">Cancel / Back</a>
  </div>
</body>
</html>