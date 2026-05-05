<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;

class CatalogController extends Controller
{
    /**
     * Published courses visible on curriculum / homepage (no auth required).
     */
    public function courses()
    {
        $courses = Course::query()
            ->catalogVisible()
            ->with('instructor')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Course $course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'category' => $course->category,
                    'level' => $course->difficulty_level,
                    'duration' => $course->duration,
                    'modules_count' => $course->modules_count,
                    'modules' => $this->stubModules((int) $course->modules_count),
                    'instructor_name' => $course->instructor ? $course->instructor->name : null,
                ];
            });

        return response()->json(['courses' => $courses]);
    }

    /** Placeholder modules until course_modules table ships */
    private function stubModules(int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $out = [];
        for ($i = 1; $i <= min($count, 50); $i++) {
            $out[] = [
                'title' => 'Module ' . $i,
                'subtopics' => [],
            ];
        }

        return $out;
    }
}
