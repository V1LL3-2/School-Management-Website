<?php
// Translation System for Course Management System
// File: config/translations.php

session_start();

// Set default language if not set
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en'; // Default to English
}

// Handle language switch
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fi'])) {
    $_SESSION['language'] = $_GET['lang'];
    
    // Redirect to remove lang parameter from URL
    $url = strtok($_SERVER["REQUEST_URI"], '?');
    $params = $_GET;
    unset($params['lang']);
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    header("Location: $url");
    exit;
}

// Translation arrays
$translations = [
    'en' => [
        // Navigation
        'nav_dashboard' => 'Dashboard',
        'nav_students' => 'Students',
        'nav_teachers' => 'Teachers',
        'nav_courses' => 'Courses',
        'nav_facilities' => 'Facilities',
        'nav_title' => 'Course Management System',
        
        // Common buttons
        'btn_add' => 'Add',
        'btn_edit' => 'Edit',
        'btn_delete' => 'Delete',
        'btn_view' => 'View',
        'btn_save' => 'Save',
        'btn_cancel' => 'Cancel',
        'btn_back' => 'Back',
        'btn_submit' => 'Submit',
        'btn_enroll' => 'Enroll',
        'btn_search' => 'Search',
        'btn_filter' => 'Filter',
        'btn_export' => 'Export',
        'btn_import' => 'Import',
        'btn_back_to_list' => 'Back to List',
        'btn_back_to_details' => 'Back to Details',
        
        // Dashboard
        'dashboard_title' => 'Dashboard',
        'dashboard_subtitle' => 'Course Management System Overview',
        'dashboard_total_students' => 'Total Students',
        'dashboard_total_teachers' => 'Total Teachers',
        'dashboard_total_courses' => 'Total Courses',
        'dashboard_total_facilities' => 'Total Facilities',
        'dashboard_quick_actions' => 'Quick Actions',
        'dashboard_recent_activity' => 'Recent Activity',
        
        // Students
        'students_title' => 'Students Management',
        'students_subtitle' => 'Manage all student records',
        'students_add_title' => 'Add New Student',
        'students_add_subtitle' => 'Enter student information',
        'students_edit_title' => 'Edit Student',
        'students_edit_subtitle' => 'Update student information',
        'students_view_title' => 'Student Details',
        'students_enroll_title' => 'Enroll Student in Course',
        'students_no_students' => 'No students found.',
        'students_add_first' => 'Add the first student',
        'students_not_enrolled' => 'This student is not enrolled in any courses yet.',
        'students_enroll_first' => 'Enroll in First Course',
        'students_all_enrolled' => 'All courses enrolled!',
        'students_all_courses_enrolled' => 'This student is enrolled in all available courses.',
        
        // Student fields
        'student_number' => 'Student Number',
        'student_first_name' => 'First Name',
        'student_surname' => 'Surname',
        'student_birthday' => 'Birthday',
        'student_grade' => 'Grade',
        'student_age' => 'Age',
        'student_name' => 'Name',
        'student_years_old' => 'years old',
        
        // Teachers
        'teachers_title' => 'Teachers Management',
        'teachers_subtitle' => 'Manage all teacher records',
        'teachers_add_title' => 'Add New Teacher',
        'teachers_add_subtitle' => 'Enter teacher information',
        'teachers_edit_title' => 'Edit Teacher',
        'teachers_edit_subtitle' => 'Update teacher information',
        'teachers_view_title' => 'Teacher Details',
        'teachers_no_teachers' => 'No teachers found.',
        'teachers_add_first' => 'Add the first teacher',
        'teachers_not_assigned' => 'This teacher is not currently assigned to any courses.',
        
        // Teacher fields
        'teacher_id' => 'Teacher ID',
        'teacher_first_name' => 'First Name',
        'teacher_surname' => 'Surname',
        'teacher_subject' => 'Subject/Specialty',
        'teacher_substance' => 'Subject/Substance',
        'teacher_name' => 'Teacher',
        'teacher_id_readonly' => 'Teacher ID cannot be changed after creation',
        'teacher_subject_help' => 'The subject area this teacher specializes in',
        
        // Courses
        'courses_title' => 'Courses Management',
        'courses_subtitle' => 'Manage all course records',
        'courses_add_title' => 'Add New Course',
        'courses_add_subtitle' => 'Enter course information',
        'courses_edit_title' => 'Edit Course',
        'courses_edit_subtitle' => 'Update course information',
        'courses_view_title' => 'Course Details',
        'courses_no_courses' => 'No courses found.',
        'courses_add_first' => 'Add the first course',
        'courses_no_students_enrolled' => 'No students enrolled in this course yet.',
        
        // Course fields
        'course_code' => 'Course Code',
        'course_name' => 'Course Name',
        'course_description' => 'Description',
        'course_start_date' => 'Start Date',
        'course_end_date' => 'End Date',
        'course_teacher' => 'Teacher',
        'course_facility' => 'Facility',
        'course_enrollment' => 'Enrollment',
        'course_duration' => 'Duration',
        'course_days' => 'days',
        'course_code_readonly' => 'Course code cannot be changed after creation',
        'course_description_placeholder' => 'Brief description of the course content',
        'course_assign_teacher' => 'Assign Teacher',
        'course_assign_facility' => 'Assign Facility',
        'course_select_teacher' => 'Select Teacher (Optional)',
        'course_select_facility' => 'Select Facility (Optional)',
        'course_teacher_help' => 'You can assign a teacher now or later',
        'course_facility_help' => 'Choose where the course will be held',
        
        // Facilities
        'facilities_title' => 'Facilities Management',
        'facilities_subtitle' => 'Manage all facility records',
        'facilities_add_title' => 'Add New Facility',
        'facilities_add_subtitle' => 'Enter facility information',
        'facilities_edit_title' => 'Edit Facility',
        'facilities_edit_subtitle' => 'Update facility information',
        'facilities_view_title' => 'Facility Details',
        'facilities_no_facilities' => 'No facilities found.',
        'facilities_add_first' => 'Add the first facility',
        'facilities_no_courses' => 'No courses scheduled for this facility yet.',
        
        // Facility fields
        'facility_code' => 'Facility Code',
        'facility_name' => 'Facility Name',
        'facility_capacity' => 'Capacity',
        'facility_courses' => 'Courses',
        'facility_total_students' => 'Total Students',
        'facility_utilization' => 'Utilization',
        'facility_students' => 'students',
        'facility_capacity_help' => 'Maximum number of students this facility can accommodate',
        
        // Table headers and common
        'actions' => 'Actions',
        'name' => 'Name',
        'status' => 'Status',
        'date' => 'Date',
        'time' => 'Time',
        'details' => 'Details',
        'enrolled_courses' => 'Enrolled Courses',
        'available_courses' => 'Available Courses',
        'scheduled_courses' => 'Scheduled Courses',
        'current_enrollment' => 'Current Enrollment',
        'enrollment_date' => 'Enrollment Date',
        'participants' => 'Participants',
        'capacity' => 'Capacity',
        
        // Status messages
        'over_capacity' => 'Over Capacity',
        'nearly_full' => 'Nearly Full',
        'available' => 'Available',
        'high_usage' => 'High Usage',
        'at_capacity' => 'At capacity but enrollment is still allowed',
        
        // Alerts and messages
        'success_added' => 'added successfully',
        'success_updated' => 'updated successfully',
        'success_deleted' => 'deleted successfully',
        'success_enrolled' => 'enrolled successfully',
        'error_not_found' => 'not found',
        'error_loading' => 'Error loading',
        'error_adding' => 'Error adding',
        'error_updating' => 'Error updating',
        'error_deleting' => 'Error deleting',
        'error_enrolling' => 'Error enrolling',
        'confirm_delete' => 'Are you sure you want to delete this',
        'confirm_enroll' => 'Are you sure you want to enroll',
        'loading' => 'Loading...',
        'saving' => 'Saving...',
        
        // Form validation
        'required_field' => 'is required',
        'invalid_email' => 'Please enter a valid email address',
        'invalid_date' => 'Please enter a valid date',
        'invalid_number' => 'Please enter a valid number',
        'password_mismatch' => 'Passwords do not match',
        'grade_range' => 'Grade must be 1, 2, or 3',
        'capacity_positive' => 'Capacity must be greater than 0',
        'end_after_start' => 'End date must be after start date',
        'already_exists' => 'already exists',
        'already_enrolled' => 'Student is already enrolled in this course!',
        
        // User management
        'user_accounts' => 'Student Accounts Needing Student Records',
        'user_accounts_help' => 'These user accounts have student role but no corresponding student record for course management',
        'username' => 'Username',
        'email' => 'Email',
        'created' => 'Created',
        'create_student_record' => 'Create Student Record',
        'create_student_for' => 'Create a student record for',
        'user_account' => 'User Account',
        'linked_account' => 'Linked to user account',
        'no_user_account' => 'No user account',
        'active_students' => 'Active Students',
        'active_students_help' => 'Students with complete records who can enroll in courses',
        'student_record_created' => 'Student record created successfully and linked to user account!',
        'user_not_found' => 'User not found or not a student role.',
        
        // Guidelines and help
        'subject_examples' => 'Subject Examples',
        'common_subjects' => 'Common subject areas:',
        'course_code_guidelines' => 'Course Code Guidelines',
        'course_naming_conventions' => 'Suggested naming conventions:',
        'facility_code_guidelines' => 'Facility Code Guidelines',
        'facility_naming_conventions' => 'Suggested naming conventions:',
        'current_enrollment_info' => 'Current Enrollment Information',
        'current_assignments' => 'Current Course Assignments',
        'assigned_courses' => 'Assigned Courses',
        'total_students_count' => 'Total Students',
        'currently_teaching' => 'Currently teaching:',
        'capacity_analysis' => 'Capacity Analysis',
        'visual_representation' => 'Visual representation of facility utilization',
        'overall_utilization' => 'Overall Utilization:',
        'total_capacity' => 'Total Capacity',
        'total_enrolled' => 'Total Enrolled',
        'available_spots' => 'Available Spots',
        'warning_over_capacity' => 'Warning: This course is currently over the facility capacity! Consider moving to a larger facility or limiting enrollment.',
        'note_over_capacity' => 'Note: Some courses are at or over capacity. You can still enroll students, but this may create overcrowding issues. Consider the facility limitations before enrolling.',
        
        // Footer
        'footer_text' => 'Course Management System. All rights reserved.',
        'mobile_optimized' => 'Mobile Optimized',
        
        // Miscellaneous
        'in' => 'in',
        'at' => 'at',
        'on' => 'on',
        'for' => 'for',
        'with' => 'with',
        'students_enrolled' => 'students enrolled',
        'no_description' => 'No description available',
        'no_teacher_assigned' => 'No teacher assigned',
        'no_facility_assigned' => 'No facility assigned',
        'select_grade' => 'Select Grade',
        'select_category' => 'Select category',
    ],
    
    'fi' => [
        // Navigation
        'nav_dashboard' => 'Kojelauta',
        'nav_students' => 'Opiskelijat',
        'nav_teachers' => 'Opettajat',
        'nav_courses' => 'Kurssit',
        'nav_facilities' => 'Tilat',
        'nav_title' => 'Kurssinhallintajärjestelmä',
        
        // Common buttons
        'btn_add' => 'Lisää',
        'btn_edit' => 'Muokkaa',
        'btn_delete' => 'Poista',
        'btn_view' => 'Näytä',
        'btn_save' => 'Tallenna',
        'btn_cancel' => 'Peruuta',
        'btn_back' => 'Takaisin',
        'btn_submit' => 'Lähetä',
        'btn_enroll' => 'Ilmoittaudu',
        'btn_search' => 'Hae',
        'btn_filter' => 'Suodata',
        'btn_export' => 'Vie',
        'btn_import' => 'Tuo',
        'btn_back_to_list' => 'Takaisin listaan',
        'btn_back_to_details' => 'Takaisin tietoihin',
        
        // Dashboard
        'dashboard_title' => 'Kojelauta',
        'dashboard_subtitle' => 'Kurssinhallintajärjestelmän yleiskatsaus',
        'dashboard_total_students' => 'Opiskelijoita yhteensä',
        'dashboard_total_teachers' => 'Opettajia yhteensä',
        'dashboard_total_courses' => 'Kursseja yhteensä',
        'dashboard_total_facilities' => 'Tiloja yhteensä',
        'dashboard_quick_actions' => 'Pikatoiminnot',
        'dashboard_recent_activity' => 'Viimeaikainen toiminta',
        
        // Students
        'students_title' => 'Opiskelijahallinto',
        'students_subtitle' => 'Hallitse kaikkia opiskelijatietoja',
        'students_add_title' => 'Lisää uusi opiskelija',
        'students_add_subtitle' => 'Syötä opiskelijan tiedot',
        'students_edit_title' => 'Muokkaa opiskelijaa',
        'students_edit_subtitle' => 'Päivitä opiskelijan tiedot',
        'students_view_title' => 'Opiskelijan tiedot',
        'students_enroll_title' => 'Ilmoita opiskelija kurssille',
        'students_no_students' => 'Opiskelijoita ei löytynyt.',
        'students_add_first' => 'Lisää ensimmäinen opiskelija',
        'students_not_enrolled' => 'Tämä opiskelija ei ole vielä ilmoittautunut millekään kurssille.',
        'students_enroll_first' => 'Ilmoittaudu ensimmäiselle kurssille',
        'students_all_enrolled' => 'Kaikille kursseille ilmoittautunut!',
        'students_all_courses_enrolled' => 'Tämä opiskelija on ilmoittautunut kaikille saatavilla oleville kursseille.',
        
        // Student fields
        'student_number' => 'Opiskelijanumero',
        'student_first_name' => 'Etunimi',
        'student_surname' => 'Sukunimi',
        'student_birthday' => 'Syntymäpäivä',
        'student_grade' => 'Vuosikurssi',
        'student_age' => 'Ikä',
        'student_name' => 'Nimi',
        'student_years_old' => 'vuotta vanha',
        
        // Teachers
        'teachers_title' => 'Opettajahallinto',
        'teachers_subtitle' => 'Hallitse kaikkia opettajatietoja',
        'teachers_add_title' => 'Lisää uusi opettaja',
        'teachers_add_subtitle' => 'Syötä opettajan tiedot',
        'teachers_edit_title' => 'Muokkaa opettajaa',
        'teachers_edit_subtitle' => 'Päivitä opettajan tiedot',
        'teachers_view_title' => 'Opettajan tiedot',
        'teachers_no_teachers' => 'Opettajia ei löytynyt.',
        'teachers_add_first' => 'Lisää ensimmäinen opettaja',
        'teachers_not_assigned' => 'Tätä opettajaa ei ole tällä hetkellä määrätty millekään kurssille.',
        
        // Teacher fields
        'teacher_id' => 'Opettajan tunniste',
        'teacher_first_name' => 'Etunimi',
        'teacher_surname' => 'Sukunimi',
        'teacher_subject' => 'Oppiaine/Erikoisala',
        'teacher_substance' => 'Oppiaine',
        'teacher_name' => 'Opettaja',
        'teacher_id_readonly' => 'Opettajan tunnistetta ei voi muuttaa luomisen jälkeen',
        'teacher_subject_help' => 'Oppiaine-alue, johon tämä opettaja erikoistuu',
        
        // Courses
        'courses_title' => 'Kurssihallinto',
        'courses_subtitle' => 'Hallitse kaikkia kurssitietoja',
        'courses_add_title' => 'Lisää uusi kurssi',
        'courses_add_subtitle' => 'Syötä kurssin tiedot',
        'courses_edit_title' => 'Muokkaa kurssia',
        'courses_edit_subtitle' => 'Päivitä kurssin tiedot',
        'courses_view_title' => 'Kurssin tiedot',
        'courses_no_courses' => 'Kursseja ei löytynyt.',
        'courses_add_first' => 'Lisää ensimmäinen kurssi',
        'courses_no_students_enrolled' => 'Tälle kurssille ei ole vielä ilmoittautunut opiskelijoita.',
        
        // Course fields
        'course_code' => 'Kurssikoodi',
        'course_name' => 'Kurssin nimi',
        'course_description' => 'Kuvaus',
        'course_start_date' => 'Alkupäivä',
        'course_end_date' => 'Loppupäivä',
        'course_teacher' => 'Opettaja',
        'course_facility' => 'Tila',
        'course_enrollment' => 'Ilmoittautuminen',
        'course_duration' => 'Kesto',
        'course_days' => 'päivää',
        'course_code_readonly' => 'Kurssikoodia ei voi muuttaa luomisen jälkeen',
        'course_description_placeholder' => 'Lyhyt kuvaus kurssin sisällöstä',
        'course_assign_teacher' => 'Määritä opettaja',
        'course_assign_facility' => 'Määritä tila',
        'course_select_teacher' => 'Valitse opettaja (vapaaehtoinen)',
        'course_select_facility' => 'Valitse tila (vapaaehtoinen)',
        'course_teacher_help' => 'Voit määrittää opettajan nyt tai myöhemmin',
        'course_facility_help' => 'Valitse missä kurssi pidetään',
        
        // Facilities
        'facilities_title' => 'Tilahallinto',
        'facilities_subtitle' => 'Hallitse kaikkia tilatietoja',
        'facilities_add_title' => 'Lisää uusi tila',
        'facilities_add_subtitle' => 'Syötä tilan tiedot',
        'facilities_edit_title' => 'Muokkaa tilaa',
        'facilities_edit_subtitle' => 'Päivitä tilan tiedot',
        'facilities_view_title' => 'Tilan tiedot',
        'facilities_no_facilities' => 'Tiloja ei löytynyt.',
        'facilities_add_first' => 'Lisää ensimmäinen tila',
        'facilities_no_courses' => 'Tälle tilalle ei ole vielä ajoitettu kursseja.',
        
        // Facility fields
        'facility_code' => 'Tilatunnus',
        'facility_name' => 'Tilan nimi',
        'facility_capacity' => 'Kapasiteetti',
        'facility_courses' => 'Kurssit',
        'facility_total_students' => 'Opiskelijoita yhteensä',
        'facility_utilization' => 'Käyttöaste',
        'facility_students' => 'opiskelijaa',
        'facility_capacity_help' => 'Suurin määrä opiskelijoita, jotka tila voi vastaanottaa',
        
        // Table headers and common
        'actions' => 'Toiminnot',
        'name' => 'Nimi',
        'status' => 'Tila',
        'date' => 'Päivämäärä',
        'time' => 'Aika',
        'details' => 'Tiedot',
        'enrolled_courses' => 'Ilmoittautuneet kurssit',
        'available_courses' => 'Saatavilla olevat kurssit',
        'scheduled_courses' => 'Ajoitetut kurssit',
        'current_enrollment' => 'Nykyinen ilmoittautuminen',
        'enrollment_date' => 'Ilmoittautumispäivä',
        'participants' => 'Osallistujat',
        'capacity' => 'Kapasiteetti',
        
        // Status messages
        'over_capacity' => 'Yli kapasiteetin',
        'nearly_full' => 'Melkein täysi',
        'available' => 'Saatavilla',
        'high_usage' => 'Paljon käyttöä',
        'at_capacity' => 'Kapasiteetti täynnä, mutta ilmoittautuminen on edelleen sallittua',
        
        // Alerts and messages
        'success_added' => 'lisätty onnistuneesti',
        'success_updated' => 'päivitetty onnistuneesti',
        'success_deleted' => 'poistettu onnistuneesti',
        'success_enrolled' => 'ilmoittautunut onnistuneesti',
        'error_not_found' => 'ei löytynyt',
        'error_loading' => 'Virhe ladattaessa',
        'error_adding' => 'Virhe lisättäessä',
        'error_updating' => 'Virhe päivitettäessä',
        'error_deleting' => 'Virhe poistettaessa',
        'error_enrolling' => 'Virhe ilmoittauduttaessa',
        'confirm_delete' => 'Oletko varma, että haluat poistaa tämän',
        'confirm_enroll' => 'Oletko varma, että haluat ilmoittaa',
        'loading' => 'Ladataan...',
        'saving' => 'Tallennetaan...',
        
        // Form validation
        'required_field' => 'on pakollinen',
        'invalid_email' => 'Syötä kelvollinen sähköpostiosoite',
        'invalid_date' => 'Syötä kelvollinen päivämäärä',
        'invalid_number' => 'Syötä kelvollinen numero',
        'password_mismatch' => 'Salasanat eivät täsmää',
        'grade_range' => 'Vuosikurssin tulee olla 1, 2 tai 3',
        'capacity_positive' => 'Kapasiteetin tulee olla suurempi kuin 0',
        'end_after_start' => 'Loppupäivän tulee olla alkupäivän jälkeen',
        'already_exists' => 'on jo olemassa',
        'already_enrolled' => 'Opiskelija on jo ilmoittautunut tälle kurssille!',
        
        // User management
        'user_accounts' => 'Opiskelija käyttäjätilit, jotka tarvitsevat opiskelijan tiedot',
        'user_accounts_help' => 'Näillä käyttäjätileillä on opiskelijan rooli, mutta niillä ei ole vastaavia opiskelijatietoja kurssinhallintaa varten',
        'username' => 'Käyttäjänimi',
        'email' => 'Sähköposti',
        'created' => 'Luotu',
        'create_student_record' => 'Luo opiskelijan tiedot',
        'create_student_for' => 'Luo opiskelijan tiedot henkilölle',
        'user_account' => 'Käyttäjätili',
        'linked_account' => 'Linkitetty käyttäjätiliin',
        'no_user_account' => 'Ei käyttäjätiliä',
        'active_students' => 'Aktiiviset opiskelijat',
        'active_students_help' => 'Opiskelijat, joilla on täydelliset tiedot ja jotka voivat ilmoittautua kursseille',
        'student_record_created' => 'Opiskelijan tiedot luotu onnistuneesti ja linkitetty käyttäjätiliin!',
        'user_not_found' => 'Käyttäjää ei löytynyt tai hänellä ei ole opiskelijan roolia.',
        
        // Guidelines and help
        'subject_examples' => 'Oppiaine-esimerkkejä',
        'common_subjects' => 'Yleisiä oppiaineita:',
        'course_code_guidelines' => 'Kurssikoodin ohjeet',
        'course_naming_conventions' => 'Ehdotetut nimeämiskäytännöt:',
        'facility_code_guidelines' => 'Tilatunnuksen ohjeet',
        'facility_naming_conventions' => 'Ehdotetut nimeämiskäytännöt:',
        'current_enrollment_info' => 'Nykyinen ilmoittautumistilanne',
        'current_assignments' => 'Nykyiset kurssiasignments',
        'assigned_courses' => 'Määrätyt kurssit',
        'total_students_count' => 'Opiskelijoita yhteensä',
        'currently_teaching' => 'Opettaa tällä hetkellä:',
        'capacity_analysis' => 'Kapasiteettianalyysi',
        'visual_representation' => 'Visuaalinen esitys tilan käyttöasteesta',
        'overall_utilization' => 'Kokonaiskäyttöaste:',
        'total_capacity' => 'Kokonaiskapasiteetti',
        'total_enrolled' => 'Ilmoittautuneita yhteensä',
        'available_spots' => 'Vapaita paikkoja',
        'warning_over_capacity' => 'Varoitus: Tämä kurssi on tällä hetkellä yli tilan kapasiteetin! Harkitse siirtämistä suurempaan tilaan tai ilmoittautumisen rajoittamista.',
        'note_over_capacity' => 'Huomio: Jotkin kurssit ovat kapasiteetissaan tai yli sen. Voit silti ilmoittaa opiskelijoita, mutta tämä voi aiheuttaa ylitäyttöongelmia. Harkitse tilan rajoituksia ennen ilmoittamista.',
        
        // Footer
        'footer_text' => 'Kurssinhallintajärjestelmä. Kaikki oikeudet pidätetään.',
        'mobile_optimized' => 'Mobiilioptimoi',
        
        // Miscellaneous
        'in' => 'sijaitsee',
        'at' => 'osoitteessa',
        'on' => 'päivänä',
        'for' => 'varten',
        'with' => 'kanssa',
        'students_enrolled' => 'opiskelijaa ilmoittautunut',
        'no_description' => 'Kuvausta ei ole saatavilla',
        'no_teacher_assigned' => 'Opettajaa ei määritetty',
        'no_facility_assigned' => 'Tilaa ei määritetty',
        'select_grade' => 'Valitse vuosikurssi',
        'select_category' => 'Valitse kategoria',
    ]
];

// Translation function
function t($key, $default = null) {
    global $translations;
    $lang = $_SESSION['language'] ?? 'en';
    
    if (isset($translations[$lang][$key])) {
        return $translations[$lang][$key];
    }
    
    // Fallback to English if not found in current language
    if ($lang !== 'en' && isset($translations['en'][$key])) {
        return $translations['en'][$key];
    }
    
    // Return default or key if nothing found
    return $default ?: $key;
}

// Get current language
function getCurrentLanguage() {
    return $_SESSION['language'] ?? 'en';
}

// Get language name
function getLanguageName($lang = null) {
    $lang = $lang ?: getCurrentLanguage();
    $names = [
        'en' => 'English',
        'fi' => 'Suomi'
    ];
    return $names[$lang] ?? 'English';
}

// Generate language switcher HTML
function getLanguageSwitcher() {
    $currentLang = getCurrentLanguage();
    $currentUrl = $_SERVER['REQUEST_URI'];
    
    // Remove existing lang parameter
    $urlParts = parse_url($currentUrl);
    $path = $urlParts['path'] ?? '';
    $query = [];
    
    if (isset($urlParts['query'])) {
        parse_str($urlParts['query'], $query);
        unset($query['lang']); // Remove existing lang parameter
    }
    
    $baseUrl = $path . (!empty($query) ? '?' . http_build_query($query) : '');
    $separator = empty($query) ? '?' : '&';
    
    $html = '<div class="language-switcher">';
    $html .= '<div class="language-dropdown">';
    $html .= '<button class="language-toggle" onclick="toggleLanguageDropdown()">';
    $html .= '<i class="fas fa-globe"></i> ' . getLanguageName($currentLang);
    $html .= ' <i class="fas fa-chevron-down"></i>';
    $html .= '</button>';
    $html .= '<div class="language-options" id="languageOptions">';
    
    $languages = [
        'en' => ['name' => 'English', 'flag' => '🇺🇸'],
        'fi' => ['name' => 'Suomi', 'flag' => '🇫🇮']
    ];
    
    foreach ($languages as $code => $info) {
        $active = $code === $currentLang ? 'active' : '';
        $html .= '<a href="' . $baseUrl . $separator . 'lang=' . $code . '" class="language-option ' . $active . '">';
        $html .= '<span class="flag">' . $info['flag'] . '</span> ' . $info['name'];
        $html .= '</a>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

// Format date according to language
function formatDate($date, $format = null) {
    $lang = getCurrentLanguage();
    $timestamp = is_string($date) ? strtotime($date) : $date;
    
    if ($lang === 'fi') {
        $months = [
            1 => 'tammikuu', 2 => 'helmikuu', 3 => 'maaliskuu',
            4 => 'huhtikuu', 5 => 'toukokuu', 6 => 'kesäkuu',
            7 => 'heinäkuu', 8 => 'elokuu', 9 => 'syyskuu',
            10 => 'lokakuu', 11 => 'marraskuu', 12 => 'joulukuu'
        ];
        
        if ($format === 'long') {
            $day = date('j', $timestamp);
            $month = $months[date('n', $timestamp)];
            $year = date('Y', $timestamp);
            return $day . '. ' . $month . 'ta ' . $year;
        } else {
            return date('j.n.Y', $timestamp);
        }
    } else {
        return $format === 'long' ? date('F j, Y', $timestamp) : date('M j, Y', $timestamp);
    }
}

// Pluralize function
function pluralize($count, $singular, $plural = null) {
    $lang = getCurrentLanguage();
    
    if ($lang === 'fi') {
        // Finnish pluralization is more complex, but simplified here
        return $count == 1 ? $singular : ($plural ?: $singular . 'a');
    } else {
        return $count == 1 ? $singular : ($plural ?: $singular . 's');
    }
}

// Number formatting
function formatNumber($number) {
    $lang = getCurrentLanguage();
    
    if ($lang === 'fi') {
        return number_format($number, 0, ',', ' ');
    } else {
        return number_format($number);
    }
}

?>