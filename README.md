# Observium-etc-hosts-Web-Editor
A custom Observium page (hosts_edit.php) that lets admin users edit /etc/hosts via the web UI. It uses sudo tee to safely overwrite the file as root, without giving the web server full root rights.
