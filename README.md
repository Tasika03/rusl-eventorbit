# RUSL EventOrbit - University Event Guide
COM2303 Web Design | Mini Project - Phase 02

Name: D.T.S. Weththasinghe 

Index No: 6222

Reg No: ASP/2023/002

## about
A university event guide for Rajarata University of Sri Lanka. Students and staff can browse events happening across all faculties.

## Pages
Home
Events
Add Event
Contact Us

## Features
Image Slider on home page
Search and filter events by category and faculty
Click events to see full details in a popup
Form with validation to submit new events
Contact Form

## Technologies used
HTML, CSS, Bootstrap, Js

## Live Site
https://tasika03.github.io/rusl-eventorbit/

  # Phase 3 – PHP & Database Integration

## Requirements
- WAMP Server installed and running
- A Web browser

## Database Setup
1. Start WAMP Server (icon should be green in taskbar)
2. Open phpMyAdmin: http://localhost/phpmyadmin5.2.3
3. Click **New** on the left sidebar
4. Name the database "eventorbit_db" → click **Create**
5. Select "eventorbit_db" from the left sidebar
6. Click the **Import** tab at the top
7. Click **Choose File** → select "database.sql" from the project folder
8. Click **Go** at the bottom

## Running the Project
1. Copy the "eventorbit" folder into "C:\wamp64\www\"
2. Make sure WAMP is running (green icon)
3. Open your browser and go to: "http://localhost/eventorbit/"

## Features Added in Phase 3
- User registration and login with password hashing
- Session-based authentication with logout
- Personal dashboard to submit, edit and delete events
- Events submitted by users go through admin approval before appearing publicly
- Contact form stores messages to the database (login required)
- Events page dynamically loads approved events from the database
- Cookie-based navbar updates to show login state
