-- Create database
CREATE DATABASE IF NOT EXISTS skst_university;

-- Use the database
USE skst_university;

-- Create the main table
CREATE TABLE IF NOT EXISTS Scholarship_application (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    department ENUM('BBA', 'BSCE', 'BSAg', 'BSME', 'BATHM', 'BSN', 'BCSE', 'BSEEE', 'BA Econ', 'BA Eng') NOT NULL,
    semester INT NOT NULL,
    mobile_number VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    current_semester_sgpa DECIMAL(3,2) NOT NULL,
    cgpa DECIMAL(3,2) NOT NULL,
    previous_semester_cgpa DECIMAL(3,2) NOT NULL,
    scholarship_percentage DECIMAL(5,2) NOT NULL,
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Additional columns that might need to be added if not present
ALTER TABLE Scholarship_application 
ADD COLUMN IF NOT EXISTS previous_semester_cgpa DECIMAL(3,2) NOT NULL AFTER cgpa;

ALTER TABLE Scholarship_application 
ADD COLUMN IF NOT EXISTS scholarship_percentage DECIMAL(5,2) NOT NULL AFTER previous_semester_cgpa;