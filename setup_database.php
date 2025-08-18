<?php
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Create database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS internlog";
    $pdo->exec($sql);
    echo "Database created successfully\n";

    // Switch to the new database
    $pdo->exec("USE internlog");

    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE,
        password VARCHAR(255),
        secret_key VARCHAR(255)
    )";
    $pdo->exec($sql);
    echo "Users table created successfully\n";

    // Create logbook_entries table
    $sql = "CREATE TABLE IF NOT EXISTS logbook_entries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        entry_date DATE,
        title VARCHAR(255),
        content TEXT,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Logbook entries table created successfully\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
