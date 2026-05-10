<?php
/**
 * teacher/leaderboard.php
 * Quiz-wise leaderboard for teacher
 * - Shows list of quizzes created by the logged-in teacher
 * - Displays leaderboard for selected quiz with rank, student name, and score
 * - Ordered by highest score first
 */

session_start();

// Check if user is authenticated and is a teacher
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Get teacher ID from session
$teacher_id = $_SESSION['user_id'];

// Get selected quiz ID from URL parameter
$selected_quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : null;

// Initialize variables
$quiz_details = null;
$leaderboard_data = [];

// Fetch all quizzes created by this teacher that have attempts
$quizzes_sql = 'SELECT DISTINCT q.id, q.title 
                FROM quizzes q
                INNER JOIN attempts a ON q.id = a.quiz_id
                WHERE q.created_by = ?
                ORDER BY q.title ASC';
$quizzes_stmt = $mysqli->prepare($quizzes_sql);
$quizzes_stmt->bind_param('i', $teacher_id);
$quizzes_stmt->execute();
$quizzes_result = $quizzes_stmt->get_result();
$quizzes = [];
while ($row = $quizzes_result->fetch_assoc()) {
    $quizzes[] = $row;
}
$quizzes_stmt->close();

// If a quiz is selected, fetch its leaderboard with ranking
if ($selected_quiz_id) {
    // Get quiz details (verify it belongs to this teacher)
    $quiz_sql = 'SELECT id, title FROM quizzes WHERE id = ? AND created_by = ?';
    $quiz_stmt = $mysqli->prepare($quiz_sql);
    $quiz_stmt->bind_param('ii', $selected_quiz_id, $teacher_id);
    $quiz_stmt->execute();
    $quiz_result = $quiz_stmt->get_result();
    
    if ($quiz_result->num_rows > 0) {
        $quiz_details = $quiz_result->fetch_assoc();
        
        // Fetch leaderboard for this quiz ordered by score descending
        // Generate rank based on score order
        $leaderboard_sql = 'SELECT 
                              u.name AS student_name,
                              a.score
                           FROM attempts a
                           JOIN users u ON a.user_id = u.id
                           WHERE a.quiz_id = ?
                           ORDER BY a.score DESC, a.end_time DESC';
        $leaderboard_stmt = $mysqli->prepare($leaderboard_sql);
        $leaderboard_stmt->bind_param('i', $selected_quiz_id);
        $leaderboard_stmt->execute();
        $leaderboard_result = $leaderboard_stmt->get_result();
        
        // Build leaderboard data with rank
        $rank = 1;
        while ($row = $leaderboard_result->fetch_assoc()) {
            $row['rank'] = $rank;
            $leaderboard_data[] = $row;
            $rank++;
        }
        $leaderboard_stmt->close();
    }
    $quiz_stmt->close();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Leaderboard</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5;margin:0}
    .wrap{max-width:900px;margin:30px auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
    .btn{display:inline-block;padding:8px 12px;border-radius:6px;text-decoration:none;color:#fff;background:#6c757d}
    .top{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:14px}
    .quiz-list{margin-bottom:20px}
    .quiz-list h3{margin:0 0 12px 0;color:#333;font-size:16px}
    .quiz-buttons{display:flex;flex-wrap:wrap;gap:10px}
    .quiz-btn{display:inline-block;padding:8px 12px;border-radius:6px;text-decoration:none;color:#fff;background:#6c757d;border:2px solid #6c757d;cursor:pointer;font-size:14px}
    .quiz-btn:hover{background:#5a6268;border-color:#5a6268}
    .quiz-btn.active{background:#007bff;border-color:#007bff}
    .leaderboard-section{margin-top:20px;display:none}
    .leaderboard-section.active{display:block}
    .leaderboard-header{background:#f8f9fa;padding:12px;border-radius:6px;margin-bottom:15px;border-left:4px solid #007bff}
    .leaderboard-header h3{margin:0;color:#333}
    .empty-message{text-align:center;padding:20px;color:#999}
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <div class="top">
      <h2 style="margin:0">Leaderboard</h2>
      <a class="btn" href="dashboard.php">Back to Dashboard</a>
    </div>

    <!-- Quiz Selection Section -->
    <div class="quiz-list">
      <h3>Select a Quiz:</h3>
      <div class="quiz-buttons">
        <?php if (!empty($quizzes)): ?>
          <?php foreach ($quizzes as $quiz): ?>
            <a href="?quiz_id=<?= $quiz['id'] ?>" 
               class="quiz-btn <?= ($selected_quiz_id == $quiz['id']) ? 'active' : '' ?>">
              <?= htmlspecialchars($quiz['title']) ?>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-message">No quizzes available with attempts yet.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Leaderboard Section (shown only when a quiz is selected) -->
    <?php if ($selected_quiz_id && $quiz_details): ?>
      <div class="leaderboard-section active">
        <div class="leaderboard-header">
          <h3><?= htmlspecialchars($quiz_details['title']) ?></h3>
        </div>
        
        <?php if (!empty($leaderboard_data)): ?>
          <table>
            <thead>
              <tr>
                <th>Rank</th>
                <th>Student Name</th>
                <th>Score</th>
              </tr>
            </thead>
            <tbody>
              <!-- Display each leaderboard entry with rank -->
              <?php foreach ($leaderboard_data as $entry): ?>
                <tr>
                  <td><?= $entry['rank'] ?></td>
                  <td><?= htmlspecialchars($entry['student_name']) ?></td>
                  <td><?= htmlspecialchars($entry['score']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="empty-message">No attempts found for this quiz yet.</div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</body>
</html>