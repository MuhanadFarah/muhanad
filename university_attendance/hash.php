<?php
// The plain password
$password = "Hanandez@123";

// Generate a secure hash using PASSWORD_DEFAULT (bcrypt)
$hash = password_hash($password, PASSWORD_DEFAULT);

// Output the hash
echo "Password: $password\n";
echo "Hashed: $hash\n";
?>
