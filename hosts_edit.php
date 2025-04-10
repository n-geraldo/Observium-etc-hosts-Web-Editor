<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Observium bootstrap
include_once("../../includes/default.inc.php");

// Only admins
if ($_SESSION['userlevel'] < 10) {
    echo "<div style='color:red;'>Access denied.</div>";
    exit;
}

$message = "";
$hosts_file = '/etc/hosts';

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new = $_POST['hosts_content'] ?? '';
    if (trim($new) === '') {
        $message = "<div style='color:red;'>Submitted content is empty.</div>";
    } else {
        // Write to a temp file
        $tmp = tempnam(sys_get_temp_dir(), 'hosts_');
        file_put_contents($tmp, $new);

        // Use sudo + tee to overwrite /etc/hosts
        $cmd = 'sudo /usr/bin/tee ' . escapeshellarg($hosts_file) . ' < ' . escapeshellarg($tmp) . ' 2>&1';
        exec($cmd, $out, $rv);

        if ($rv === 0) {
            $message = "<div style='color:green;'>File saved successfully.</div>";
        } else {
            $message = "<div style='color:red;'>Failed to update /etc/hosts.<br>"
                     . "<strong>Command:</strong> {$cmd}<br>"
                     . "<strong>Return code:</strong> {$rv}<br>"
                     . "<strong>Output:</strong><pre>" . htmlspecialchars(implode("\n", $out)) . "</pre></div>";
        }

        unlink($tmp);
    }
}

// Read current
$cur = @file_get_contents($hosts_file);
if ($cur === false) {
    $cur = '';
    $message .= "<div style='color:red;'>Cannot read /etc/hosts.</div>";
}
?>

<h3>Edit /etc/hosts</h3>
<?php echo $message; ?>

<form method="post">
  <textarea name="hosts_content" rows="20" style="width:100%; font-family:monospace;"><?php echo htmlspecialchars($cur); ?></textarea><br><br>
  <input type="submit" value="Save Changes" class="btn btn-primary">
</form>
