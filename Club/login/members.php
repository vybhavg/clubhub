<?php
// Connect to the database
include('var/www/db_connect.php');
// Fetch branches
$branches_sql = "SELECT id, branch_name FROM branches";
$branches_result = $conn->query($branches_sql);

// Handle branch selection and fetch corresponding clubs
$selected_branch_id = isset($_POST['branch_id']) ? $_POST['branch_id'] : null;
$clubs_result = null;
if ($selected_branch_id) {
    $clubs_sql = "SELECT id, club_name FROM clubs WHERE branch_id = ?";
    $stmt = $conn->prepare($clubs_sql);
    $stmt->bind_param("i", $selected_branch_id);
    $stmt->execute();
    $clubs_result = $stmt->get_result();
    $stmt->close();
}
?>

<form method="post" action="members.php">
    <label for="branch_id">Select Branch:</label>
    <select name="branch_id" id="branch_id" onchange="this.form.submit()">
        <option value="">--Select Branch--</option>
        <?php while ($row = $branches_result->fetch_assoc()): ?>
            <option value="<?php echo $row['id']; ?>" <?php echo ($selected_branch_id == $row['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($row['branch_name']); ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

<?php if ($clubs_result): ?>
    <form method="post" action="members.php">
        <input type="hidden" name="branch_id" value="<?php echo $selected_branch_id; ?>">
        <label for="club_id">Select Club:</label>
        <select name="club_id" id="club_id" onchange="this.form.submit()">
            <option value="">--Select Club--</option>
            <?php while ($row = $clubs_result->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>" <?php echo ($selected_club_id == $row['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($row['club_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>
<?php
