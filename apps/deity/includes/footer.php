
</body>
</html>

<?php

// Log the user's access
SiteTracker::log($_SERVER['REMOTE_ADDR'], $url);