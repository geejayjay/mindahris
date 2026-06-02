<?php
// test-session.php
ini_set('session.save_handler', 'files');
session_save_path('/tmp');

session_start();

if (!isset($_SESSION['count'])) {
    $_SESSION['count'] = 1;
    echo "Session initiated in /tmp. Count: 1. <br>Please refresh the page to test persistence.";
} else {
    $_SESSION['count']++;
    echo "Session works! Count: " . $_SESSION['count'];
}
