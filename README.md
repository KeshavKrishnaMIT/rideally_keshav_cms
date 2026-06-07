# Summer Training with RideAlly

## Content Management System (CMS)

A complete multi-role Content Management System developed during my Summer Training with RideAlly. This project enables content creation, moderation, publishing, reporting, and user management through dedicated dashboards for different user roles.

---

## Live Demo

🌐 Website URL

**https://rideally-keshav-cms.lovestoblog.com/mini_pro_rideally/auth/login.php**

---

## Demo Credentials

### User Account

**Email:** demo@rideally.com

**Password:** demo123

**Role:** User

---

## Project Overview

This CMS provides a centralized platform for managing website content through role-based access control. Different users are assigned different permissions, ensuring secure and organized content workflows.

The system supports:

- User Management
- Category Management
- Post Management
- Approval Workflow
- Reporting Dashboard
- Authentication System
- Role-Based Access Control

---

## User Roles

### Super Admin

- Full system access
- Manage all users
- Create admins, editors, authors, and users
- View reports and analytics
- Approve or reject content
- Manage categories

### Admin

- Manage posts and categories
- Moderate content
- View reports
- Manage assigned users

### Editor

- Review submitted posts
- Approve or reject content
- Maintain content quality

### Author

- Create and edit own posts
- Submit posts for approval

### User

- Access approved content
- Basic account access

---

## Major Features

### Authentication System

- Secure Login System
- Session Management
- Role-Based Redirection
- Access Restrictions

### User Management

- Create Users
- Edit Users
- Delete Users
- Activate / Deactivate Accounts
- Assign Roles

### Category Management

- Create Categories
- Update Categories
- Delete Categories
- Organize Content

### Post Management

- Create Posts
- Edit Posts
- Delete Posts
- Upload Featured Images
- Assign Categories

### Content Approval Workflow

Posts move through different stages:

1. Draft
2. Pending Review
3. Approved
4. Rejected

This ensures quality control before publishing.

### Reports Dashboard

Provides:

- Total Users
- Total Posts
- Approved Posts
- Pending Posts
- Categories Count
- Comments Count

### Dashboard Analytics

Displays:

- User Statistics
- Recent Posts
- New Users
- Content Status Overview

---

## Database Design

Main Tables:

### users

Stores:

- User Information
- Email
- Password
- Role
- Status

### posts

Stores:

- Post Details
- Content
- Author
- Status
- Category

### categories

Stores:

- Category Information

### comments

Stores:

- User Comments
- Approval Status

---

## Project Structure

```text
mini_pro_rideally/
│
├── admin/
├── super_admin/
├── editor/
├── author/
├── user/
│
├── auth/
│   ├── login.php
│   ├── logout.php
│
├── config/
│   ├── db.php
│   ├── constants.php
│
├── includes/
├── assets/
│   ├── css/
│   ├── js/
│   └── uploads/
│
├── index.php
└── .htaccess
```

---

## Technologies Used

### Frontend

- HTML5
- CSS3
- JavaScript
- Bootstrap

### Backend

- PHP

### Database

- MySQL

### Hosting

- InfinityFree

### Version Control

- Git
- GitHub

---

## Learning Outcomes

Through this project I learned:

- PHP Development
- MySQL Database Design
- CRUD Operations
- Authentication & Authorization
- Session Handling
- Role-Based Access Control
- Content Management Workflows
- Deployment on Shared Hosting
- Git & GitHub Version Control

---

## Future Improvements

Potential enhancements:

- Password Hashing
- Email Verification
- Rich Text Editor
- Search Functionality
- REST API
- Media Library
- Activity Logs
- Advanced Analytics
- Dark Mode
- Notifications

---

## Developed During

**Summer Training with RideAlly**

This project was developed as part of practical industry-oriented training focused on web development, database management, authentication systems, content workflows, and deployment practices.

---

## Author

**Keshav Krishna Singh**

B.Tech Student

Summer Training Project – RideAlly

---

## Thank You

Thank you for visiting this repository.
