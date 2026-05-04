<?php
// index.php
// Simple landing page with links to auth pages
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quiz System</title>
  <style>
    body{font-family:Arial, sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
    .box{text-align:center}
    a{display:inline-block;margin:8px;padding:10px 14px;background:#007bff;color:#fff;text-decoration:none;border-radius:4px}
  </style>
</head>
<body>
  <div class="box">
    <h1>Online Quiz System</h1>
    <p>
      <a href="auth/register.php">Register</a>
      <a href="auth/login.php">Login</a>
    </p>
  </div>
</body>
</html>
