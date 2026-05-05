<?php
// student/quizzes.php
// Show all available quizzes for students
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

// Fetch all quizzes with teacher names and question count for display
$sql = 'SELECT q.id, q.title, q.duration, u.name AS teacher_name, q.created_at,
        (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) AS question_count
        FROM quizzes q
        LEFT JOIN users u ON q.created_by = u.id
        ORDER BY q.created_at DESC';
$result = $mysqli->query($sql);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Available Quizzes</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5;margin:0}
    .wrap{max-width:1000px;margin:30px auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
    .btn{display:inline-block;padding:8px 12px;border-radius:6px;text-decoration:none;color:#fff;background:#007bff}
    .top{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:14px}
    .gray{background:#6c757d}
    
    /* Modal styles */
    .modal { display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5); align-items:center; justify-content:center; }
    .modal.show { display:flex; }
    .modal-content { background-color:#fff; padding:20px 30px; border-radius:8px; width:400px; max-width:90%; position:relative; box-shadow:0 4px 15px rgba(0,0,0,0.2); }
    .close-btn { position:absolute; top:15px; right:20px; color:#aaa; font-size:24px; font-weight:bold; cursor:pointer; }
    .close-btn:hover, .close-btn:focus { color:#333; text-decoration:none; }
    .modal-title { margin-top:0; font-size:22px; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:10px; }
    .modal-details { margin-bottom:20px; line-height:1.6; font-size:16px; }
    .modal-actions { text-align:right; }
    .btn-start { background:#28a745; font-size:16px; padding:10px 20px; border:none; cursor:pointer; }
    .btn-start:hover { background:#218838; }
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <div class="top">
      <h2 style="margin:0">Available Quizzes</h2>
      <div>
        <a class="btn gray" href="dashboard.php">Back to Dashboard</a>
        <a class="btn gray" href="../auth/logout.php">Logout</a>
      </div>
    </div>
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Teacher</th>
          <th>Duration</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?=htmlspecialchars($row['title'])?></td>
            <td><?=htmlspecialchars($row['teacher_name'] ?? 'Unknown')?></td>
            <td><?=htmlspecialchars($row['duration'])?> min</td>
            <td>
              <a href="#" class="btn view-quiz-btn" 
                 data-id="<?=$row['id']?>" 
                 data-title="<?=htmlspecialchars($row['title'])?>" 
                 data-duration="<?=htmlspecialchars($row['duration'])?>" 
                 data-questions="<?=$row['question_count']?>">Start Quiz</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="4">No quizzes available yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Quiz Details Modal -->
  <div id="quizModal" class="modal">
    <div class="modal-content">
      <span class="close-btn">&times;</span>
      <h3 class="modal-title">Quiz Details</h3>
      <div class="modal-details">
        <p><strong>Title:</strong> <span id="m-title"></span></p>
        <p><strong>Duration:</strong> <span id="m-duration"></span> minutes</p>
        <p><strong>Questions:</strong> <span id="m-questions"></span></p>
      </div>
      <div class="modal-actions">
        <a id="m-start-btn" href="#" class="btn btn-start">Start</a>
      </div>
    </div>
  </div>

  <script>
    const modal = document.getElementById('quizModal');
    const closeBtn = document.querySelector('.close-btn');
    const viewButtons = document.querySelectorAll('.view-quiz-btn');
    
    // Open modal and set data
    viewButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        
        const id = this.getAttribute('data-id');
        const title = this.getAttribute('data-title');
        const duration = this.getAttribute('data-duration');
        const questions = this.getAttribute('data-questions');
        
        document.getElementById('m-title').textContent = title;
        document.getElementById('m-duration').textContent = duration;
        document.getElementById('m-questions').textContent = questions;
        
        document.getElementById('m-start-btn').href = 'start_quiz.php?quiz_id=' + id;
        
        modal.classList.add('show');
      });
    });

    // Close modal
    closeBtn.addEventListener('click', () => modal.classList.remove('show'));
    window.addEventListener('click', (e) => {
      if (e.target === modal) modal.classList.remove('show');
    });
  </script>
</body>
</html>
