<?php
session_start();
include 'db.php';

// Fetch featured listings (e.g., top 4 rated)
$stmt = $pdo->prepare("SELECT * FROM properties ORDER BY rating DESC LIMIT 4");
$stmt->execute();
$featured = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch top-rated (rating > 4.5)
$stmt = $pdo->prepare("SELECT * FROM properties WHERE rating > 4.5 ORDER BY rating DESC LIMIT 4");
$stmt->execute();
$topRated = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airbnb Clone - Home</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #FF385C, #E61E4D); color: #333; animation: fadeIn 1s ease-in; }
        header { background: #FFF; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .search-bar { display: flex; justify-content: center; margin: 20px 0; }
        .search-bar form { display: flex; gap: 10px; background: #FFF; padding: 10px; border-radius: 50px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); animation: slideIn 0.5s; }
        .search-bar input { padding: 10px; border: 1px solid #DDD; border-radius: 20px; transition: all 0.3s; }
        .search-bar input:focus { border-color: #FF385C; transform: scale(1.05); }
        .search-bar button { background: #FF385C; color: #FFF; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; transition: background 0.3s, transform 0.3s; }
        .search-bar button:hover { background: #E61E4D; transform: scale(1.1); }
        .section { padding: 40px; text-align: center; }
        .listings { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .property { background: #FFF; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s; animation: fadeInUp 1s; }
        .property:hover { transform: translateY(-10px); box-shadow: 0 8px 30px rgba(0,0,0,0.2); }
        .property img { width: 100%; height: 200px; object-fit: cover; }
        .property h3 { margin: 10px; color: #FF385C; }
        .filters { display: flex; justify-content: center; gap: 10px; margin: 20px 0; }
        .filters select, .filters input { padding: 10px; border-radius: 20px; border: 1px solid #DDD; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) { .search-bar form { flex-direction: column; } .listings { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <h1 style="text-align: center; color: #FF385C;">Airbnb Clone</h1>
    </header>
    <div class="search-bar">
        <form action="listings.php" method="GET">
            <input type="text" name="destination" placeholder="Destination" required>
            <input type="date" name="check_in" required>
            <input type="date" name="check_out" required>
            <button type="submit">Search</button>
        </form>
    </div>
    <div class="filters">
        <form action="listings.php" method="GET">
            <input type="number" name="min_price" placeholder="Min Price">
            <input type="number" name="max_price" placeholder="Max Price">
            <select name="type">
                <option value="">Property Type</option>
                <option value="apartment">Apartment</option>
                <option value="house">House</option>
                <option value="villa">Villa</option>
            </select>
            <input type="text" name="amenities" placeholder="Amenities (comma separated)">
            <button type="submit">Apply Filters</button>
        </form>
    </div>
    <div class="section">
        <h2>Featured Listings</h2>
        <div class="listings">
            <?php foreach ($featured as $prop): ?>
                <div class="property">
                    <img src="<?php echo $prop['image'] ?? 'https://via.placeholder.com/300x200'; ?>" alt="Property">
                    <h3><?php echo htmlspecialchars($prop['title']); ?></h3>
                    <p>$<?php echo $prop['price']; ?>/night</p>
                    <a href="property.php?id=<?php echo $prop['id']; ?>">View</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="section">
        <h2>Top-Rated Stays</h2>
        <div class="listings">
            <?php foreach ($topRated as $prop): ?>
                <div class="property">
                    <img src="<?php echo $prop['image'] ?? 'https://via.placeholder.com/300x200'; ?>" alt="Property">
                    <h3><?php echo htmlspecialchars($prop['title']); ?></h3>
                    <p>Rating: <?php echo $prop['rating']; ?></p>
                    <a href="property.php?id=<?php echo $prop['id']; ?>">View</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        // Example animation trigger
        document.querySelectorAll('.property').forEach(el => {
            el.addEventListener('mouseover', () => el.style.transform = 'scale(1.05)');
            el.addEventListener('mouseout', () => el.style.transform = 'scale(1)');
        });
    </script>
</body>
</html>
