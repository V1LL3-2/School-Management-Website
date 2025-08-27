-- Course Management System Database
-- Create database
CREATE DATABASE IF NOT EXISTS course_management;
USE course_management;

-- Create Students table
CREATE TABLE students (
    student_number INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL,
    birthday DATE NOT NULL,
    grade INT NOT NULL CHECK (grade BETWEEN 1 AND 3)
);

-- Create Teachers table
CREATE TABLE teachers (
    identification_number INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL,
    substance VARCHAR(100) NOT NULL
);

-- Create Facilities table
CREATE TABLE facilities (
    emblem VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    capacity INT NOT NULL
);

-- Create Courses table
CREATE TABLE courses (
    emblem VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    start_day DATE NOT NULL,
    rest_of_day DATE NOT NULL,
    teacher_id INT,
    facility_id VARCHAR(20),
    FOREIGN KEY (teacher_id) REFERENCES teachers(identification_number) ON DELETE SET NULL,
    FOREIGN KEY (facility_id) REFERENCES facilities(emblem) ON DELETE SET NULL
);

-- Create Course Logins (Enrollments) table
CREATE TABLE course_logins (
    emblem INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    course_id VARCHAR(20),
    login_date_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_number) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(emblem) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
);

-- Insert sample data for Teachers
INSERT INTO teachers (first_name, surname, substance) VALUES
('John', 'Smith', 'Mathematics'),
('Sarah', 'Johnson', 'English Literature'),
('Michael', 'Brown', 'Computer Science'),
('Emma', 'Davis', 'Physics'),
('David', 'Wilson', 'Chemistry'),
('Lisa', 'Anderson', 'Biology'),
('Robert', 'Taylor', 'History');

-- Insert sample data for Facilities
INSERT INTO facilities (emblem, name, capacity) VALUES
('ROOM101', 'Computer Lab A', 25),
('ROOM102', 'Mathematics Classroom', 30),
('ROOM103', 'Science Laboratory', 20),
('ROOM104', 'English Classroom', 35),
('ROOM105', 'Physics Lab', 18),
('HALL001', 'Main Auditorium', 100),
('LAB001', 'Chemistry Lab', 22);

-- Insert sample data for Courses
INSERT INTO courses (emblem, name, description, start_day, rest_of_day, teacher_id, facility_id) VALUES
('MATH101', 'Basic Mathematics', 'Introduction to algebra and geometry', '2025-01-15', '2025-05-30', 1, 'ROOM102'),
('ENG101', 'English Literature', 'Classic and modern literature analysis', '2025-01-15', '2025-05-30', 2, 'ROOM104'),
('CS101', 'Introduction to Programming', 'Basic programming concepts using Python', '2025-01-20', '2025-06-05', 3, 'ROOM101'),
('PHY101', 'Physics Fundamentals', 'Basic physics principles and experiments', '2025-01-18', '2025-05-28', 4, 'ROOM105'),
('CHEM101', 'General Chemistry', 'Introduction to chemical principles', '2025-01-22', '2025-06-10', 5, 'LAB001'),
('BIO101', 'Biology Basics', 'Introduction to life sciences', '2025-01-17', '2025-05-25', 6, 'ROOM103'),
('HIST101', 'World History', 'Overview of world historical events', '2025-01-16', '2025-05-29', 7, 'ROOM104'),
('MATH201', 'Advanced Mathematics', 'Calculus and advanced algebra', '2025-02-01', '2025-06-15', 1, 'ROOM102');

-- Insert sample data for Students
INSERT INTO students (first_name, surname, birthday, grade) VALUES
('Alice', 'Johnson', '2007-03-15', 1),
('Bob', 'Williams', '2006-07-22', 2),
('Charlie', 'Brown', '2005-11-08', 3),
('Diana', 'Davis', '2007-01-30', 1),
('Edward', 'Miller', '2006-09-12', 2),
('Fiona', 'Wilson', '2005-05-18', 3),
('George', 'Moore', '2007-08-25', 1),
('Hannah', 'Taylor', '2006-12-03', 2),
('Ivan', 'Anderson', '2005-04-14', 3),
('Julia', 'Thomas', '2007-06-27', 1),
('Kevin', 'Jackson', '2006-10-09', 2),
('Laura', 'White', '2005-02-21', 3),
('Marcus', 'Harris', '2007-09-16', 1),
('Nina', 'Martin', '2006-03-08', 2),
('Oliver', 'Thompson', '2005-12-11', 3);

-- Insert sample course enrollments
INSERT INTO course_logins (student_id, course_id, login_date_time) VALUES
(1, 'MATH101', '2025-01-10 09:00:00'),
(1, 'ENG101', '2025-01-10 09:15:00'),
(2, 'MATH101', '2025-01-10 10:00:00'),
(2, 'CS101', '2025-01-10 10:30:00'),
(3, 'PHY101', '2025-01-11 08:30:00'),
(3, 'MATH201', '2025-01-11 09:00:00'),
(4, 'ENG101', '2025-01-11 14:00:00'),
(4, 'HIST101', '2025-01-11 14:30:00'),
(5, 'CHEM101', '2025-01-12 11:00:00'),
(5, 'BIO101', '2025-01-12 11:30:00'),
(6, 'CS101', '2025-01-12 16:00:00'),
(6, 'PHY101', '2025-01-12 16:30:00'),
(7, 'MATH101', '2025-01-13 08:00:00'),
(8, 'ENG101', '2025-01-13 13:00:00'),
(9, 'HIST101', '2025-01-14 10:00:00'),
(10, 'BIO101', '2025-01-14 15:00:00'),
(11, 'CS101', '2025-01-15 09:30:00'),
(12, 'CHEM101', '2025-01-15 14:00:00'),
(13, 'MATH101', '2025-01-16 08:30:00'),
(14, 'PHY101', '2025-01-16 11:00:00'),
(15, 'MATH201', '2025-01-17 09:00:00');
