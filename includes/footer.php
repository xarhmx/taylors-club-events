<?php
/*
 * includes/footer.php
 * ------------------------------------------------------------
 * Shared bottom part of every page: closes <main>, footer,
 * and loads the JavaScript file.
 * ------------------------------------------------------------
 */
?>
</main>

<?php if (defined('USING_SAMPLE_DATA') && isset($currentUser)): ?>
    <!-- Demo bar: disappears automatically once sample_data.php is no longer used -->
    <div class="demo-bar">
        Demo data · viewing as <strong><?= $currentUser['role'] === 'organiser' ? 'Club Organiser' : 'Student' ?></strong> ·
        <?php if ($currentUser['role'] === 'organiser'): ?>
            <a href="?as=student">switch to Student</a>
        <?php else: ?>
            <a href="?as=organiser">switch to Organiser</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<footer class="site-footer">
    <div class="container footer-inner">
        <p>&copy; <?= date('Y') ?> Taylor's Club Events &middot; Discover, follow and join campus clubs.</p>
        <ul class="footer-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="clubs.php">Clubs</a></li>
            <li><a href="calendar.php">Calendar</a></li>
            <li><a href="profile.php">Profile</a></li>
        </ul>
    </div>
</footer>

<script src="js/script.js"></script>
</body>
</html>
