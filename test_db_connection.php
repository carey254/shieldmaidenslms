<?php
require 'theshieldmaidens-lms-backend/vendor/autoload.php';

$app = require_once 'theshieldmaidens-lms-backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DATABASE CONNECTION TEST ===\n";
echo "Database: " . DB::connection()->getDatabaseName() . "\n";
echo "Connection: " . DB::connection()->getDriverName() . "\n\n";

echo "=== USERS TABLE ===\n";
$users = DB::table('users')->select('id', 'name', 'email', 'role', 'status')->get();
foreach($users as $user) {
    echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}, Status: {$user->status}\n";
}

echo "\n=== ENROLLMENTS TABLE ===\n";
$enrollments = DB::table('enrollments')->get();
echo "Total enrollments: " . $enrollments->count() . "\n";
foreach($enrollments as $enrollment) {
    echo "User ID: {$enrollment->user_id}, Course ID: {$enrollment->course_id}, Progress: {$enrollment->progress}%\n";
}

echo "\n=== COURSES TABLE ===\n";
$courses = DB::table('courses')->get();
echo "Total courses: " . $courses->count() . "\n";
foreach($courses as $course) {
    echo "Course ID: {$course->id}, Title: {$course->title}, Status: {$course->status}\n";
}

echo "\n=== AUTHENTICATION TEST ===\n";
$student = DB::table('users')->where('role', 'student')->first();
if ($student) {
    echo "Found student: {$student->name} ({$student->email})\n";
    
    // Test student enrollment
    $studentEnrollments = DB::table('enrollments')->where('user_id', $student->id)->get();
    echo "Student enrollments: " . $studentEnrollments->count() . "\n";
}

$admin = DB::table('users')->where('role', 'admin')->first();
if ($admin) {
    echo "Found admin: {$admin->name} ({$admin->email})\n";
}

$facilitator = DB::table('users')->where('role', 'instructor')->first();
if ($facilitator) {
    echo "Found facilitator: {$facilitator->name} ({$facilitator->email})\n";
}
