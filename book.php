<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location = 'login.php';</script>";
    exit;
}

if (!isset($_GET['id'])) {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

$property_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = :id");
$stmt->execute([':id' => $property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = $_POST['guests'];

    // Calculate nights
    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
    $nights = $date1->diff($date2)->days;
    $total_price = $property['price'] * $nights;

    // Insert booking
    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, property_id, check_in, check_out, guests, total_price, status) VALUES (:user_id, :property_id, :check_in, :check_out, :guests, :total_price, 'confirmed')");
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':property_id' => $property_id,
        ':check_in' => $check_in,
        ':check_out' => $check_out,
        ':guests' => $guests,
        ':total_price' => $total_price
    ]);

    echo "Booking confirmed!";
    echo "<script>setTimeout(() => window.location = 'dashboard.php', 2000);</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Property</title>
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
        <h1 style="text-align: center; color: #FF385C;">Book <?php echo htmlspecialchars($property['title']); ?></h1>
    </header>
    <form method="POST">
        <input type="date" name="check_in" required>
        <input type="date" name="check_out" required>
        <input type="number" name="guests" placeholder="Number of Guests" required min="1">
        <button type="submit">Confirm Booking</button>
    </form>
    <script>
        // Add shake effect on submit if invalid
        document.querySelector('form').addEventListener('submit', (e) => {
            if (!e.target.checkValidity()) {
                e.target.style.animation = 'shake 0.5s';
                setTimeout(() => e.target.style.animation = '', 500);
            }
        });
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
    </script>
</body>
</html>
