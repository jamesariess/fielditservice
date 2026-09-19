<?php
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/demo.php';

$hash = '$2y$10$OhAxJFuDuzKXbiFN169Jh.3W/6XR1eTOBlRnRjeGkdCtkUjtjle9W';
echo "Hash: $hash\n";

$try = [
    'admin','password','123456','admin123','FieldIT2024','FieldIT','demo',
    'Demo123!','P@ssw0rd','Passw0rd','FieldIT123','changeme','Field!t',
    'admin1','root','toor','letmein','welcome','Pass@123','Demo@123',
    'FieldIT@2024','Pass123!','P@ssword1','Fieldit123','Fieldit','fieldit123',
    'Demo1234','admin@123','Pass@word','FieldIT1','demo1234','Demo2024',
    'FieldIT01','Admin123!','Pass@1234','demo@fieldit','FieldIT@123',
    'Demo1234!','Pass@123!','FieldIT2025','FieldIT2026','demo123!',
    'Admin@123','Pass@word1','FieldIT_2024','demo@123','admin@fieldit',
];

foreach ($try as $pw) {
    if (password_verify($pw, $hash)) {
        echo "FOUND: $pw\n";
        exit(0);
    }
}
echo "None matched\n";
