<?php
// Test script to check password hashing

$password = 'password';
$hash = '$2y$10$BzG2Ne.4z4D9.5bP9.7.8Ou7kR5dG1hE3jF6gI9kL0mN2oP3qR4uS';

echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n";
echo "Verify result: " . (password_verify($password, $hash) ? "Valid" : "Invalid") . "\n";

// Generate a new hash for testing
$newHash = password_hash($password, PASSWORD_DEFAULT);
echo "New hash: " . $newHash . "\n";
echo "Verify new hash: " . (password_verify($password, $newHash) ? "Valid" : "Invalid") . "\n";