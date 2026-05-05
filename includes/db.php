<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "pawhub_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getEmoji($species) {
    $emojis = [
        "Dog" => "🐶",
        "Cat" => "🐱",
        "Rabbit" => "🐰",
        "Bird" => "🐦",
        "Other" => "🐾"
    ];
    return $emojis[$species] ?? $emojis["Other"];
}
?>