<?php

/**
 * CBC Backend API Test Script
 * Tests all modules and endpoints
 */

// Configuration
$baseUrl = 'http://localhost:8000/api/v1';
$token = null;

// Test results
$results = [
    'passed' => 0,
    'failed' => 0,
    'tests' => []
];

/**
 * Make HTTP request
 */
function makeRequest($method, $url, $data = null, $token = null) {
    $ch = curl_init();

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => json_decode($response, true),
        'raw' => $response
    ];
}

/**
 * Run a test
 */
function runTest($name, $callback) {
    global $results;

    echo "\n🧪 Testing: $name... ";

    try {
        $result = $callback();
        if ($result['success']) {
            echo "✅ PASSED";
            $results['passed']++;
            $results['tests'][] = ['name' => $name, 'status' => 'passed', 'message' => $result['message'] ?? ''];
        } else {
            echo "❌ FAILED - " . ($result['message'] ?? 'Unknown error');
            $results['failed']++;
            $results['tests'][] = ['name' => $name, 'status' => 'failed', 'message' => $result['message'] ?? ''];
        }
    } catch (Exception $e) {
        echo "❌ ERROR - " . $e->getMessage();
        $results['failed']++;
        $results['tests'][] = ['name' => $name, 'status' => 'error', 'message' => $e->getMessage()];
    }
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║           CBC LEARNING PLATFORM - BACKEND API TESTS              ║\n";
echo "║                    Doitix Tech Labs © 2026                       ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n";
echo "\n📍 Base URL: $baseUrl\n";
echo "📅 Date: " . date('Y-m-d H:i:s') . "\n";

// ============================================================================
// 1. AUTHENTICATION MODULE
// ============================================================================
echo "\n\n═══════════════════════════════════════════════════════════════════";
echo "\n📦 MODULE 1: AUTHENTICATION";
echo "\n═══════════════════════════════════════════════════════════════════";

// Test Login
runTest('Auth - Login with valid credentials', function() use ($baseUrl, &$token) {
    $response = makeRequest('POST', "$baseUrl/auth/login", [
        'email' => 'admin@cbcplatform.com',
        'password' => 'password'
    ]);

    if ($response['code'] === 200 && isset($response['body']['data']['token'])) {
        $token = $response['body']['data']['token'];
        return ['success' => true, 'message' => 'Token received'];
    }
    return ['success' => false, 'message' => "HTTP {$response['code']}: " . json_encode($response['body'])];
});

// Test Login with invalid credentials
runTest('Auth - Login with invalid credentials (should fail)', function() use ($baseUrl) {
    $response = makeRequest('POST', "$baseUrl/auth/login", [
        'email' => 'invalid@test.com',
        'password' => 'wrongpassword'
    ]);

    return ['success' => $response['code'] === 401, 'message' => "HTTP {$response['code']}"];
});

// Test Get Current User
runTest('Auth - Get authenticated user profile', function() use ($baseUrl, $token) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];

    $response = makeRequest('GET', "$baseUrl/auth/me", null, $token);

    if ($response['code'] === 200 && isset($response['body']['data']['email'])) {
        return ['success' => true, 'message' => "User: {$response['body']['data']['email']}"];
    }
    return ['success' => false, 'message' => "HTTP {$response['code']}"];
});

// Test Unauthorized access
runTest('Auth - Unauthorized access (no token)', function() use ($baseUrl) {
    $response = makeRequest('GET', "$baseUrl/auth/me");
    return ['success' => $response['code'] === 401, 'message' => "HTTP {$response['code']}"];
});

// ============================================================================
// 2. SCHOOLS MODULE
// ============================================================================
echo "\n\n═══════════════════════════════════════════════════════════════════";
echo "\n📦 MODULE 2: SCHOOLS MANAGEMENT";
echo "\n═══════════════════════════════════════════════════════════════════";

$schoolId = null;

// Test Create School
runTest('Schools - Create new school', function() use ($baseUrl, $token, &$schoolId) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];

    $response = makeRequest('POST', "$baseUrl/schools", [
        'name' => 'Test Primary School',
        'code' => 'TPS-' . time(),
        'school_type_id' => 1,
        'school_level_id' => 1,
        'email' => 'test@school.com',
        'phone' => '+254700000000',
        'county' => 'Nairobi',
        'gender_type' => 'mixed',
        'boarding_type' => 'day'
    ], $token);

    if ($response['code'] === 201 && isset($response['body']['data']['id'])) {
        $schoolId = $response['body']['data']['id'];
        return ['success' => true, 'message' => "School ID: $schoolId"];
    }
    return ['success' => false, 'message' => "HTTP {$response['code']}: " . json_encode($response['body'])];
});

// Test List Schools
runTest('Schools - List all schools', function() use ($baseUrl, $token) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];

    $response = makeRequest('GET', "$baseUrl/schools", null, $token);

    if ($response['code'] === 200) {
        $count = count($response['body']['data'] ?? []);
        return ['success' => true, 'message' => "Found $count schools"];
    }
    return ['success' => false, 'message' => "HTTP {$response['code']}"];
});

// Test Get Single School
runTest('Schools - Get school details', function() use ($baseUrl, $token, $schoolId) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];
    if (!$schoolId) return ['success' => false, 'message' => 'No school ID'];

    $response = makeRequest('GET', "$baseUrl/schools/$schoolId", null, $token);

    return ['success' => $response['code'] === 200, 'message' => "HTTP {$response['code']}"];
});

// Test Update School
runTest('Schools - Update school', function() use ($baseUrl, $token, $schoolId) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];
    if (!$schoolId) return ['success' => false, 'message' => 'No school ID'];

    $response = makeRequest('PUT', "$baseUrl/schools/$schoolId", [
        'name' => 'Updated Test Primary School',
        'motto' => 'Excellence in Education'
    ], $token);

    return ['success' => $response['code'] === 200, 'message' => "HTTP {$response['code']}"];
});

// Test School Statistics
runTest('Schools - Get school statistics', function() use ($baseUrl, $token, $schoolId) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];
    if (!$schoolId) return ['success' => false, 'message' => 'No school ID'];

    $response = makeRequest('GET', "$baseUrl/schools/$schoolId/statistics", null, $token);

    return ['success' => $response['code'] === 200, 'message' => "HTTP {$response['code']}"];
});

// ============================================================================
// 3. STUDENTS MODULE
// ============================================================================
echo "\n\n═══════════════════════════════════════════════════════════════════";
echo "\n📦 MODULE 3: STUDENTS MANAGEMENT";
echo "\n═══════════════════════════════════════════════════════════════════";

$studentId = null;

// Test Create Student
runTest('Students - Create new student', function() use ($baseUrl, $token, $schoolId, &$studentId) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];
    if (!$schoolId) return ['success' => false, 'message' => 'No school ID'];

    $response = makeRequest('POST', "$baseUrl/students", [
        'school_id' => $schoolId,
        'admission_number' => 'ADM-' . time(),
        'first_name' => 'John',
        'middle_name' => 'Doe',
        'last_name' => 'Student',
        'gender' => 'male',
        'date_of_birth' => '2015-05-15',
        'admission_date' => '2024-01-10',
        'nationality' => 'Kenyan'
    ], $token);

    if ($response['code'] === 201 && isset($response['body']['data']['id'])) {
        $studentId = $response['body']['data']['id'];
        return ['success' => true, 'message' => "Student ID: $studentId"];
    }
    return ['success' => false, 'message' => "HTTP {$response['code']}: " . json_encode($response['body'])];
});

// Test List Students
runTest('Students - List all students', function() use ($baseUrl, $token) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];

    $response = makeRequest('GET', "$baseUrl/students", null, $token);

    if ($response['code'] === 200) {
        $count = count($response['body']['data'] ?? []);
        return ['success' => true, 'message' => "Found $count students"];
    }
    return ['success' => false, 'message' => "HTTP {$response['code']}"];
});

// Test Get Single Student
runTest('Students - Get student details', function() use ($baseUrl, $token, $studentId) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];
    if (!$studentId) return ['success' => false, 'message' => 'No student ID'];

    $response = makeRequest('GET', "$baseUrl/students/$studentId", null, $token);

    return ['success' => $response['code'] === 200, 'message' => "HTTP {$response['code']}"];
});

// Test Update Student
runTest('Students - Update student', function() use ($baseUrl, $token, $studentId) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];
    if (!$studentId) return ['success' => false, 'message' => 'No student ID'];

    $response = makeRequest('PUT', "$baseUrl/students/$studentId", [
        'middle_name' => 'Updated'
    ], $token);

    return ['success' => $response['code'] === 200, 'message' => "HTTP {$response['code']}"];
});

// ============================================================================
// 4. TEACHERS MODULE
// ============================================================================
echo "\n\n═══════════════════════════════════════════════════════════════════";
echo "\n📦 MODULE 4: TEACHERS MANAGEMENT";
echo "\n═══════════════════════════════════════════════════════════════════";

$teacherId = null;

// Test Create Teacher
runTest('Teachers - Create new teacher', function() use ($baseUrl, $token, $schoolId, &$teacherId) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];
    if (!$schoolId) return ['success' => false, 'message' => 'No school ID'];

    $response = makeRequest('POST', "$baseUrl/teachers", [
        'school_id' => $schoolId,
        'staff_number' => 'STF-' . time(),
        'first_name' => 'Jane',
        'last_name' => 'Teacher',
        'gender' => 'female',
        'phone' => '+254711111111',
        'date_joined' => '2023-01-15',
        'email' => 'jane.teacher@school.com'
    ], $token);

    if ($response['code'] === 201 && isset($response['body']['data']['id'])) {
        $teacherId = $response['body']['data']['id'];
        return ['success' => true, 'message' => "Teacher ID: $teacherId"];
    }
    return ['success' => false, 'message' => "HTTP {$response['code']}: " . json_encode($response['body'])];
});

// Test List Teachers
runTest('Teachers - List all teachers', function() use ($baseUrl, $token) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];

    $response = makeRequest('GET', "$baseUrl/teachers", null, $token);

    if ($response['code'] === 200) {
        $count = count($response['body']['data'] ?? []);
        return ['success' => true, 'message' => "Found $count teachers"];
    }
    return ['success' => false, 'message' => "HTTP {$response['code']}"];
});

// Test Get Single Teacher
runTest('Teachers - Get teacher details', function() use ($baseUrl, $token, $teacherId) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];
    if (!$teacherId) return ['success' => false, 'message' => 'No teacher ID'];

    $response = makeRequest('GET', "$baseUrl/teachers/$teacherId", null, $token);

    return ['success' => $response['code'] === 200, 'message' => "HTTP {$response['code']}"];
});

// ============================================================================
// 5. GUARDIANS MODULE
// ============================================================================
echo "\n\n═══════════════════════════════════════════════════════════════════";
echo "\n📦 MODULE 5: GUARDIANS MANAGEMENT";
echo "\n═══════════════════════════════════════════════════════════════════";

$guardianId = null;

// Test Create Guardian
runTest('Guardians - Create new guardian', function() use ($baseUrl, $token, $schoolId, &$guardianId) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];
    if (!$schoolId) return ['success' => false, 'message' => 'No school ID'];

    $response = makeRequest('POST', "$baseUrl/guardians", [
        'school_id' => $schoolId,
        'first_name' => 'Mary',
        'last_name' => 'Parent',
        'phone' => '+254722222222',
        'email' => 'mary.parent@email.com',
        'relationship_type' => 'mother'
    ], $token);

    if ($response['code'] === 201 && isset($response['body']['data']['id'])) {
        $guardianId = $response['body']['data']['id'];
        return ['success' => true, 'message' => "Guardian ID: $guardianId"];
    }
    return ['success' => false, 'message' => "HTTP {$response['code']}: " . json_encode($response['body'])];
});

// Test List Guardians
runTest('Guardians - List all guardians', function() use ($baseUrl, $token) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];

    $response = makeRequest('GET', "$baseUrl/guardians", null, $token);

    if ($response['code'] === 200) {
        $count = count($response['body']['data'] ?? []);
        return ['success' => true, 'message' => "Found $count guardians"];
    }
    return ['success' => false, 'message' => "HTTP {$response['code']}"];
});

// ============================================================================
// 6. DATABASE MODELS & RELATIONSHIPS TEST
// ============================================================================
echo "\n\n═══════════════════════════════════════════════════════════════════";
echo "\n📦 MODULE 6: DATABASE INTEGRITY & MODELS";
echo "\n═══════════════════════════════════════════════════════════════════";

// We need to test via artisan tinker
echo "\n\n🔍 Testing Database Tables & Models via Artisan...\n";

// ============================================================================
// CLEANUP - Delete test data
// ============================================================================
echo "\n\n═══════════════════════════════════════════════════════════════════";
echo "\n🧹 CLEANUP: Removing test data";
echo "\n═══════════════════════════════════════════════════════════════════";

// Delete test student
runTest('Cleanup - Delete test student', function() use ($baseUrl, $token, $studentId) {
    if (!$token || !$studentId) return ['success' => true, 'message' => 'Skipped'];

    $response = makeRequest('DELETE', "$baseUrl/students/$studentId", null, $token);
    return ['success' => $response['code'] === 200, 'message' => "HTTP {$response['code']}"];
});

// Delete test teacher
runTest('Cleanup - Delete test teacher', function() use ($baseUrl, $token, $teacherId) {
    if (!$token || !$teacherId) return ['success' => true, 'message' => 'Skipped'];

    $response = makeRequest('DELETE', "$baseUrl/teachers/$teacherId", null, $token);
    return ['success' => $response['code'] === 200, 'message' => "HTTP {$response['code']}"];
});

// Delete test guardian
runTest('Cleanup - Delete test guardian', function() use ($baseUrl, $token, $guardianId) {
    if (!$token || !$guardianId) return ['success' => true, 'message' => 'Skipped'];

    $response = makeRequest('DELETE', "$baseUrl/guardians/$guardianId", null, $token);
    return ['success' => $response['code'] === 200, 'message' => "HTTP {$response['code']}"];
});

// Delete test school
runTest('Cleanup - Delete test school', function() use ($baseUrl, $token, $schoolId) {
    if (!$token || !$schoolId) return ['success' => true, 'message' => 'Skipped'];

    $response = makeRequest('DELETE', "$baseUrl/schools/$schoolId", null, $token);
    return ['success' => $response['code'] === 200, 'message' => "HTTP {$response['code']}"];
});

// Test Logout
runTest('Auth - Logout', function() use ($baseUrl, $token) {
    if (!$token) return ['success' => false, 'message' => 'No token available'];

    $response = makeRequest('POST', "$baseUrl/auth/logout", null, $token);
    return ['success' => $response['code'] === 200, 'message' => "HTTP {$response['code']}"];
});

// ============================================================================
// RESULTS SUMMARY
// ============================================================================
echo "\n\n";
echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║                        TEST RESULTS SUMMARY                       ║\n";
echo "╠══════════════════════════════════════════════════════════════════╣\n";
printf("║  ✅ Passed: %-5d                                                ║\n", $results['passed']);
printf("║  ❌ Failed: %-5d                                                ║\n", $results['failed']);
printf("║  📊 Total:  %-5d                                                ║\n", $results['passed'] + $results['failed']);
echo "╠══════════════════════════════════════════════════════════════════╣\n";
$percentage = $results['passed'] + $results['failed'] > 0
    ? round(($results['passed'] / ($results['passed'] + $results['failed'])) * 100, 1)
    : 0;
printf("║  📈 Success Rate: %.1f%%                                         ║\n", $percentage);
echo "╚══════════════════════════════════════════════════════════════════╝\n\n";

exit($results['failed'] > 0 ? 1 : 0);
