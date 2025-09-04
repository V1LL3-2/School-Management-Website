-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2025 at 11:46 AM
-- Server version: 10.4.6-MariaDB
-- PHP Version: 7.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `course_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `emblem` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `start_day` date NOT NULL,
  `rest_of_day` date NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `facility_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`emblem`, `name`, `description`, `start_day`, `rest_of_day`, `teacher_id`, `facility_id`) VALUES
('BIO101', 'Biology Basics', 'Introduction to life sciences', '2025-01-17', '2025-05-25', 6, 'ROOM103'),
('CHEM101', 'General Chemistry', 'Introduction to chemical principles', '2025-01-22', '2025-06-10', 5, 'LAB001'),
('CS101', 'Introduction to Programming', 'Basic programming concepts using Python', '2025-01-20', '2025-06-05', 3, 'ROOM101'),
('ENG101', 'English Literature', 'Classic and modern literature analysis', '2025-01-15', '2025-05-30', 2, 'ROOM104'),
('HIST101', 'World History', 'Overview of world historical events', '2025-01-16', '2025-05-29', 7, 'ROOM104'),
('MATH101', 'Basic Mathematics', 'Introduction to algebra and geometry', '2025-01-15', '2025-05-30', 1, 'ROOM102'),
('MATH1010', 'Mathematics2', 'Math and meth', '2025-09-08', '2025-10-10', 1, 'HALL001'),
('MATH201', 'Advanced Mathematics', 'Calculus and advanced algebra', '2025-02-01', '2025-06-15', 1, 'ROOM102'),
('PHY101', 'Physics Fundamentals', 'Basic physics principles and experiments', '2025-01-18', '2025-05-28', 4, 'ROOM105');

-- --------------------------------------------------------

--
-- Table structure for table `course_logins`
--

CREATE TABLE `course_logins` (
  `emblem` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `course_id` varchar(20) DEFAULT NULL,
  `login_date_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `course_logins`
--

INSERT INTO `course_logins` (`emblem`, `student_id`, `course_id`, `login_date_time`) VALUES
(1, 1, 'MATH101', '2025-01-10 09:00:00'),
(2, 1, 'ENG101', '2025-01-10 09:15:00'),
(3, 2, 'MATH101', '2025-01-10 10:00:00'),
(4, 2, 'CS101', '2025-01-10 10:30:00'),
(5, 3, 'PHY101', '2025-01-11 08:30:00'),
(6, 3, 'MATH201', '2025-01-11 09:00:00'),
(7, 4, 'ENG101', '2025-01-11 14:00:00'),
(8, 4, 'HIST101', '2025-01-11 14:30:00'),
(9, 5, 'CHEM101', '2025-01-12 11:00:00'),
(10, 5, 'BIO101', '2025-01-12 11:30:00'),
(11, 6, 'CS101', '2025-01-12 16:00:00'),
(12, 6, 'PHY101', '2025-01-12 16:30:00'),
(13, 7, 'MATH101', '2025-01-13 08:00:00'),
(14, 8, 'ENG101', '2025-01-13 13:00:00'),
(15, 9, 'HIST101', '2025-01-14 10:00:00'),
(16, 10, 'BIO101', '2025-01-14 15:00:00'),
(17, 11, 'CS101', '2025-01-15 09:30:00'),
(18, 12, 'CHEM101', '2025-01-15 14:00:00'),
(19, 13, 'MATH101', '2025-01-16 08:30:00'),
(20, 14, 'PHY101', '2025-01-16 11:00:00'),
(21, 15, 'MATH201', '2025-01-17 09:00:00'),
(22, 1, 'HIST101', '2025-09-03 08:46:33'),
(23, 16, 'ENG101', '2025-09-03 08:57:04'),
(24, 16, 'MATH201', '2025-09-03 08:57:11'),
(25, 16, 'MATH1010', '2025-09-03 09:22:20'),
(26, 2, 'ENG101', '2025-09-03 12:23:22'),
(27, 17, 'MATH101', '2025-09-03 12:53:13'),
(28, 17, 'HIST101', '2025-09-03 12:53:19');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `emblem` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`emblem`, `name`, `capacity`) VALUES
('HALL001', 'Main Auditorium', 100),
('LAB001', 'Chemistry Lab', 22),
('ROOM101', 'Computer Lab A', 25),
('ROOM102', 'Mathematics Classroom', 30),
('ROOM103', 'Science Laboratory', 20),
('ROOM104', 'English Classroom', 35),
('ROOM105', 'Physics Lab', 2);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_number` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `birthday` date NOT NULL,
  `grade` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_number`, `first_name`, `surname`, `birthday`, `grade`) VALUES
(1, 'Alice', 'Johnson', '2007-03-15', 1),
(2, 'Bob', 'Williams', '2006-07-22', 2),
(3, 'Charlie', 'Brown', '2005-11-08', 3),
(4, 'Diana', 'Davis', '2007-01-30', 1),
(5, 'Edward', 'Miller', '2006-09-12', 2),
(6, 'Fiona', 'Wilson', '2005-05-18', 3),
(7, 'George', 'Moore', '2007-08-25', 1),
(8, 'Hannah', 'Taylor', '2006-12-03', 2),
(9, 'Ivan', 'Anderson', '2005-04-14', 3),
(10, 'Julia', 'Thomas', '2007-06-27', 1),
(11, 'Kevin', 'Jackson', '2006-10-09', 2),
(12, 'Laura', 'White', '2005-02-21', 3),
(13, 'Marcus', 'Harris', '2007-09-16', 1),
(14, 'Nina', 'Martin', '2006-03-08', 2),
(15, 'Oliver', 'Thompson', '2005-12-11', 3),
(16, 'Ville', 'Lång', '2007-09-07', 1),
(17, 'James', 'Test', '2000-02-04', 2),
(50, 'james', 'brayd', '2003-10-09', 1);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `identification_number` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `substance` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`identification_number`, `first_name`, `surname`, `substance`) VALUES
(1, 'John', 'Smith', 'Mathematics'),
(2, 'Sarah', 'Johnson', 'English Literature'),
(3, 'Michael', 'Brown', 'Computer Science'),
(4, 'Emma', 'Davis', 'Physics'),
(5, 'David', 'Wilson', 'Chemistry'),
(6, 'Lisa', 'Anderson', 'Biology'),
(7, 'Robert', 'Taylor', 'History');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student','staff') DEFAULT 'student',
  `student_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `student_id`, `teacher_id`, `first_name`, `last_name`, `created_at`, `last_login`, `is_active`) VALUES
(1, 'admin', 'admin@school.com', '$2y$10$07J/Ck1ISIkhF5WM3SQP2uuxzG5zoQ.3/uDJV1OZTh7HKEMtxs2Xi', 'admin', NULL, NULL, 'System', 'Administrator', '2025-09-03 05:16:32', '2025-09-04 07:28:25', 1),
(2, 'teacher1', 'teacher@school.com', '$2y$10$07J/Ck1ISIkhF5WM3SQP2uuxzG5zoQ.3/uDJV1OZTh7HKEMtxs2Xi', '', NULL, NULL, 'John', 'Teacher', '2025-09-03 05:16:32', '2025-09-03 05:33:11', 1),
(3, 'john.smith', 'john.smith@school.edu', '$2y$10$07J/Ck1ISIkhF5WM3SQP2uuxzG5zoQ.3/uDJV1OZTh7HKEMtxs2Xi', 'teacher', NULL, 1, 'John', 'Smith', '2025-09-03 05:18:37', '2025-09-03 05:34:11', 1),
(4, 'sarah.johnson', 'sarah.johnson@school.edu', '$2y$10$07J/Ck1ISIkhF5WM3SQP2uuxzG5zoQ.3/uDJV1OZTh7HKEMtxs2Xi', 'teacher', NULL, 2, 'Sarah', 'Johnson', '2025-09-03 05:18:37', NULL, 1),
(5, 'michael.brown', 'michael.brown@school.edu', '$2y$10$07J/Ck1ISIkhF5WM3SQP2uuxzG5zoQ.3/uDJV1OZTh7HKEMtxs2Xi', 'teacher', NULL, 3, 'Michael', 'Brown', '2025-09-03 05:18:37', NULL, 1),
(6, 'alice.johnson1', 'student1@student.school.edu', '$2y$10$07J/Ck1ISIkhF5WM3SQP2uuxzG5zoQ.3/uDJV1OZTh7HKEMtxs2Xi', 'student', 1, NULL, 'Alice', 'Johnson', '2025-09-03 05:18:37', '2025-09-03 05:47:17', 1),
(7, 'bob.williams2', 'student2@student.school.edu', '$2y$10$07J/Ck1ISIkhF5WM3SQP2uuxzG5zoQ.3/uDJV1OZTh7HKEMtxs2Xi', 'student', 2, NULL, 'Bob', 'Williams', '2025-09-03 05:18:37', NULL, 1),
(8, 'charlie.brown3', 'student3@student.school.edu', '$2y$10$07J/Ck1ISIkhF5WM3SQP2uuxzG5zoQ.3/uDJV1OZTh7HKEMtxs2Xi', 'student', 3, NULL, 'Charlie', 'Brown', '2025-09-03 05:18:37', NULL, 1),
(9, 'diana.davis4', 'student4@student.school.edu', '$2y$10$07J/Ck1ISIkhF5WM3SQP2uuxzG5zoQ.3/uDJV1OZTh7HKEMtxs2Xi', 'student', 4, NULL, 'Diana', 'Davis', '2025-09-03 05:18:37', NULL, 1),
(10, 'edward.miller5', 'student5@student.school.edu', '$2y$10$07J/Ck1ISIkhF5WM3SQP2uuxzG5zoQ.3/uDJV1OZTh7HKEMtxs2Xi', 'student', 5, NULL, 'Edward', 'Miller', '2025-09-03 05:18:37', NULL, 1),
(13, 'Ville', 'vl6802@edu.turku.fi', '$2y$10$MvOTVJXMINji2RyhkYHWHu6Ms.Vy4SdtZRcSoEVpXeHsvTMZmcN4m', 'student', 16, NULL, 'Ville', 'Lång', '2025-09-03 05:19:19', '2025-09-03 10:06:59', 1),
(14, 'Koulutuspaalikko', 'koulutuspaalikko@edu.turku.fi', '$2y$10$rCZvX56jo0MI4gWIPGr2b.TwgxUE6jWJgW94uDAFCfQJFusWrdpQW', 'admin', NULL, NULL, 'Koulutus', 'paalikko', '2025-09-03 05:42:30', '2025-09-04 07:00:32', 1),
(15, 'James', 'james.test@edu.turku.fi', '$2y$10$iJSDDl9Yu7Q5aNGlc2uxIu6BlFVu5c3HiCOn1OV/UZD2N49oA59wi', 'student', 17, NULL, 'James', 'Test', '2025-09-03 09:51:53', '2025-09-03 09:53:42', 1),
(50, 'jamesbrayd', 'jamesbrayd@gmail.com', '$2y$10$qxKE7MglRMGMKLXf5gvJxelNnurpo2TJKM9qUurQ7P8ynNLe93sji', 'student', 50, NULL, 'james', 'brayd', '2025-09-04 05:16:35', NULL, 1),
(999, 'dsadsa', 'dssa@gmail.com', '$2y$10$LTui.LOqcvmMwf5b.3D2COl0bBpru9AlaoPkEnCTn68Opu9dWQPr2', 'student', NULL, NULL, 'dsada', 'dsadsadsa', '2025-09-04 07:15:27', NULL, 1),
(2001, 'ArvanitisChristos1', 'ca6493@edu.turku.fi', '$2y$10$SOP/62ifnQSWbV375dRHse9LAjKV/XYcgQVW7mqgJkjRtKLJHYRES', 'student', NULL, NULL, 'Arvanitis1', 'Christos1', '2025-09-04 07:25:39', NULL, 1),
(2002, 'ArvanitisChristos12', 'ca6490@edu.turku.fi', '$2y$10$4i4cex95LwftskG42x8jKuIR9Vv/Uk01sSh1iylbAnn/Oj4hVySL2', 'student', NULL, NULL, 'Arvanitis12', 'Christos12', '2025-09-04 07:26:18', NULL, 1),
(2003, 'ArvanitisChristos123', 'ca6423@edu.turku.fi', '$2y$10$OUfEEQR8/ZOOvGGDDz3yeOd5uIRPI9vAUuqmEvSG0eogMuU05D1Zu', 'student', NULL, NULL, 'Arvanitis123', 'Christos123', '2025-09-04 07:27:00', NULL, 1),
(2004, 'markmark', 'mark@gmail.com', '$2y$10$4vDHeras0dQsAdoauGw/CegsOqXaRyO4Ww1PNPu5PNtsTRDXGbsHq', 'teacher', NULL, NULL, 'mark', 'mark', '2025-09-04 07:28:01', '2025-09-04 07:28:11', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`emblem`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `course_logins`
--
ALTER TABLE `course_logins`
  ADD PRIMARY KEY (`emblem`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`emblem`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`identification_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `course_logins`
--
ALTER TABLE `course_logins`
  MODIFY `emblem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2005;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
