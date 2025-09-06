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

// Check if user has booked this property
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = :user_id AND property_id = :property_id");
$stmt->execute([':user_id' => $_SESSION['user_id'], ':property_id' => $property_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $booking) {
    $rating = $_POST['rating'];
    $feedback = $_POST['feedback'];

    $stmt = $pdo->prepare("INSERT INTO reviews (property_id, user_id, rating, feedback) VALUES (:property_id, :user_id, :rating, :feedback)");
    $stmt->execute([
        ':property_id' => $property_id,
        ':user_id' => $_SESSION['user_id'],
        ':rating' => $rating,
        ':feedback' => $feedback
    ]);

    // Update property rating
    $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE property_id = :property_id");
    $stmt->execute([':property_id' => $property_id]);
    $avg_rating = $stmt->fetch(PDO::FETCH_ASSOC)['avg_rating'];

    $stmt = $pdo->prepare("UPDATE properties SET rating = :rating WHERE id = :property_id");
    $stmt->execute([':rating' => $avg_rating, ':property_id' => $property_id]);

    echo "Review submitted successfully!";
    echo "<script>setTimeout(() => window.location = 'property.php?id=$property_id', 2000);</script>";
    exit;
}

// Fetch existing reviews
$stmt = $pdo->prepare("SELECT r.*, u.name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.property_id = :id ORDER BY r.created_at DESC");
$stmt->execute([':id' => $property_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - <?php echo htmlspecialchars($property['title']); ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #1E3A8A, #3B82F6);
            color: #333;
            animation: fadeIn 1s ease-in;
        }
        header {
            background: #FFF;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            text-align: center;
        }
        h1 {
            color: #F97316;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #FFF;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            animation: slideIn 0.5s ease-out;
        }
        .review-form {
            margin-bottom: 40px;
        }
        .review-form select, .review-form textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #DDD;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s, transform 0.3s;
        }
        .review-form select:focus, .review-form textarea:focus {
            border-color: #F97316;
            transform: scale(1.02);
            outline: none;
        }
        .review-form textarea {
            height: 100px;
            resize: vertical;
        }
        .review-form button {
            background: #F97316;
            color: #FFF;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 18px;
            transition: background 0.3s, transform 0.3s;
        }
        .review-form button:hover {
            background: #EA580C;
            transform: scale(1.1);
        }
        .reviews-list {
            margin-top: 20px;
        }
        .review {
            background: #F5F5F5;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            animation: fadeInUp 0.5s ease-out;
        }
        .review:hover {
            transform: translateY(-5px);
        }
        .review p {
            margin: 5px 0;
        }
        .review .rating {
            color: #F97316;
            font-weight: bold;
        }
        .review .name {
            color: #1E3A8A;
            font-size: 14px;
        }
        .error {
            color: #DC2626;
            text-align: center;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 768px) {
            .container { margin: 20px; padding: 15px; }
            .review-form button { padding: 12px; font-size: 16px; }
        }
    </style>
</head>
<body>
    <header>
        <h1>Reviews for <?php echo htmlspecialchars($property['title']); ?></h1>
    </header>
    <div class="container">
        <?php if ($booking): ?>
            <div class="review-form">
                <h2>Submit Your Review</h2>
                <form method="POST">
                    <select name="rating" required>
                        <option value="">Select Rating</option>
                        <option value="1">1 Star</option>
                        <option value="2">2 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="5">5 Stars</option>
                    </select>
                    <textarea name="feedback" placeholder="Write your feedback..." required></textarea>
                    <button type="submit">Submit Review</button>
                </form>
            </div>
        <?php else: ?>
            <p class="error">You must book this property to leave a review.</p>
        <?php endif; ?>
        <div class="reviews-list">
            <h2>Existing Reviews</h2>
            <?php if (empty($reviews)): ?>
                <p>No reviews yet.</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review">
                        <p class="rating">Rating: <?php echo htmlspecialchars($review['rating']); ?> / 5</p>
                        <p><?php echo nl2br(htmlspecialchars($review['feedback'])); ?></p>
                        <p class="name">By: <?php echo htmlspecialchars($review['name']); ?> on <?php echo $review['created_at']; ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // Animation for form submission button
        const submitBtn = document.querySelector('.review-form button');
        if (submitBtn) {
            submitBtn.addEventListener('click', () => {
                submitBtn.style.transform = 'scale(1.1)';
                setTimeout(() => submitBtn.style.transform = 'scale(1)', 200);
            });
        }

        // Hover effect for reviews
        document.querySelectorAll('.review').forEach(review => {
            review.addEventListener('mouseover', () => {
                review.style.background = '#E5E7EB';
                review.style.boxShadow = '0 0 15px #F97316';
            });
            review.addEventListener('mouseout', () => {
                review.style.background = '#F5F5F5';
                review.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            });
        });

        // Form validation animation
        const form = document.querySelector('.review-form form');
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    form.style.animation = 'shake 0.3s';
                    setTimeout(() => form.style.animation = '', 300);
                }
            });
        }

        // Define shake animation
        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
