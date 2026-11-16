<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
// Club details and follow actions live on the individual club page.
$pageTitle = 'Clubs';

$clubs = SampleData::getClubs();
