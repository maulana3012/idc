<?php

require_once "./auth.php";
require_auth();

// backup file location
$path = "/var/www/backup";

$files = scandir($path, SCANDIR_SORT_DESCENDING);
$latest_filename = $path . '/' . $files[0];

// Process download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($latest_filename) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($latest_filename));
flush(); // Flush system output buffer
readfile($latest_filename);
exit;
