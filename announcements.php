<?php
require_once 'includes/db.php'; // your database connection file

// Fetch latest announcements with poster info
$sql = "SELECT a.id, a.title, a.content, a.date_posted, a.image_path, 
               CONCAT(u.first_name, ' ', u.last_name) AS posted_by
        FROM announcements a
        JOIN admin_users u ON a.posted_by = u.id
        ORDER BY a.date_posted DESC";
$result = $conn->query($sql);
?>

<!-- Announcements Section -->
<section id="announcements" class="d-none">
    <h2 class="mb-4">Announcements</h2>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Latest Announcements</span>
            <a href="announcements_archive.php" class="btn btn-sm btn-warning">View Archive</a>
        </div>
        <div class="card-body">
            <div class="list-group">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <a href="announcement_view.php?id=<?= $row['id'] ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?= htmlspecialchars($row['title']) ?></h5>
                                <small class="text-muted"><?= date("F j, Y", strtotime($row['date_posted'])) ?></small>
                            </div>
                            <p class="mb-1"><?= nl2br(htmlspecialchars(substr($row['content'], 0, 150))) ?>...</p>
                            <small>Posted by: <?= htmlspecialchars($row['posted_by']) ?></small>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No announcements available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
