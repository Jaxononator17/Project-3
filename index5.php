<?php

session_start();
require_once 'auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$host = 'localhost'; 
$dbname = 'paint_codes'; 
$user = 'jax'; 
$pass = 'jax';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// Handle paint search
$search_results = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $search_sql = 'SELECT `Color Name`, `Color Number`, `Hex`, `RGB`, `ID` FROM valspar WHERE `Color Number` LIKE :search';
    $search_stmt = $pdo->prepare($search_sql);
    $search_stmt->execute(['search' => $search_term]);
    $search_results = $search_stmt->fetchAll();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_id'])) {
        // Save an entry
        $save_id = (int) $_POST['save_id'];
        
        $save_sql = 'INSERT INTO saved_paint_codes (ID, `Color Name`, `Color Number`, `Hex`, `RGB`)
                     SELECT ID, `Color Name`, `Color Number`, `Hex`, `RGB` FROM valspar WHERE ID = :id';
        $stmt_save = $pdo->prepare($save_sql);
        $stmt_save->execute(['id' => $save_id]);
    }
}

// Get all paint codes for main table
$sql = 'SELECT `ID`, `Color Name`, `Color Number`, `Hex`, `RGB` FROM valspar';
$stmt = $pdo->query($sql);

$saved_sql = 'SELECT `ID`, `Color Name`, `Color Number`, `Hex`, `RGB` FROM saved_paint_codes';
$saved_stmt = $pdo->query($saved_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Paint Code Lookup</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="hero-title">Paint Code Lookup</h1>
        <p class="hero-subtitle">"Look up a paint that fits your needs"</p>
        
        <!-- Search moved to hero section -->
        <div class="hero-search">
            <h2>Search for a paint to add to your list</h2>
            <form action="" method="GET" class="search-form">
                <label for="search">Search by Paint Code:</label>
                <input type="text" id="search" name="search" required>
                <input type="submit" value="Search">
            </form>
            
            <?php if (isset($_GET['search'])): ?>
                <div class="search-results">
                    <h3>Search Results</h3>
                    <?php if ($search_results && count($search_results) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Color Name</th>
                                    <th>Color Number</th>
                                    <th>Hex</th>
                                    <th>RGB</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['Color Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Color Number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Hex']); ?></td>
                                    <td><?php echo htmlspecialchars($row['RGB']); ?></td>
                                    <td>
                                        <form action="index5.php" method="post" style="display:inline;">
                                            <input type="hidden" name="save_id" value="<?php echo $row['ID']; ?>">
                                            <input type="submit" value="Save">
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No paint codes found matching your search.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table section with container -->
    <div class="table-container">
        <h2>All Paint Codes in Database</h2>
        <table class="half-width-left-align">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Color Name</th>
                    <th>Color Number</th>
                    <th>Hex</th>
                    <th>RGB</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ID']); ?></td>
                    <td><?php echo htmlspecialchars($row['Color Name']); ?></td>
                    <td><?php echo htmlspecialchars($row['Color Number']); ?></td>
                    <td><?php echo htmlspecialchars($row['Hex']); ?></td>
                    <td><?php echo htmlspecialchars($row['RGB']); ?></td>
                    <td>
                        <form action="index5.php" method="post" style="display:inline;">
                            <input type="hidden" name="save_id" value="<?php echo $row['ID']; ?>">
                            <input type="submit" value="Save">
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Saved Paint Codes section with container -->
    <div class="table-container">
        <h2>Saved Paint Codes</h2>
        <table class="half-width-left-align">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Color Name</th>
                    <th>Color Number</th>
                    <th>Hex</th>
                    <th>RGB</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $saved_stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ID']); ?></td>
                    <td><?php echo htmlspecialchars($row['Color Name']); ?></td>
                    <td><?php echo htmlspecialchars($row['Color Number']); ?></td>
                    <td><?php echo htmlspecialchars($row['Hex']); ?></td>
                    <td><?php echo htmlspecialchars($row['RGB']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

