<?php
session_start();
include 'db.php';

$error_message = '';
$property = null;
$reviews = [];

if (!isset($_GET['id'])) {
    $error_message = 'No property ID provided. Please select a property from the listings.';
} else {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$property) {
            $error_message = 'Property not found. Please try another property.';
        } else {
            // Fetch reviews
            $stmt = $pdo->prepare("SELECT r.*, u.name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.property_id = :id ORDER BY r.created_at DESC LIMIT 5");
            $stmt->execute([':id' => $id]);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $property ? htmlspecialchars($property['title']) : 'Property Details'; ?> - Airbnb Clone</title>
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
        .details {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #FFF;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            animation: slideIn 0.5s ease-out;
        }
        .error {
            color: #DC2626;
            text-align: center;
            font-size: 18px;
            margin: 20px;
            animation: fadeIn 0.5s;
        }
        .gallery {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }
        img.main-img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        img.main-img:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }
        .info {
            margin: 20px 0;
        }
        .info h2 {
            color: #1E3A8A;
            border-bottom: 2px solid #F97316;
            padding-bottom: 10px;
        }
        .info p {
            line-height: 1.6;
        }
        .amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .amenity {
            background: #F97316;
            color: #FFF;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            transition: transform 0.3s;
        }
        .amenity:hover {
            transform: scale(1.1);
        }
        .action-buttons {
            margin: 20px 0;
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        .book-btn, .review-btn {
            background: #F97316;
            color: #FFF;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 18px;
            text-decoration: none;
            transition: background 0.3s, transform 0.3s;
        }
        .book-btn:hover, .review-btn:hover {
            background: #EA580C;
            transform: scale(1.1);
        }
        .reviews {
            margin-top: 30px;
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
        .review .rating {
            color: #F97316;
            font-weight: bold;
        }
        .review .name {
            color: #1E3A8A;
            font-size: 14px;
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
            .details { margin: 20px; padding: 15px; }
            img.main-img { height: 250px; }
            .action-buttons { flex-direction: column; }
            .book-btn, .review-btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <header>
        <h1><?php echo $property ? htmlspecialchars($property['title']) : 'Property Details'; ?></h1>
    </header>
    <div class="details">
        <?php if ($error_message): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
            <p style="text-align: center;"><a href="index.php" class="review-btn">Back to Home</a></p>
        <?php else: ?>
            <div class="gallery">
                <img class="main-img" src="<?php echo $property['image'] ?? 'https://via.placeholder.com/800x400'; ?>" alt="Property">
            </div>
            <div class="info">
                <h2>Details</h2>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?></p>
                <p><strong>Price:</strong> $<?php echo number_format($property['price'], 2); ?>/night</p>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($property['type']); ?></p>
                <p><strong>Rating:</strong> <?php echo number_format($property['rating'], 1); ?> / 5</p>
                <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                <h2>Amenities</h2>
                <div class="amenities">
                    <?php
                    $amenities = explode(',', $property['amenities']);
                    foreach ($amenities as $amenity) {
                        if (trim($amenity)) {
                            echo '<span class="amenity">' . htmlspecialchars(trim($amenity)) . '</span>';
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="action-buttons">
                <button class="book-btn" onclick="window.location.href='book.php?id=<?php echo $property['id']; ?>'">Book Now</button>
                <a href="reviews.php?id=<?php echo $property['id']; ?>" class="review-btn">View & Submit Reviews</a>
            </div>
            <div class="reviews">
                <h2>Recent Reviews</h2>
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
        <?php endif; ?>
    </div>
    <script>
        // Pulse animation for buttons
        const buttons = document.querySelectorAll('.book-btn, .review-btn');
        buttons.forEach(btn => {
            setInterval(() => {
                btn.style.transform = 'scale(1.05)';
                setTimeout(() => btn.style.transform = 'scale(1)', 300);
            }, 2000);
        });

        // Hover animation for reviews
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

        // Image zoom on click
        const mainImg = document.querySelector('.main-img');
        if (mainImg) {
            mainImg.addEventListener('click', () => {
                mainImg.style.transform = 'scale(1.1)';
                setTimeout(() => mainImg.style.transform = 'scale(1)', 500);
            });
        }
    </script>
</body>
</html>
