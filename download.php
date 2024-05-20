<?php
// download.php

// Get the file path from the query string
$filepath = $_GET['file'];

// Check if the file exists
if (file_exists($filepath)) {
    // Send the file for download
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
    header('Content-Length: ' . filesize($filepath));

    flush();
    readfile($filepath);

    // Delete the file
    unlink($filepath);
}