# CBC Integrated Learning Management System

## Overview

This is a comprehensive **Competency-Based Curriculum (CBC) Digital Education Platform** built with Laravel, MySQL, and Vue.js. The system supports the full national-scale CBC education ecosystem in Kenya.

## Technology Stack

- **Backend**: Laravel 12.x
- **Frontend**: Vue.js 3 with Inertia.js
- **Database**: MySQL/SQLite
- **Authentication**: Laravel Sanctum + Fortify
- **Authorization**: Spatie Laravel Permission
- **Queue**: Redis/Database
- **Cache**: Redis/Database

## Database Schema

The system includes **150+ database tables** organized into the following modules:

### Core Modules
1. **Authentication & RBAC** - Users, roles, permissions, login logs
2. **School Administration** - Schools, branches, settings, documents
3. **Academic Structure** - Academic years, terms, grade levels, streams, classes
4. **Student Management** - Students, guardians, enrollments, documents
5. **Teacher Management** - Teachers, qualifications, attendance, leaves
6. **CBC Curriculum** - Learning areas, subjects, strands, sub-strands, competencies
7. **Assessment System** - Assessments, rubrics, grading scales, report cards
8. **Attendance** - Student attendance, teacher attendance, sessions
9. **Timetable** - Periods, timetables, rooms, substitutions
10. **Communication** - Conversations, messages, announcements, notifications

### Extended Modules
11. **Behavior & Discipline** - Behavior records, disciplinary actions, interventions
12. **LMS** - Courses, modules, lessons, enrollments, progress tracking
13. **Assignments & Portfolio** - Assignments, submissions, portfolios, talents
14. **Finance** - Fee structures, invoices, payments, expenses
15. **Library** - Books, loans, reservations, e-resources
16. **Transport** - Vehicles, routes, drivers, trip logs
17. **Hostel** - Hostels, rooms, allocations, exeats
18. **Health** - Medical records, visits, screenings, medications
19. **Events & Activities** - Events, clubs, extracurricular activities
20. **Career Guidance** - Career pathways, counseling, mentorship
21. **Analytics** - Reports, metrics, dashboards, audit logs
22. **Integrations** - API keys, webhooks, SMS/Email logs

## Roles & Permissions

The system supports 12 user roles:
- Super Admin
- School Admin
- Principal
- Deputy Principal
- Head of Department (HOD)
- Teacher
- Class Teacher
- Parent/Guardian
- Student
- Finance Officer
- Librarian
- School Nurse

Each role has specific permissions for their functionality.

## API Endpoints (v1)

### Authentication
```
POST   /api/v1/auth/login          - User login
POST   /api/v1/auth/logout         - User logout
GET    /api/v1/auth/me             - Get current user
POST   /api/v1/auth/refresh        - Refresh token
```

### Schools
```
GET    /api/v1/schools             - List schools
POST   /api/v1/schools             - Create school
GET    /api/v1/schools/{id}        - Get school
PUT    /api/v1/schools/{id}        - Update school
DELETE /api/v1/schools/{id}        - Delete school
GET    /api/v1/schools/{id}/statistics - School statistics
GET    /api/v1/schools/{id}/settings   - School settings
```

### Students
```
GET    /api/v1/students            - List students
POST   /api/v1/students            - Create student
GET    /api/v1/students/{id}       - Get student
PUT    /api/v1/students/{id}       - Update student
DELETE /api/v1/students/{id}       - Delete student
PATCH  /api/v1/students/{id}/status - Update status
GET    /api/v1/students/{id}/attendance - Attendance summary
GET    /api/v1/students/{id}/academic-performance - Academic records
```

### Teachers
```
GET    /api/v1/teachers            - List teachers
POST   /api/v1/teachers            - Create teacher
GET    /api/v1/teachers/{id}       - Get teacher
PUT    /api/v1/teachers/{id}       - Update teacher
DELETE /api/v1/teachers/{id}       - Delete teacher
GET    /api/v1/teachers/{id}/subjects  - Teacher's subjects
GET    /api/v1/teachers/{id}/timetable - Teacher's timetable
```

### Guardians
```
GET    /api/v1/guardians           - List guardians
POST   /api/v1/guardians           - Create guardian
GET    /api/v1/guardians/{id}      - Get guardian
PUT    /api/v1/guardians/{id}      - Update guardian
DELETE /api/v1/guardians/{id}      - Delete guardian
GET    /api/v1/guardians/{id}/students - Guardian's children
```

## Installation

```bash
# Clone the repository
git clone [repository-url]
cd CBC

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations with seeders
php artisan migrate --seed

# Start development server
php artisan serve
npm run dev
```

## Demo Credentials

After seeding, the following accounts are available:

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@cbcplatform.com | password |
| School Admin | school@demo.com | password |
| Teacher | teacher@demo.com | password |
| Parent | parent@demo.com | password |
| Student | student@demo.com | password |

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/V1/          # API Controllers
│   ├── Middleware/          # Custom middleware
│   ├── Requests/            # Form requests
│   └── Resources/           # API Resources
├── Models/                  # Eloquent models
│   ├── Academic/            # Academic-related models
│   ├── Curriculum/          # Curriculum models
│   └── ...
├── Services/                # Business logic services
└── Providers/               # Service providers

database/
├── migrations/              # Database migrations (33 files)
└── seeders/                 # Database seeders

routes/
├── api.php                  # API routes
├── web.php                  # Web routes
└── settings.php             # Settings routes

resources/
└── js/                      # Vue.js frontend
```

## License

This project is proprietary software.
