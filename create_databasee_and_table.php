<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "practice";

// Connect to MySQL server without specifying a database
$conn = mysqli_connect($host, $username, $password);
if (!$conn) {
    die("MySQL connection error: " . mysqli_connect_error());
}

// Check if database exists and create if needed
$result = mysqli_query($conn, "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");

if (mysqli_num_rows($result) == 0) {
    // Database doesn't exist, create it
    if (!mysqli_query($conn, "CREATE DATABASE `$dbname`")) {
        die("Error creating database: " . mysqli_error($conn));
    }
    echo "Database created successfully.<br>";
}

// Now connect to the specific database
$dbconnect = mysqli_connect($host, $username, $password, $dbname);
if (!$dbconnect) {
    die("Database connection error: " . mysqli_connect_error());
}

echo "Successfully connected to database '$dbname'";

$tablename='student';
$table="CREATE TABLE $tablename(id int PRIMARY KEY, name varchar(50),password varchar(50),email varchar(100))";
if(mysqli_query($dbconnect,$table)){
    echo "'$tablename' created successfully";
}

?>