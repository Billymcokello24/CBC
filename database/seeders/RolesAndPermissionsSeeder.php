<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions by module
        $permissions = [
            // Schools
            'schools.view', 'schools.create', 'schools.update', 'schools.delete', 'schools.settings',

            // Users
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.manage',

            // Students
            'students.view', 'students.create', 'students.update', 'students.delete',
            'students.view_own', 'students.enroll', 'students.transfer',

            // Teachers
            'teachers.view', 'teachers.create', 'teachers.update', 'teachers.delete',
            'teachers.view_own', 'teachers.assign_subjects',

            // Guardians
            'guardians.view', 'guardians.create', 'guardians.update', 'guardians.delete',
            'guardians.view_own',

            // Classes
            'classes.view', 'classes.create', 'classes.update', 'classes.delete',
            'classes.assign_students', 'classes.assign_teachers',

            // Curriculum
            'curriculum.view', 'curriculum.create', 'curriculum.update', 'curriculum.delete',

            // Assessments
            'assessments.view', 'assessments.create', 'assessments.update', 'assessments.delete',
            'assessments.grade', 'assessments.view_own',

            // Attendance
            'attendance.view', 'attendance.mark', 'attendance.update', 'attendance.reports',
            'attendance.view_own',

            // Finance
            'finance.view', 'finance.create', 'finance.update', 'finance.delete',
            'finance.collect_payments', 'finance.view_own', 'finance.reports',

            // Reports
            'reports.view', 'reports.generate', 'reports.export',
            'reports.view_own', 'reports.analytics',

            // Communication
            'communication.announcements', 'communication.messages', 'communication.notifications',

            // Timetable
            'timetable.view', 'timetable.create', 'timetable.update', 'timetable.delete',

            // Library
            'library.view', 'library.manage', 'library.issue_books',

            // Transport
            'transport.view', 'transport.manage',

            // Hostel
            'hostel.view', 'hostel.manage',

            // Health
            'health.view', 'health.manage', 'health.view_own',

            // Events
            'events.view', 'events.create', 'events.update', 'events.delete',

            // Settings
            'settings.view', 'settings.update', 'settings.system',
        ];

        // Create permissions (idempotent with firstOrCreate)
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin - has all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // School Admin
        $schoolAdmin = Role::firstOrCreate(['name' => 'school_admin']);
        $schoolAdmin->givePermissionTo([
            'schools.view', 'schools.update', 'schools.settings',
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.manage',
            'students.view', 'students.create', 'students.update', 'students.delete', 'students.enroll', 'students.transfer',
            'teachers.view', 'teachers.create', 'teachers.update', 'teachers.delete', 'teachers.assign_subjects',
            'guardians.view', 'guardians.create', 'guardians.update', 'guardians.delete',
            'classes.view', 'classes.create', 'classes.update', 'classes.delete', 'classes.assign_students', 'classes.assign_teachers',
            'curriculum.view', 'curriculum.create', 'curriculum.update',
            'assessments.view', 'assessments.create', 'assessments.update', 'assessments.delete', 'assessments.grade',
            'attendance.view', 'attendance.mark', 'attendance.update', 'attendance.reports',
            'finance.view', 'finance.create', 'finance.update', 'finance.collect_payments', 'finance.reports',
            'reports.view', 'reports.generate', 'reports.export', 'reports.analytics',
            'communication.announcements', 'communication.messages', 'communication.notifications',
            'timetable.view', 'timetable.create', 'timetable.update', 'timetable.delete',
            'library.view', 'library.manage', 'library.issue_books',
            'transport.view', 'transport.manage',
            'hostel.view', 'hostel.manage',
            'health.view', 'health.manage',
            'events.view', 'events.create', 'events.update', 'events.delete',
            'settings.view', 'settings.update',
        ]);

        // Principal
        $principal = Role::firstOrCreate(['name' => 'principal']);
        $principal->givePermissionTo([
            'schools.view', 'schools.settings',
            'users.view',
            'students.view', 'students.create', 'students.update', 'students.enroll',
            'teachers.view', 'teachers.create', 'teachers.update', 'teachers.assign_subjects',
            'guardians.view',
            'classes.view', 'classes.create', 'classes.update', 'classes.assign_students', 'classes.assign_teachers',
            'curriculum.view',
            'assessments.view', 'assessments.create', 'assessments.update', 'assessments.grade',
            'attendance.view', 'attendance.reports',
            'finance.view', 'finance.reports',
            'reports.view', 'reports.generate', 'reports.export', 'reports.analytics',
            'communication.announcements', 'communication.messages', 'communication.notifications',
            'timetable.view', 'timetable.create', 'timetable.update',
            'events.view', 'events.create', 'events.update',
        ]);

        // Deputy Principal
        $deputyPrincipal = Role::firstOrCreate(['name' => 'deputy_principal']);
        $deputyPrincipal->givePermissionTo([
            'schools.view',
            'students.view', 'students.update',
            'teachers.view', 'teachers.update',
            'guardians.view',
            'classes.view', 'classes.update', 'classes.assign_students',
            'curriculum.view',
            'assessments.view', 'assessments.create', 'assessments.update', 'assessments.grade',
            'attendance.view', 'attendance.mark', 'attendance.reports',
            'reports.view', 'reports.generate',
            'communication.announcements', 'communication.messages', 'communication.notifications',
            'timetable.view', 'timetable.update',
            'events.view', 'events.create', 'events.update',
        ]);

        // Head of Department (HOD)
        $hod = Role::firstOrCreate(['name' => 'hod']);
        $hod->givePermissionTo([
            'students.view',
            'teachers.view',
            'classes.view',
            'curriculum.view', 'curriculum.update',
            'assessments.view', 'assessments.create', 'assessments.update', 'assessments.grade',
            'attendance.view', 'attendance.reports',
            'reports.view', 'reports.generate',
            'communication.messages', 'communication.notifications',
            'timetable.view',
        ]);

        // Teacher
        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->givePermissionTo([
            'students.view',
            'teachers.view_own',
            'classes.view',
            'curriculum.view',
            'assessments.view', 'assessments.create', 'assessments.update', 'assessments.grade', 'assessments.view_own',
            'attendance.view', 'attendance.mark', 'attendance.view_own',
            'reports.view', 'reports.view_own',
            'communication.messages', 'communication.notifications',
            'timetable.view',
        ]);

        // Class Teacher
        $classTeacher = Role::firstOrCreate(['name' => 'class_teacher']);
        $classTeacher->givePermissionTo([
            'students.view', 'students.update',
            'teachers.view_own',
            'guardians.view',
            'classes.view',
            'curriculum.view',
            'assessments.view', 'assessments.create', 'assessments.update', 'assessments.grade',
            'attendance.view', 'attendance.mark', 'attendance.reports',
            'reports.view', 'reports.generate', 'reports.view_own',
            'communication.messages', 'communication.notifications',
            'timetable.view',
            'health.view',
        ]);

        // Parent/Guardian
        $parent = Role::firstOrCreate(['name' => 'parent']);
        $parent->givePermissionTo([
            'students.view_own',
            'guardians.view_own',
            'assessments.view_own',
            'attendance.view_own',
            'finance.view_own',
            'reports.view_own',
            'communication.messages', 'communication.notifications',
            'events.view',
            'health.view_own',
        ]);

        // Student
        $student = Role::firstOrCreate(['name' => 'student']);
        $student->givePermissionTo([
            'students.view_own',
            'assessments.view_own',
            'attendance.view_own',
            'reports.view_own',
            'communication.notifications',
            'timetable.view',
            'library.view',
            'events.view',
        ]);

        // Finance Officer
        $financeOfficer = Role::firstOrCreate(['name' => 'finance_officer']);
        $financeOfficer->givePermissionTo([
            'students.view',
            'guardians.view',
            'finance.view', 'finance.create', 'finance.update', 'finance.collect_payments', 'finance.reports',
            'reports.view', 'reports.generate', 'reports.export',
            'communication.messages', 'communication.notifications',
        ]);

        // Librarian
        $librarian = Role::firstOrCreate(['name' => 'librarian']);
        $librarian->givePermissionTo([
            'students.view',
            'teachers.view',
            'library.view', 'library.manage', 'library.issue_books',
            'communication.notifications',
        ]);

        // School Nurse
        $nurse = Role::firstOrCreate(['name' => 'nurse']);
        $nurse->givePermissionTo([
            'students.view',
            'health.view', 'health.manage',
            'communication.notifications',
        ]);

        // Auto-assign super_admin role to the first user if they have no role
        $firstUser = \App\Models\User::first();
        if ($firstUser && $firstUser->roles->isEmpty()) {
            $firstUser->assignRole('super_admin');
        }
    }
}
