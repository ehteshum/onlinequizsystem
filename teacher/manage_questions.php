<?php
// teacher/manage_questions.php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$teacher_id = $_SESSION['user_id'];

// Verify ownership
$stmt = $mysqli->prepare('SELECT id, title FROM quizzes WHERE id = ? AND created_by = ?');
$stmt->bind_param('ii', $quiz_id, $teacher_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
if (!$quiz) {
    die('Quiz not found or access denied.');
}

// Fetch questions
$q_stmt = $mysqli->prepare('SELECT id, question_text, question_type FROM questions WHERE quiz_id = ? ORDER BY id ASC');
$q_stmt->bind_param('i', $quiz_id);
$q_stmt->execute();
$questions = $q_stmt->get_result();

// Handle question deletion
if (isset($_GET['delete_q'])) {
    $del_q_id = (int)$_GET['delete_q'];
    // verify the question belongs to this quiz
    $chk_q = $mysqli->prepare('SELECT id FROM questions WHERE id = ? AND quiz_id = ?');
    $chk_q->bind_param('ii', $del_q_id, $quiz_id);
    $chk_q->execute();
    if ($chk_q->get_result()->fetch_assoc()) {
        $del = $mysqli->prepare('DELETE FROM questions WHERE id = ?');
        $del->bind_param('i', $del_q_id);
        $del->execute();
    }
    header("Location: manage_questions.php?quiz_id=$quiz_id");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Manage Questions</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5;margin:0}
    .wrap{max-width:900px;margin:30px auto;background:#fff;padding:20px;border-radius:6px}
    table{width:100%;border-collapse:collapse;margin-top:15px}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
    .btn{padding:6px 10px;background:#007bff;color:#fff;text-decoration:none;border-radius:4px;font-size:14px;display:inline-block}
    .btn-danger{background:#dc3545}
    .btn-warning{background:#ffc107;color:#000}
    .top{display:flex;justify-content:space-between;align-items:center}
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <div class="top">
      <h2>Questions for: <?=htmlspecialchars($quiz['title'])?></h2>
      <div>
        <a class="btn" href="add_question.php?quiz_id=<?=$quiz_id?>">Add Question</a>
        <a class="btn view-quiz-btn gray" style="background:#6c757d" href="quizzes.php">Back to Quizzes</a>
      </div>
    </div>
    
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Question</th>
          <th>Type</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($questions->num_rows > 0): $count = 1; ?>
        <?php while ($q = $questions->fetch_assoc()): ?>
          <tr>
            <td><?=$count++?></td>
            <td><?=htmlspecialchars($q['question_text'])?></td>
            <td><?=htmlspecialchars($q['question_type'])?></td>
            <td>
              <a class="btn btn-warning" href="edit_question.php?id=<?=$q['id']?>">Edit</a>
              <a class="btn btn-danger" href="manage_questions.php?quiz_id=<?=$quiz_id?>&delete_q=<?=$q['id']?>" onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="4">No questions added yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>