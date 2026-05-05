<?php
$basePath = '/onlinequizsystem';
?>
<style>
  .navbar { background-color: #343a40; padding: 15px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
  .navbar .nav-links { display: flex; gap: 20px; }
  .navbar a { color: #f8f9fa; text-decoration: none; font-size: 16px; transition: color 0.3s; }
  .navbar a:hover { color: #17a2b8; }
  .navbar .logout { background: #dc3545; padding: 6px 12px; border-radius: 4px; font-weight: bold; }
  .navbar .logout:hover { background: #c82333; color: white;}
</style>
<nav class="navbar">
  <div class="nav-links">
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
      <a href="<?=$basePath?>/student/dashboard.php">Dashboard</a>
      <a href="<?=$basePath?>/student/quizzes.php">Available Quizzes</a>
      <a href="<?=$basePath?>/student/leaderboard.php">Leaderboard</a>
    <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher'): ?>
      <a href="<?=$basePath?>/teacher/dashboard.php">Dashboard</a>
      <a href="<?=$basePath?>/teacher/quizzes.php">My Quizzes</a>
      <a href="<?=$basePath?>/teacher/create_quiz.php">Create Quiz</a>
    <?php endif; ?>
  </div>
  <div>
    <?php if(isset($_SESSION['role'])): ?>
      <span style="color: #fff; margin-right: 15px;">Welcome, <?=htmlspecialchars($_SESSION['user_name'] ?? 'User')?>!</span>
      <a class="logout" href="<?=$basePath?>/auth/logout.php">Logout</a>
    <?php endif; ?>
  </div>
</nav>