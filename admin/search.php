<?php
require_once __DIR__ . '/includes/db.php'; // ✅ DB connection

$query = '';
if (isset($_GET['query'])) {
    $query = mysqli_real_escape_string($conn, $_GET['query']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h3>Search Results for: <span class="text-primary"><?php echo htmlspecialchars($query); ?></span></h3>
    <hr>

    <?php if ($query): ?>
        <!-- 🔹 Residents -->
        <h5 class="mt-4">Residents</h5>
        <table class="table table-bordered table-striped">
            <thead>
                <tr><th>Name</th><th>Address</th><th>Contact</th></tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT fullname, address, contact_number 
                        FROM residents 
                        WHERE fullname LIKE '%$query%' OR address LIKE '%$query%' OR contact_number LIKE '%$query%' 
                        LIMIT 10";
                $res = mysqli_query($conn, $sql);
                if ($res && mysqli_num_rows($res) > 0):
                    while ($row = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr><td colspan="3" class="text-center text-muted">No residents found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 🔹 Announcements -->
        <h5 class="mt-4">Announcements</h5>
        <ul class="list-group">
            <?php
            $sql = "SELECT title, content, date_posted 
                    FROM announcements 
                    WHERE title LIKE '%$query%' OR content LIKE '%$query%' 
                    ORDER BY date_posted DESC 
                    LIMIT 5";
            $res = mysqli_query($conn, $sql);
            if ($res && mysqli_num_rows($res) > 0):
                while ($row = mysqli_fetch_assoc($res)): ?>
                    <li class="list-group-item">
                        <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                        <small><?php echo substr(htmlspecialchars($row['content']), 0, 100) . '...'; ?></small>
                        <div class="text-muted"><em><?php echo $row['date_posted']; ?></em></div>
                    </li>
                <?php endwhile;
            else: ?>
                <li class="list-group-item text-muted">No announcements found</li>
            <?php endif; ?>
        </ul>

        <!-- 🔹 Document Requests -->
        <h5 class="mt-4">Document Requests</h5>
        <table class="table table-bordered table-striped">
            <thead>
                <tr><th>Request ID</th><th>Resident</th><th>Type</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT request_id, resident_name, document_type, status 
                        FROM document_requests 
                        WHERE request_id LIKE '%$query%' OR resident_name LIKE '%$query%' OR document_type LIKE '%$query%' 
                        LIMIT 10";
                $res = mysqli_query($conn, $sql);
                if ($res && mysqli_num_rows($res) > 0):
                    while ($row = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['request_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['resident_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['document_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr><td colspan="4" class="text-center text-muted">No document requests found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php else: ?>
        <p class="text-muted">Please enter a search term.</p>
    <?php endif; ?>
</body>
</html>
