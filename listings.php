<?php
session_start();
include 'db.php';

$error_message = '';
$properties = [];
$where = '1=1';
$params = [];

try {
    // Build dynamic WHERE clause based on filters
    if (!empty($_GET['destination'])) {
        $where .= ' AND location LIKE :destination';
        $params[':destination'] = '%' . $_GET['destination'] . '%';
    }
    if (!empty($_GET['min_price'])) {
        $where .= ' AND price >= :min_price';
        $params[':min_price'] = $_GET['min_price'];
    }
    if (!empty($_GET['max_price'])) {
        $where .= ' AND price <= :max_price';
        $params[':max_price'] = $_GET['max_price'];
    }
    if (!empty($_GET['type'])) {
        $where .= ' AND type = :type';
        $params[':type'] = $_GET['type'];
    }
    if (!empty($_GET['amenities'])) {
        $amenities = array_filter(array_map('trim', explode(',', $_GET['amenities'])));
        foreach ($amenities as $i => $amenity) {
            $where .= " AND amenities LIKE :amenity$i";
            $params[":amenity$i"] = "%$amenity%";
        }
    }

    // Handle sorting
    $sort = isset($_GET['sort']) && in_array($_GET['sort'], ['price ASC', 'rating DESC']) ? $_GET['sort'] : 'price ASC';

    // Execute query
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE $where ORDER BY $sort");
    $stmt->execute($params);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($properties)) {
        $error_message = 'No properties found for the selected filters. Try adjusting your search criteria.';
    }
} catch (PDOException $e) {
    $error_message = 'Database error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Listings - Airbnb Clone</title>
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
        .sort {
            text-align: center;
            margin: 20px;
        }
        .sort select {
            padding: 10px;
            border-radius: 20px;
            background: #F97316;
            color: #FFF;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s, transform 0.3s;
        }
        .sort select:hover {
            background: #EA580C;
            transform: scale(1.05);
        }
        .listings {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 40px;
        }
        .property {
            background: #FFF;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            animation: fadeInUp 0.5s ease-out;
        }
        .property:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        }
        .property img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .property h3 {
            margin: 10px;
            color: #F97316;
        }
        .property a {
            display: inline-block;
            margin: 10px;
            color: #1E3A8A;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .property a:hover {
            color: #F97316;
        }
        .error {
            color: #DC2626;
            text-align: center;
            font-size: 18px;
            margin: 20px;
            animation: fadeIn 0.5s;
        }
        .back-btn {
            display: inline-block;
            margin: 20px auto;
            text-align: center;
            background: #F97316;
            color: #FFF;
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            transition: background 0.3s, transform 0.3s;
        }
        .back-btn:hover {
            background: #EA580C;
            transform: scale(1.1);
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 768px) {
            .listings { grid-template-columns: 1fr; }
            .sort select { width: 100%; }
        }
    </style>
</head>
<body>
    <header>
        <h1>Property Listings</h1>
    </header>
    <div class="sort">
        <form method="GET">
            <select name="sort" onchange="this.form.submit()">
                <option value="price ASC" <?php if ($sort == 'price ASC') echo 'selected'; ?>>Price Low to High</option>
                <option value="rating DESC" <?php if ($sort == 'rating DESC') echo 'selected'; ?>>Best Rated</option>
            </select>
            <?php foreach ($_GET as $key => $val) if ($key != 'sort') echo "<input type='hidden' name='$key' value='$val'>"; ?>
        </form>
    </div>
    <div class="listings">
        <?php if ($error_message): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
            <p style="text-align: center;"><a href="index.php" class="back-btn">Back to Home</a></p>
        <?php else: ?>
            <?php foreach ($properties as $prop): ?>
                <div class="property">
                    <img src="<?php echo $prop['image'] ?? 'https://via.placeholder.com/300x200'; ?>" alt="Property">
                    <h3><?php echo htmlspecialchars($prop['title']); ?></h3>
                    <p>$<?php echo number_format($prop['price'], 2); ?>/night - Rating: <?php echo number_format($prop['rating'], 1); ?></p>
                    <a href="property.php?id=<?php echo $prop['id']; ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <script>
        // Hover animation for properties
        document.querySelectorAll('.property').forEach(el => {
            el.addEventListener('mouseover', () => {
                el.style.transform = 'scale(1.05)';
                el.style.boxShadow = '0 0 20px #F97316';
            });
            el.addEventListener('mouseout', () => {
                el.style.transform = 'scale(1)';
                el.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
            });
        });

        // Pulse animation for back button
        const backBtn = document.querySelector('.back-btn');
        if (backBtn) {
            setInterval(() => {
                backBtn.style.transform = 'scale(1.05)';
                setTimeout(() => backBtn.style.transform = 'scale(1)', 300);
            }, 2000);
        }
    </script>
</body>
</html>
