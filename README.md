# Faydev Control Panel

A web-based dashboard for managing portfolio and application projects across multiple subdomains. This control panel serves as a centralized management system that generates static JSON files for frontend websites to consume.

## Overview

Faydev Control Panel is designed to manage project data in one place and distribute it to multiple websites. It uses MySQL as the primary database and generates JSON files that can be read by your frontend applications.

### What It Does

- Manages project portfolio with categories and status tracking
- Distributes projects to different subdomains (app and portfolio)
- Generates JSON files automatically when data changes
- Keeps activity logs of all changes
- Creates automatic backups of JSON files before updates

## Features

- **Project Management**: Create, edit, soft-delete, and restore projects
- **Category System**: Organize projects into categories
- **Multi-Subdomain Support**: Configure which projects appear on each subdomain
- **Auto JSON Generation**: JSON files update automatically when you make changes
- **Activity Logging**: Track all changes made through the dashboard
- **Backup System**: Automatic backup of JSON files before overwriting

## Technology Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, Bootstrap 5, jQuery, Font Awesome
- **Authentication**: Session-based login

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- PDO extension enabled

## Installation

### Step 1: Set Up the Database

1. Open your database management tool (phpMyAdmin, MySQL Workbench, or command line)
2. Run the SQL commands from `database/schema.sql`
3. This will create the database and all required tables

### Step 2: Configure the Application

1. Open `includes/config.php`
2. Update the database connection settings in `includes/db.php`:
   - Host (usually `localhost`)
   - Database name
   - Username
   - Password

### Step 3: Set Folder Permissions

Ensure the following folders are writable:
- `uploads/` - for storing project images
- `backup/` - for JSON backups
- `data/` - for generated JSON files

### Step 4: Access the Dashboard

1. Open your browser and navigate to the project folder
2. Login with the default credentials:
   - Email: `admin@faydev.my.id`
   - Password: `admin123`

**Important**: Change the admin password after first login for security.

## Project Structure

```
control-panel/
├── api/                    # AJAX endpoints for actions
├── backup/                 # JSON backup files (auto-generated)
├── data/                   # JSON output files (auto-generated)
├── database/               # SQL schema files
├── includes/               # Shared PHP files
│   ├── config.php         # Main configuration
│   ├── db.php            # Database connection
│   ├── header.php        # Page header
│   └── footer.php        # Page footer
├── uploads/               # Image uploads
├── index.php              # Dashboard home
├── login.php              # Login page
├── logout.php             # Logout handler
├── projects.php          # Project management
├── project-form.php       # Add/Edit project
├── categories.php         # Category management
├── app-config.php        # App subdomain settings
├── portfolio-config.php  # Portfolio subdomain settings
└── logs.php              # Activity logs
```

## How to Use

### Managing Projects

1. Go to the Projects page from the navigation
2. Click "Add Project" to create a new project
3. Fill in the project details:
   - Name (required)
   - Description
   - Technology stack
   - Demo URL
   - GitHub URL
   - Preview image
   - Status (Draft, Development, Live, Archived)
   - Categories
4. Click Save

### Managing Categories

1. Go to the Categories page
2. Add new categories to organize your projects
3. You can see how many projects are in each category

### Configuring Subdomains

**App Configuration** (`app.faydev.my.id`):
- Select which projects to display
- Drag and drop to reorder
- No limit on number of projects

**Portfolio Configuration** (`portfolio.faydev.my.id`):
- Select up to 3 projects to display
- Drag and drop to reorder
- Only the top 3 will appear on the website

### Viewing Activity Logs

The Logs page shows a history of all actions:
- Project created, updated, deleted, restored
- Category created or deleted
- Configuration changes

## JSON Output Files

The system generates three JSON files in the `data/` folder:

1. **projects.json** - All active projects with full details
2. **app-config.json** - List of project slugs for the app subdomain
3. **portfolio-config.json** - List of project slugs for the portfolio subdomain

Your frontend websites can read these JSON files to display projects.

## Security Notes

- The default admin password should be changed immediately
- Keep your database credentials secure
- The `uploads/` folder should only accept image files
- Session timeout follows PHP default settings

## Troubleshooting

### Cannot Connect to Database
- Check your database credentials in `includes/db.php`
- Ensure MySQL service is running
- Verify the database exists

### JSON Files Not Generating
- Check folder permissions on `data/`, `backup/`, and `uploads/`
- Verify PHP has write permissions

### Login Not Working
- Clear browser cookies and try again
- Check session save path is writable

### Images Not Uploading
- Verify `uploads/` folder is writable
- Check PHP upload settings in php.ini

## License

This project is for personal use.
