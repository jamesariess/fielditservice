<?php
$root = dirname(__DIR__).'/database/migrations/';
$sql = file_get_contents($root.'20261003_test_users.sql');
$credentials = file_get_contents($root.'TEST-ACCOUNT-LOGINS.txt');
preg_match_all('/Temporary password: ([a-f0-9]+)/', $credentials, $passwords);
preg_match_all('/\$2y\$10\$[.\/A-Za-z0-9]{53}/', $sql, $hashes);
if (count($passwords[1]) !== 4 || count($hashes[0]) !== 4) throw new RuntimeException('Expected four accounts');
foreach ($passwords[1] as $i => $password) {
    if (!password_verify($password, $hashes[0][$i])) throw new RuntimeException('Credential mismatch');
}
if (count(array_unique($passwords[1])) !== 4) throw new RuntimeException('Passwords must be unique');
echo "PASS: Four unique credentials match their bcrypt hashes.\n";
