# Summer Training with RideAlly

## Content Management System (CMS)

A complete multi-role Content Management System developed during my Summer Training with RideAlly. This project enables content creation, moderation, publishing, reporting, and user management through dedicated dashboards for different user roles.

---

## Live Demo

🌐 **Website URL**

**https://rideally-keshav-cms.lovestoblog.com/mini_pro_rideally/auth/login.php**

---

## Demo Credentials

The following demo accounts are provided to explore different roles and functionalities of the Content Management System.

### User Account

**Email:** [user@rideally.com](mailto:user@rideally.com)
**Password:** user123
**Role:** User

**Access:**

* View approved content
* Access the user dashboard
* Interact with published posts

---

### Author Account

**Email:** [author@rideally.com](mailto:author@rideally.com)
**Password:** author123
**Role:** Author

**Access:**

* Create new posts
* Edit own posts
* Submit posts for review and approval

---

### Editor Account

**Email:** [editor@rideally.com](mailto:editor@rideally.com)
**Password:** editor123
**Role:** Editor

**Access:**

* Review submitted posts
* Approve or reject content
* Maintain content quality standards

---

> **Note:** Super Admin and Admin accounts are reserved for system administration purposes and are not included in the public demo credentials.


### Notes

- User can view approved content and access the user dashboard.
- Author can create and manage their own posts.
- Editor can review submitted posts and maintain content quality.
- Super Admin and Admin accounts are reserved for administration and are not included in public demo credentials.

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

- Create and edit their own posts
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

Provides insights such as:

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

### Main Tables

#### users

Stores:

- User Information
- Email
- Password
- Role
- Status

#### posts

Stores:

- Post Details
- Content
- Author Information
- Status
- Category Association

#### categories

Stores:

- Category Information

#### comments

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
│   └── logout.php
│
├── config/
│   ├── db.php
│   └── constants.php
│
├── includes/
├── assets/
│   ├── css/
│   ├── js/
│   └── uploads/
│
├── index.php
└── .htaccess
