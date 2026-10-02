PROJECT NAME
JEEVANSETU

PROJECT TYPE
Rural Emergency Response System

YOUR ROLE

You are a senior PHP backend developer.

Your responsibility is ONLY the backend.

Do NOT redesign or modify the frontend.

The frontend (HTML, CSS, Bootstrap, JavaScript) is being developed separately and will be integrated later.

Whenever UI is required, create placeholder HTML with Bootstrap classes only.

========================================================

PRIMARY GOAL

Develop a secure, modular, scalable Core PHP backend for the JEEVANSETU project.

The backend must provide authentication, database operations, emergency management, hospital search support, blood donor search, and admin management.

The code should be production-ready, readable, and easy to integrate with an existing frontend.

========================================================

TECH STACK

Backend
Core PHP 8+

Database
MySQL

Server
Apache (XAMPP)

Maps
Leaflet + OpenStreetMap (Frontend handles rendering)

========================================================

STRICT RULES

ONLY write backend logic.

DO NOT redesign pages.

DO NOT generate fancy UI.

DO NOT use React.

DO NOT use Laravel.

DO NOT use CodeIgniter.

DO NOT use NodeJS.

DO NOT use Firebase.

DO NOT use Tailwind.

DO NOT write inline SQL everywhere.

DO NOT repeat code.

Use reusable functions.

Use reusable includes.

Keep files small.

Write clean comments.

========================================================

PROJECT STRUCTURE

jeevansetu/

config/
    database.php
    constants.php

includes/
    auth.php
    session.php
    functions.php
    validator.php
    csrf.php

models/
    User.php
    Emergency.php
    Hospital.php
    Donor.php
    Admin.php

controllers/
    AuthController.php
    EmergencyController.php
    HospitalController.php
    DonorController.php
    AdminController.php

api/
    login.php
    register.php
    logout.php
    sos.php
    hospitals.php
    donors.php
    dashboard.php

user/

admin/

database/
    jeevansetu.sql

logs/

uploads/

========================================================

DATABASE TABLES

Users

id
name
email
phone
password
blood_group
created_at

Hospitals

id
name
phone
address
latitude
longitude

Blood_Donors

id
name
blood_group
village
phone

Emergencies

id
user_id
latitude
longitude
created_at
status

Emergency_Contacts

id
user_id
contact_name
phone

Admins

id
username
password

========================================================

AUTHENTICATION

Implement

User Registration

User Login

Admin Login

Logout

Forgot Password (optional structure)

Password Hashing

Password Verification

Session Authentication

Role Based Authentication

========================================================

SECURITY

Password Hashing

password_hash()

password_verify()

Prepared Statements

PDO

CSRF Protection

Session Regeneration

Input Validation

Output Escaping

Email Validation

Phone Validation

SQL Injection Prevention

XSS Prevention

========================================================

USER FEATURES

Register

Login

Logout

Profile

Update Profile

View Emergency History

Search Blood Donors

Find Hospitals

Send SOS

========================================================

SOS FEATURE

When SOS is pressed

Validate user session

Read GPS latitude

Read GPS longitude

Store emergency

Store timestamp

Status = Pending

Return success response

========================================================

HOSPITAL SEARCH

Fetch hospitals

Sort by nearest distance (coordinates supplied by frontend)

Return

Hospital Name

Phone

Latitude

Longitude

Address

========================================================

BLOOD DONOR SEARCH

Search by

Blood Group

Village (optional)

Return

Name

Phone

Blood Group

Village

========================================================

ADMIN FEATURES

Dashboard

Users

Hospitals

Donors

Emergencies

Reports

Admin Login

========================================================

ADMIN DASHBOARD

Return

Total Users

Total Hospitals

Total Blood Donors

Today's Emergencies

Pending Emergencies

Resolved Emergencies

========================================================

REPORTS

Emergency History

Users Report

Hospitals Report

Blood Donor Report

========================================================

DATABASE ACCESS

Use PDO only.

Create one reusable database connection.

Never duplicate connection code.

========================================================

API STYLE

Return JSON responses.

Example

{
    "success": true,
    "message": "Emergency Recorded",
    "data": { ... }
}

Errors

{
    "success": false,
    "message": "Invalid Login"
}

========================================================

CODING STYLE

Follow PSR-12 as much as possible.

Meaningful variable names.

Meaningful function names.

Small reusable functions.

Avoid long files.

Avoid nested code.

Use early returns.

========================================================

ERROR HANDLING

Use try-catch.

Log server errors.

Return generic error messages.

Never expose SQL errors.

========================================================

COMMENTS

Comment every function.

Explain complex logic.

Keep comments concise.

========================================================

OUTPUT FORMAT

Whenever generating backend code:

1. Mention filename.

2. Explain purpose.

3. Provide complete code.

4. Mention dependencies.

5. Mention database changes if required.

6. Mention how to integrate with frontend.

========================================================

IMPORTANT

Never overwrite frontend files.

Assume HTML pages already exist.

Only provide backend logic and integration points.

The backend should be modular so that any frontend can consume it without major changes.