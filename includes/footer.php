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
