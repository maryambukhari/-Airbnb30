<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location = 'login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT b.*, p.title FROM bookings b JOIN properties p ON b.property_id = p.id WHERE b.user_id = :user_id ORDER BY b.created_at DESC");
$stmt->execute([':user_id' => $user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #FF385C, #BD1A4D); color: #333; animation: fadeIn 1s; }
        header { background: #FFF; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .bookings { padding: 40px; }
        table { width: 100%; border-collapse: collapse; background: #FFF; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); animation: fadeInUp 1s; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #DDD; }
        th { background: #FF385C; color: #FFF; }
        tr:hover { background: #FEE; transition: background 0.3s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) { table { font-size: 14px; } }
    </style>
</head>
<body>
    <header>
        <h1 style="text-align: center; color: #FF385C;">Your Bookings</h1>
    </header>
    <div class="bookings">
        <?php if (empty($bookings)): ?>
            <p>No bookings yet.</p>
        <?php else: ?>
            <table>
                <tr><th>Title</th><th>Check-in</th><th>Check-out</th><th>Guests</th><th>Total Price</th><th>Status</th></tr>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['title']); ?></td>
                        <td><?php echo $booking['check_in']; ?></td>
                        <td><?php echo $booking['check_out']; ?></td>
                        <td><?php echo $booking['guests']; ?></td>
                        <td>$<?php echo $booking['total_price']; ?></td>
                        <td><?php echo ucfirst($booking['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
    <script>
        document.querySelectorAll('tr').forEach(tr => {
            tr.addEventListener('mouseover', () => tr.style.color = '#FF385C');
            tr.addEventListener('mouseout', () => tr.style.color = '#333');
        });
    </script>
</body>
</html>
