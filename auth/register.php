<?php
// auth/register.php
// Simple user registration page (HTML + CSS + PHP)
session_start();
require_once __DIR__ . '/../config/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic input retrieval and trimming
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    // Simple validation
    if ($name === '') $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if (!in_array($role, ['teacher','student'])) $errors[] = 'Invalid role.';

    if (empty($errors)) {
        // Hash the password securely
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user using prepared statement to avoid SQL injection
        $stmt = $mysqli->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $name, $email, $password_hash, $role);
        if ($stmt->execute()) {
            // Registration successful — redirect to login
            header('Location: login.php?registered=1');
            exit;
        } else {
            // Handle duplicate email or other DB errors
            if ($mysqli->errno === 1062) {
                $errors[] = 'An account with that email already exists.';
            } else {
                $errors[] = 'Database error. Try again.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register - Quiz System</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5}
    .wrap{width:320px;margin:60px auto;background:#fff;padding:20px;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,.1)}
    h2{text-align:center;margin:0 0 12px}
    input, select{width:100%;padding:8px;margin:6px 0;border:1px solid #ccc;border-radius:4px}
    button{width:100%;padding:10px;background:#28a745;color:#fff;border:0;border-radius:4px;cursor:pointer}
    .errors{color:#b00020;margin-bottom:8px}
    .small{font-size:90%;text-align:center;margin-top:10px}
  </style>
</head>
<body>
  <div class="wrap">
    <h2>Create Account</h2>
    <?php if (!empty($errors)): ?>
      <div class="errors"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
    <?php endif; ?>
    <form method="post" action="">
      <!-- Name field -->
      <input type="text" name="name" placeholder="Full name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
      <!-- Email field -->
      <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
      <!-- Password field -->
      <input type="password" name="password" placeholder="Password (min 6 chars)" required>
      <!-- Role dropdown -->
      <select name="role">
        <option value="student" <?php if (($_POST['role'] ?? '') === 'student') echo 'selected'; ?>>Student</option>
        <option value="teacher" <?php if (($_POST['role'] ?? '') === 'teacher') echo 'selected'; ?>>Teacher</option>
      </select>
      <button type="submit">Register</button>
    </form>
    <div class="small">Already have an account? <a href="login.php">Login</a></div>
  </div>
</body>
</html>
