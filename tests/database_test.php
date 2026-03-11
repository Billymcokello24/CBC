<?php

/**
 * CBC Backend - Database & Models Integrity Test
 * Tests all database tables and model relationships
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║     CBC LEARNING PLATFORM - DATABASE & MODELS INTEGRITY TEST     ║\n";
echo "║                    Doitix Tech Labs © 2026                       ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n";
echo "\n📅 Date: " . date('Y-m-d H:i:s') . "\n\n";

$passed = 0;
$failed = 0;

// ============================================================================
// 1. DATABASE CONNECTION TEST
// ============================================================================
echo "═══════════════════════════════════════════════════════════════════\n";
echo "📦 1. DATABASE CONNECTION\n";
echo "═══════════════════════════════════════════════════════════════════\n";

try {
    DB::connection()->getPdo();
    echo "✅ Database connection: OK\n";
    echo "   Driver: " . DB::connection()->getDriverName() . "\n";
    echo "   Database: " . DB::connection()->getDatabaseName() . "\n";
    $passed++;
} catch (Exception $e) {
    echo "❌ Database connection: FAILED - " . $e->getMessage() . "\n";
    $failed++;
}

// ============================================================================
// 2. TABLE COUNT TEST
// ============================================================================
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "📦 2. DATABASE TABLES\n";
echo "═══════════════════════════════════════════════════════════════════\n";

$tables = DB::select('SHOW TABLES');
$tableCount = count($tables);
echo "✅ Total tables created: $tableCount\n";

$expectedMinTables = 100; // We expect at least 100 tables
if ($tableCount >= $expectedMinTables) {
    echo "✅ Table count check: PASSED (expected >= $expectedMinTables)\n";
    $passed++;
} else {
    echo "❌ Table count check: FAILED (expected >= $expectedMinTables, got $tableCount)\n";
    $failed++;
}

// ============================================================================
// 3. CORE MODELS TEST
// ============================================================================
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "📦 3. CORE MODELS & SEEDED DATA\n";
echo "═══════════════════════════════════════════════════════════════════\n";

$modelTests = [
    ['model' => \App\Models\User::class, 'name' => 'Users', 'min' => 1],
    ['model' => \Spatie\Permission\Models\Role::class, 'name' => 'Roles', 'min' => 10],
    ['model' => \Spatie\Permission\Models\Permission::class, 'name' => 'Permissions', 'min' => 50],
    ['model' => \App\Models\SchoolType::class, 'name' => 'School Types', 'min' => 3],
    ['model' => \App\Models\SchoolLevel::class, 'name' => 'School Levels', 'min' => 3],
    ['model' => \App\Models\Curriculum\LearningArea::class, 'name' => 'Learning Areas', 'min' => 5],
    ['model' => \App\Models\Curriculum\Subject::class, 'name' => 'Subjects', 'min' => 10],
    ['model' => \App\Models\Curriculum\Competency::class, 'name' => 'Competencies', 'min' => 5],
];

foreach ($modelTests as $test) {
    try {
        $count = $test['model']::count();
        if ($count >= $test['min']) {
            echo "✅ {$test['name']}: $count records (min: {$test['min']})\n";
            $passed++;
        } else {
            echo "❌ {$test['name']}: $count records (expected min: {$test['min']})\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "❌ {$test['name']}: ERROR - " . $e->getMessage() . "\n";
        $failed++;
    }
}

// ============================================================================
// 4. RBAC SYSTEM TEST
// ============================================================================
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "📦 4. RBAC (ROLE-BASED ACCESS CONTROL)\n";
echo "═══════════════════════════════════════════════════════════════════\n";

$requiredRoles = ['super_admin', 'school_admin', 'principal', 'teacher', 'parent', 'student'];
foreach ($requiredRoles as $roleName) {
    $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
    if ($role) {
        $permCount = $role->permissions->count();
        echo "✅ Role '$roleName': exists with $permCount permissions\n";
        $passed++;
    } else {
        echo "❌ Role '$roleName': NOT FOUND\n";
        $failed++;
    }
}

// Test admin user has role
$adminUser = \App\Models\User::where('email', 'admin@cbcplatform.com')->first();
if ($adminUser && $adminUser->hasRole('super_admin')) {
    echo "✅ Admin user has 'super_admin' role\n";
    $passed++;
} else {
    echo "❌ Admin user missing 'super_admin' role\n";
    $failed++;
}

// ============================================================================
// 5. API TOKEN SYSTEM TEST
// ============================================================================
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "📦 5. SANCTUM API TOKEN SYSTEM\n";
echo "═══════════════════════════════════════════════════════════════════\n";

if (Schema::hasTable('personal_access_tokens')) {
    echo "✅ Personal access tokens table: EXISTS\n";
    $passed++;

    // Test token creation
    try {
        $user = \App\Models\User::first();
        $token = $user->createToken('test-token');
        if ($token->plainTextToken) {
            echo "✅ Token creation: WORKING\n";
            $passed++;
            // Clean up test token
            $user->tokens()->where('name', 'test-token')->delete();
        }
    } catch (Exception $e) {
        echo "❌ Token creation: FAILED - " . $e->getMessage() . "\n";
        $failed++;
    }
} else {
    echo "❌ Personal access tokens table: MISSING\n";
    $failed++;
}

// ============================================================================
// 6. MODULE TABLES VERIFICATION
// ============================================================================
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "📦 6. MODULE TABLES VERIFICATION\n";
echo "═══════════════════════════════════════════════════════════════════\n";

$moduleTables = [
    'Schools' => ['schools', 'school_types', 'school_levels', 'school_branches', 'school_settings'],
    'Academic' => ['academic_years', 'academic_terms', 'grade_levels', 'streams', 'school_classes'],
    'Students' => ['students', 'guardians', 'student_guardians', 'student_enrollments'],
    'Teachers' => ['teachers', 'staff_categories', 'staff_designations', 'teacher_qualifications'],
    'Curriculum' => ['learning_areas', 'subjects', 'competencies', 'strands', 'sub_strands'],
    'Assessment' => ['assessments', 'assessment_types', 'rubrics', 'student_assessments', 'report_cards'],
    'Attendance' => ['student_attendances', 'attendance_sessions', 'attendance_summaries'],
    'Finance' => ['fee_structures', 'invoices', 'payments', 'payment_methods'],
    'Communication' => ['conversations', 'messages', 'announcements', 'notifications'],
    'LMS' => ['courses', 'modules', 'lessons', 'course_enrollments'],
    'Library' => ['books', 'book_copies', 'book_loans'],
    'Transport' => ['vehicles', 'routes', 'drivers'],
    'Hostel' => ['hostels', 'hostel_rooms', 'hostel_allocations'],
    'Health' => ['medical_conditions', 'medical_visits', 'immunization_types'],
    'Events' => ['events', 'clubs', 'extracurricular_activities'],
    'Career' => ['career_pathways', 'career_clusters', 'student_career_interests'],
    'Analytics' => ['report_types', 'metric_definitions', 'analytics_reports'],
    'Integrations' => ['api_keys', 'webhook_endpoints', 'sms_logs'],
];

foreach ($moduleTables as $module => $tables) {
    $existing = 0;
    $missing = [];
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            $existing++;
        } else {
            $missing[] = $table;
        }
    }

    if (empty($missing)) {
        echo "✅ $module module: All " . count($tables) . " tables exist\n";
        $passed++;
    } else {
        echo "⚠️  $module module: $existing/" . count($tables) . " tables (missing: " . implode(', ', $missing) . ")\n";
        $failed++;
    }
}

// ============================================================================
// 7. FOREIGN KEY RELATIONSHIPS TEST
// ============================================================================
echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "📦 7. FOREIGN KEY RELATIONSHIPS\n";
echo "═══════════════════════════════════════════════════════════════════\n";

// Test student-school relationship
try {
    $school = \App\Models\School::create([
        'name' => 'Test School',
        'code' => 'TEST-FK-' . time(),
        'school_type_id' => 1,
        'school_level_id' => 1,
        'email' => 'test-fk@school.com',
        'phone' => '+254700000000',
        'county' => 'Nairobi',
        'gender_type' => 'mixed',
        'boarding_type' => 'day',
    ]);

    $student = \App\Models\Student::create([
        'school_id' => $school->id,
        'admission_number' => 'ADM-FK-' . time(),
        'first_name' => 'Test',
        'last_name' => 'Student',
        'gender' => 'male',
        'date_of_birth' => '2015-01-01',
        'admission_date' => now(),
    ]);

    // Test relationship
    if ($student->school->id === $school->id) {
        echo "✅ Student-School relationship: WORKING\n";
        $passed++;
    } else {
        echo "❌ Student-School relationship: FAILED\n";
        $failed++;
    }

    // Cleanup
    $student->delete();
    $school->delete();

} catch (Exception $e) {
    echo "❌ Foreign key test: ERROR - " . $e->getMessage() . "\n";
    $failed++;
}

// ============================================================================
// RESULTS SUMMARY
// ============================================================================
echo "\n";
echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║                    DATABASE TEST RESULTS                         ║\n";
echo "╠══════════════════════════════════════════════════════════════════╣\n";
printf("║  ✅ Passed: %-5d                                                ║\n", $passed);
printf("║  ❌ Failed: %-5d                                                ║\n", $failed);
printf("║  📊 Total:  %-5d                                                ║\n", $passed + $failed);
echo "╠══════════════════════════════════════════════════════════════════╣\n";
$percentage = $passed + $failed > 0
    ? round(($passed / ($passed + $failed)) * 100, 1)
    : 0;
printf("║  📈 Success Rate: %.1f%%                                         ║\n", $percentage);
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

exit($failed > 0 ? 1 : 0);
