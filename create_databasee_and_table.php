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
// 
//second step and easy
$dbname="practice2";
$dbcreate= "CREATE DATABASE IF NOT EXISTS $dbname";
if(mysqli_query($conn,$dbcreate)){
    echo "'$dbname' create successfully";
    $dbconnect=mysqli_connect($host, $username, $password,$dbname);
    if (!$dbconnect){
        die("connect error".mysqli_connect_error());
    }
}
else {
    echo "'$dbname' is already exist";
}

echo "Successfully connected to database '$dbname'";

$tablename='student';
$table="CREATE TABLE IF NOT EXISTS $tablename(id int PRIMARY KEY, name varchar(50),password varchar(50),email varchar(100))";
if(mysqli_query($dbconnect,$table)){
    echo "'$tablename' created successfully";
}
echo "'$tablename' alreaty exists";

?>