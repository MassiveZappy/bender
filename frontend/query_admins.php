<?php
$ch = curl_init("http://localhost:5000/api/admins");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$admins = json_decode(curl_exec($ch), true);
curl_close($ch);

echo "<h3>Admins:</h3><ul>";
foreach ($admins as $admin) {
    echo "<li>" . htmlspecialchars($admin['username']) . " (ID: " . $admin['id'] . ")</li>";
}
echo "</ul>";
?>
