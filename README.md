# Course Management System

A comprehensive web-based course management system built with PHP and MySQL. This system allows institutions to manage students, teachers, courses, facilities, and course enrollments.

## Features

### Core Functionality
- **Student Management**: Add, edit, delete, and view student records with grades 1-3
- **Teacher Management**: Manage teacher information and their subject specializations
- **Course Management**: Create and manage courses with start/end dates, descriptions
- **Facility Management**: Track classrooms, labs, and their capacity limits
- **Course Enrollments**: Student registration system with enrollment tracking
- **Capacity Warnings**: Automatic alerts when courses exceed facility capacity

### User Interface
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Interactive Dashboard**: Overview statistics and recent activity
- **Detail Views**: Comprehensive views showing relationships between entities
- **Modern UI**: Clean, professional interface with Font Awesome icons

## System Requirements

- **Web Server**: Apache (XAMPP recommended)
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **Browser**: Modern web browser (Chrome, Firefox, Safari, Edge)

## Installation Instructions

### Step 1: Download and Setup Files

1. Download all the project files
2. Create the following folder structure on your web server:

```
course_management/
├── index.php
├── config/
│   └── database.php
├── css/
│   └── style.css
├── students/
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   ├── view.php
│   └── enroll.php
├── teachers/
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   └── view.php
├── courses/
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   └── view.php
├── facilities/
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   └── view.php
└── README.md
```

### Step 2: Database Setup

1. **Start your XAMPP server** (Apache and MySQL)

2. **Open phpMyAdmin** (usually at http://localhost/phpmyadmin)

3. **Create the database**: