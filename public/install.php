<?php

if (file_exists(('./install.lock'))) {
    echo 'Install script already executed';
    exit;
}

$host = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'dstrct';

$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the database exists
$result = $conn->query("SHOW DATABASES LIKE '$dbname'");

if ($result->num_rows == 0) {
    // Database doesn't exist, so create it
    $sql = "CREATE DATABASE $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "Database '$dbname' created successfully\n";
    } else {
        echo "Error creating database: " . $conn->error;
        exit;
    }
} else {
    echo "Database '$dbname' already exists.\n";
    exit();
}

$conn->select_db($dbname);

$sql = "CREATE TABLE IF NOT EXISTS `users` (
        `id` int NOT NULL AUTO_INCREMENT,
        `full_name` varchar(100) NOT NULL,
        `phone_number` varchar(15) DEFAULT NULL,
        `email` varchar(100) DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    )";

if ($conn->query($sql) === TRUE) {
    echo "Table 'users' created successfully\n";
} else {
    echo "Error creating table: " . $conn->error;
}

$sql = "INSERT INTO users (full_name, phone_number, email) VALUES 
            ('Sample user 1', '1234567890', 'sampleuser1@example.com'),
            ('Sample user 2', '1234567890', 'sampleuser2@example.com'),
            ('Sample user 3', '1234567890', 'sampleuser3@example.com'),
            ('Sample user 4', '1234567890', 'sampleuser4@example.com'),
            ('Sample user 5', '1234567890', 'sampleuser5@example.com')";

if ($conn->query($sql) === TRUE) {
    echo "Default data inserted successfully\n";
} else {
    echo "Error inserting data: " . $conn->error;
}

$sql2 = "CREATE TABLE IF NOT EXISTS `users_image` (
        `id` int NOT NULL AUTO_INCREMENT,
        `user_id` int NOT NULL,
        `image_name` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
    )";

if ($conn->query($sql2) === TRUE) {
    echo "Table 'users_image' created successfully\n";
} else {
    echo "Error creating table: " . $conn->error;
}

$sql3 = "CREATE TABLE IF NOT EXISTS `user_scores` (
        `id` int NOT NULL AUTO_INCREMENT,
        `user_id` int NOT NULL,
        `score` int NOT NULL,
        `score_date` date NOT NULL,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    )";

if ($conn->query($sql3) === TRUE) {
    echo "Table 'user_scores' created successfully\n";
} else {
    echo "Error creating table: " . $conn->error;
}

$sql3 = "INSERT INTO user_scores (user_id, score, score_date) 
                VALUES ('1', '1', '2024-12-1'),
                ('1', '5', '2024-12-2'),
                ('2', '6', '2024-12-1'),
                ('2', '2', '2024-12-2'),
                ('2', '4', '2024-12-3'),
                ('3', '3', '2024-12-3'),
                ('3', '4', '2024-12-4'),
                ('4', '2', '2024-12-5'),
                ('4', '6', '2024-12-6'),
                ('5', '5', '2024-12-4')";

if ($conn->query($sql3) === TRUE) {
    echo "Default data inserted successfully\n";
} else {
    echo "Error inserting data: " . $conn->error;
}

file_put_contents('install.lock', 'installed');
echo "Installation complete!";

$conn->close();