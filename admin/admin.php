<?php
// Create a dedicated connection function
function getDBConnection() {
    $connection = mysqli_connect('localhost','root','','skst_university');
    if(!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $connection;
}

// Student count function with proper connection handling
function student_count() {
    $connection = getDBConnection();
    $q1 = 'SELECT COUNT(*) as total FROM student_registration';
    $result = mysqli_query($connection, $q1);
    
    if($result) {
        $row = mysqli_fetch_assoc($result);
        $student_count = $row['total'];
        mysqli_close($connection);
        return $student_count;
    } else {
        $error = mysqli_error($connection);
        mysqli_close($connection);
        return "Error: " . $error;
    }
}
function faculty_count() {
    $connection = getDBConnection();
    $q1 = 'SELECT COUNT(*) as total FROM faculty';
    $result = mysqli_query($connection, $q1);
    
    if($result) {
        $row = mysqli_fetch_assoc($result);
        $student_count = $row['total'];
        mysqli_close($connection);
        return $student_count;
    } else {
        $error = mysqli_error($connection);
        mysqli_close($connection);
        return "Error: " . $error;
    }
}
function course_count() {
    $connection = getDBConnection();
    $q1 = 'SELECT COUNT(*) as total FROM faculty';
    $result = mysqli_query($connection, $q1);
    
    if($result) {
        $row = mysqli_fetch_assoc($result);
        $student_count = $row['total'];
        mysqli_close($connection);
        return $student_count;
    } else {
        $error = mysqli_error($connection);
        mysqli_close($connection);
        return "Error: " . $error;
    }
}

?>