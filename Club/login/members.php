<?php
// Connect to the database
include('db_connect.php');

// Fetch branches
$branches_sql = "SELECT id, name FROM branches";
$branches_result = $conn->query($branches_sql);

// Fetch clubs by selected branch (default is the first branch)
$selected_branch_id = isset($_GET['branch_id']) ? $_GET['branch_id'] : 1;

$clubs_sql = "SELECT * FROM clubs WHERE branch_id = ?";
$clubs_stmt = $conn->prepare($clubs_sql);
$clubs_stmt->bind_param("i", $selected_branch_id);
$clubs_stmt->execute();
$clubs_result = $clubs_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clubs</title>
</head>
<body>
    <h1>Select Branch</h1>
    <form method="get" action="index.php">
        <select name="branch_id" onchange="this.form.submit()">
            <?php while ($branch = $branches_result->fetch_assoc()): ?>
                <option value="<?php echo $branch['id']; ?>" <?php if ($branch['id'] == $selected_branch_id) echo 'selected'; ?>>
                    <?php echo $branch['name']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <h2>Clubs in <?php echo $branches_result->fetch_assoc()['name']; ?></h2>
    <ul>
        <?php while ($club = $clubs_result->fetch_assoc()): ?>
            <li><?php echo $club['name']; ?></li>
        <?php endwhile; ?>
    </ul>

</body>
</html>

<?php
$conn->close();
?>
