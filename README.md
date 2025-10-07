"# balas-2.0"

BARANGAY BALAS ONLINE SERVICES AND MANAGEMENT SYSTEM

A modern, web-based System designed for Barangay Balas, Mexico, Pampanga.
This system provides digital management for residents, document requests, barangay officials, and announcements. 



 FEATURES

Resident Management
- Add, view, edit, verify, and delete residents  
- Verification with admin notes  
- Export residents to Excel  
- View full resident details via modals

Document Requests
- Residents can request documents such as:
  - Certificate of Indigency  
  - Barangay Clearance  
  - Business Permit    
- Admin can approve/disapprove equests  
- Automatic PHPWord document generation for download  


 Admin Panel
- Secure login authentication  
- Dashboard overview of residents, requests, and announcements  
- CRUD management for:
  - Barangay Officials  
  - Announcements  
  - Residents  
  - Document Requests  
- Portal registration approval (Approve / Disapprove)  
- Export data by document type and date range

 Announcements
- Admin and Official can post and manage barangay announcements  
- Residents can view announcements on the portal

Resident Portal
- Residents can register for a portal account  
- One account per approved resident  
- Upload valid ID for approval
- Track document request status online

 Document Generation
- Auto-generate DOCX to PDF documents using PHPWord and LibreOffice

Census Management (Resident approved account)
-Serves as the official record of all residents within Barangay Balas.  
-It supports both admin and resident access for transparency and accountability.

Tech Stack

PHP 8+ | Backend scripting 
MySQL | Database 
Bootstrap 5 | Frontend UI framework 
jQuery + AJAX | Dynamic modals and form submission 
PHPWord | Document generation (DOCX) 
Composer | PHP dependency manager 


 INSTALLATION GUIDE

Follow these steps to set up the Barangay Balas System locally:

 Prerequisites
Make sure you have installed:
- XAMPP

- PHP 8.0+
•In php.ini, remove the  ‘ ; ’ at the beginning.
;extension=gd  to extension=gd
;extension=zip to extension=zip

- Composer (for dependency management)
•Install in VS Code terminal
composer require phpoffice/phpword
composer require chillerlan/php-qrcode
composer require phpoffice/phpspreadsheet

-MySQL  server






DEVELOPER NOTES

Default admin credentials:

Username: administrator
Password: 12345678

Database name: Balas

Tested on PHP 8.1 and MySQL 8.0

Recommended browser: Google Chrome / Edge


This capstone project is developed for Barangay Balas, Mexico, Pampanga
© 2025 Barangay Balas Online Services and Management System — All Rights Reserved.

Developed by BS Info Tech student of Pampanga State University Mexico Campus 
Powered by PHP, MySQL, and Bootstrap 5.



