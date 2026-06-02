<?php
// test-write.php
$dir = __DIR__ . '/application/cache/sessions';

echo "<h3>Diagnostic session directory check</h3>";
echo "Target directory: <b>$dir</b><br><br>";

if (is_dir($dir)) {
    echo "✅ Directory exists.<br>";
    if (is_writable($dir)) {
        echo "✅ Directory is writable!<br>";
        
        // Try writing a dummy file
        $file = $dir . '/test_session_write.txt';
        if (@file_put_contents($file, 'session_test_data') !== false) {
            echo "✅ Successfully wrote a test file to the session folder.<br>";
            unlink($file);
        } else {
            echo "❌ Failed to write file to the session folder.<br>";
        }
    } else {
        echo "❌ Directory exists but is NOT writable by the web server user.<br>";
    }
} else {
    echo "❌ Directory does NOT exist.<br>";
    echo "Attempting to create it...<br>";
    if (@mkdir($dir, 0777, true)) {
        echo "✅ Successfully created the directory! Refresh to test write permissions.<br>";
    } else {
        echo "❌ Failed to create directory. The parent folder (application/cache) might not be writable.<br>";
    }
}
