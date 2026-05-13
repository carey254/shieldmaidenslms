<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Course;
use App\Models\Opportunity;

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
                    'duration_hours' => $course->duration_hours,
                    'modules_count' => $course->modules_count,
                    'modules' => $this->stubModules((int) $course->modules_count),
                    'instructor_name' => $course->instructor ? $course->instructor->name : null,
                    'instructor_id' => $course->instructor_id,
                    'price' => $course->price,
                    'max_students' => $course->max_students,
                    'enrolled_count' => $course->enrolled_count,
                    'start_date' => $course->start_date?->format('Y-m-d'),
                    'end_date' => $course->end_date?->format('Y-m-d'),
                    'status' => $course->status,
                    'visibility' => $course->visibility,
                    'thumbnail' => $course->thumbnail,
                    'created_at' => $course->created_at->toISOString(),
                ];
            });

        return response()->json(['courses' => $courses]);
    }

    /**
     * Public home feed: announcements and opportunities visible to everyone (no auth).
     */
    public function homeFeed()
    {
        $announcements = Announcement::query()
            ->active()
            ->where('show_on_home', true)
            ->whereIn('audience', ['all', 'students'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get()
            ->map(fn (Announcement $a) => $a->toPortalArray());

        $opportunities = Opportunity::query()
            ->active()
            ->where('deadline', '>', now())
            ->whereIn('visibility', ['all', 'students'])
            ->orderBy('deadline')
            ->limit(8)
            ->get()
            ->map(function (Opportunity $o) {
                return [
                    'id' => $o->id,
                    'title' => $o->title,
                    'description' => $o->description,
                    'type' => $o->type,
                    'organization' => $o->organization,
                    'location' => $o->location,
                    'deadline' => $o->deadline->format('Y-m-d'),
                    'application_link' => $o->application_link,
                    'contact_email' => $o->contact_email,
                    'created_at' => $o->created_at->toISOString(),
                ];
            });

        return response()->json([
            'announcements' => $announcements,
            'opportunities' => $opportunities,
        ]);
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
