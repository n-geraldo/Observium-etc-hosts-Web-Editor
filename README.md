Observium /etc/hosts Web Editor
📋 Overview
A custom Observium page (hosts_edit.php) that lets admin users edit /etc/hosts via the web UI. It uses sudo tee to safely overwrite the file as root, without giving the web server full root rights.

⚙️ Prerequisites
Observium install (PHP + Apache running as www-data)

Ability to edit /etc/sudoers.d/observium-hosts

/usr/bin/tee available (standard on Linux)

🔐 Sudoers Configuration
Create (or edit) /etc/sudoers.d/observium-hosts:

text
Copy
Edit
# Allow www-data to run tee on /etc/hosts as root, without password
www-data ALL=(root) NOPASSWD: /usr/bin/tee /etc/hosts
Then secure the file:

bash
Copy
Edit
sudo chown root:root /etc/sudoers.d/observium-hosts
sudo chmod 440   /etc/sudoers.d/observium-hosts
📝 hosts_edit.php
Place this in html/pages/hosts_edit.php (adjust path if needed):

php
Copy
Edit
<?php
// 1) Enable error reporting
ini_set('display_errors',1);
error_reporting(E_ALL);

// 2) Bootstrap Observium
include_once("../../includes/default.inc.php");

// 3) Only allow admins
if ($_SESSION['userlevel'] < 10) {
    echo "<div style='color:red;'>Access denied.</div>";
    exit;
}

$message   = '';
$hostsFile = '/etc/hosts';

// 4) Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new = $_POST['hosts_content'] ?? '';
    if (trim($new) === '') {
        $message = "<div style='color:red;'>Submitted content is empty.</div>";
    } else {
        // write to temp, then sudo+tee
        $tmp = tempnam(sys_get_temp_dir(), 'hosts_');
        file_put_contents($tmp, $new);

        $cmd = 'sudo /usr/bin/tee ' . escapeshellarg($hostsFile)
             . ' < ' . escapeshellarg($tmp) . ' 2>&1';
        exec($cmd, $out, $rv);

        if ($rv === 0) {
            $message = "<div style='color:green;'>File saved successfully.</div>";
        } else {
            $message = "<div style='color:red;'>Failed to update /etc/hosts.<br>"
                     . "<strong>Cmd:</strong> {$cmd}<br>"
                     . "<strong>RC:</strong> {$rv}<br>"
                     . "<pre>" . htmlspecialchars(implode("\n",$out)) . "</pre></div>";
        }

        unlink($tmp);
    }
}

// 5) Load current hosts
$cur = @file_get_contents($hostsFile);
if ($cur === false) {
    $cur = '';
    $message .= "<div style='color:red;'>Cannot read /etc/hosts.</div>";
}
?>

<h3>Edit /etc/hosts</h3>
<?php echo $message; ?>

<form method="post">
  <textarea name="hosts_content" rows="20" style="width:100%;font-family:monospace;">
    <?php echo htmlspecialchars($cur); ?>
  </textarea><br><br>
  <input type="submit" value="Save Changes" class="btn btn-primary">
</form>
🚀 Installation Steps
Add sudoers rule (see above).

Deploy hosts_edit.php to your Observium html/pages/ folder.

Secure it behind Observium auth (the script checks $_SESSION['userlevel']).

Test from the UI—make a small edit and hit Save.

✅ Verification
On the command line, as root:

bash
Copy
Edit
echo "127.0.0.1  test-entry" > /tmp/test
sudo -u www-data sudo /usr/bin/tee /etc/hosts < /tmp/test
cat /etc/hosts   # should show “test-entry”
If that works, the web UI will, too.
