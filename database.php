<?php
$host = 'localhost';
$user = 'qnrebcbs_pos';
$pass = 'U0WTpbO*B7XV';
$dbname = 'qnrebcbs_pos';

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
