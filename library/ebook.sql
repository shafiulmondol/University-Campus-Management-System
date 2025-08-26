CREATE DATABASE IF NOT EXISTS skst_university;

USE skst_university;

CREATE TABLE IF NOT EXISTS ebook (
    id INT PRIMARY KEY,
    book_name VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    author VARCHAR(255),
    publish_year INT,
    link VARCHAR(500)
);