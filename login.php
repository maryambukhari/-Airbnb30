<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        echo "Login successful!";
        echo "<script>setTimeout(() => window.location = 'index.php', 2000);</script>";
    } else {
        echo "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #FF385C, #E61E4D); color: #333; animation: fadeIn 1s; }
        header { background: #FFF; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        form { max-width: 400px; margin: 40px auto; background: #FFF; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); animation: slideIn 0.5s; }
        input { display: block; width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #DDD; border-radius: 20px; transition: border 0.3s; }
        input:focus { border-color: #FF385C; }
        button { background: #FF385C; color: #FFF; border: none; padding: 15px; border-radius: 30px; cursor: pointer; width: 100%; transition: background 0.3s; }
        button:hover { background: #E61E4D; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @media (max-width: 768px) { form { margin: 20px; } }
    </style>
</head>
<body>
    <header>
        <h1 style="text-align: center; color: #FF385C;">Login</h1>
    </header>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
