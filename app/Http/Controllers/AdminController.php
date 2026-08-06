<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\FacultyCode;
use App\Models\User;
use App\Services\UserCodeService;
use App\Support\UserPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * AdminController — the whole Admin_* surface (dashboard, profile, user
 * management, courses & badges, analytics report) fed from the database.
 */
class AdminController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────────────

    public function dashboard()
    {
        $stats = [
            'total_students'    => User::where('role_id', User::ROLE_STUDENT)->count(),
            'badges_issued'     => DB::table('user_badges')->count(),
            'course_score_avg'  => round((float) Course::avg('passing_score'), 1),
            'students_enrolled' => DB::table('enrollments')->count(),
        ];

        [$enrollmentByCourse, $completionRate] = $this->courseCharts();

        $activeCourses = Course::query()
            ->withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->limit(4)
            ->get()
            ->map(fn (Course $c) => (object) [
                'title'         => $c->title,
                'meta'          => $c->enrollments_count . ' Students · 1 Faculty',
                'thumbnail_url' => $c->thumbnail_url,
                'percent'       => (int) round((float) DB::table('enrollments')->where('course_id', $c->id)->avg('progress_percent')),
            ])
            ->values();

        // All badges are listed — even ones nobody has earned yet — so the
        // panel shows "0 Earned" counters that climb in real time.
        $recentBadges = DB::table('badges')
            ->leftJoin('user_badges', 'user_badges.badge_id', '=', 'badges.id')
            ->select('badges.name', DB::raw('COUNT(user_badges.id) as earned_count'))
            ->groupBy('badges.id', 'badges.name')
            ->orderByDesc('earned_count')
            ->orderBy('badges.name')
            ->limit(4)
            ->get()
            ->map(fn ($row) => (object) ['name' => $row->name, 'earned_count' => (int) $row->earned_count])
            ->values();

        return view('Admin_Main_Home', [
            'stats'              => $stats,
            'activeCourses'      => $activeCourses,
            'recentBadges'       => $recentBadges,
            'enrollmentByCourse' => $enrollmentByCourse,
            'completionRate'     => $completionRate,
        ]);
    }

    // ── Profile ───────────────────────────────────────────────────────────

    public function profile()
    {
        return view('Admin_Profile', [
            'user' => UserPresenter::admin(Auth::user()),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $auth = Auth::user();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:120', 'unique:users,email,' . $auth->id],
            'phone'    => ['nullable', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:120'],
            'role'     => ['nullable', 'string', 'max:80'],
            'about'    => ['nullable', 'string', 'max:600'],
            'bio'      => ['nullable', 'string', 'max:600'],
        ]);

        $parts            = preg_split('/\s+/', trim($data['name']), 2);
        $auth->first_name = $parts[0];
        $auth->last_name  = $parts[1] ?? $auth->last_name;
        $auth->email      = $data['email'];
        $auth->phone      = $data['phone'] ?? null;
        $auth->location   = $data['location'] ?? null;
        $auth->about      = $data['about'] ?? null;
        $auth->bio        = $data['bio'] ?? null;

        if (! empty($data['role'])) {
            $auth->role_label = $data['role'];
        }

        $auth->save();

        return redirect()->route('admin.profile')->with('success', 'Admin profile updated successfully.');
    }

    // ── User Management ───────────────────────────────────────────────────

    public function userManagement(Request $request)
    {
        $query     = trim((string) $request->input('q', ''));
        $sort      = $request->input('sort', 'created_at');
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $usersQuery = User::query()
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('username', 'like', "%{$query}%")
                        ->orWhere('user_code', 'like', "%{$query}%");
                });
            });

        if ($sort === 'user_code') {
            $usersQuery->orderBy('user_code', $direction);
        } elseif ($sort === 'role') {
            $usersQuery->orderBy('role_id', $direction);
        } elseif ($sort === 'name') {
            $usersQuery->orderBy('first_name', $direction)->orderBy('last_name', $direction);
        } else {
            $usersQuery->orderBy('created_at', $direction);
        }

        return view('Admin_Management_UserManage', [
            'users'     => $usersQuery->get(),
            'q'         => $query,
            'sort'      => $sort,
            'direction' => $direction,
        ]);
    }

    public function storeUser(Request $request, UserCodeService $userCodes)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username'   => ['required', 'string', 'max:255', 'unique:users'],
            'password'   => ['required', 'confirmed', Password::defaults()],
            'role_id'    => ['required', 'integer', 'in:1,2,3'],
        ]);

        $generatedCode = $userCodes->generateForRole((int) $validated['role_id']);

        User::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'username'   => $validated['username'],
            'password'   => Hash::make($validated['password']),
            'role_id'    => (int) $validated['role_id'],
            'student_id' => $generatedCode,
            'user_code'  => $generatedCode,
            'is_active'  => true,
        ]);

        return redirect()->route('admin.usermanagement')->with('success', 'User created successfully.');
    }

    // ── Faculty Codes ─────────────────────────────────────────────────────

    /**
     * Admin › Faculty Codes — lists every shareable faculty registration
     * code with a green (available) / red (used) indicator.
     */
    public function facultyCodes()
    {
        $codes = FacultyCode::with('usedBy')
            ->latest()
            ->get()
            ->map(fn (FacultyCode $c) => (object) [
                'id'           => $c->id,
                'code'         => $c->code,
                'is_used'      => $c->isUsed(),
                'used_by_name' => $c->usedBy?->name,
                'used_at'      => $c->used_at,
                'created_at'   => $c->created_at,
            ])
            ->values();

        return view('Admin_Faculty_Codes', compact('codes'));
    }

    /**
     * Generate a new shareable faculty code.
     */
    public function generateFacultyCode()
    {
        $code = FacultyCode::create([
            'code'       => FacultyCode::generateCode(),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.facultycodes')
            ->with('success', 'New faculty code generated: ' . $code->code);
    }

    /**
     * Delete an UNUSED code (used codes are kept as an audit trail).
     */
    public function deleteFacultyCode(int $id)
    {
        $code = FacultyCode::findOrFail($id);

        if ($code->isUsed()) {
            return redirect()->route('admin.facultycodes')
                ->with('success', 'Code ' . $code->code . ' has already been used and cannot be deleted.');
        }

        $code->delete();

        return redirect()->route('admin.facultycodes')
            ->with('success', 'Faculty code ' . $code->code . ' deleted.');
    }

    // ── Courses & Badges ──────────────────────────────────────────────────

    public function courses()
    {
        // Pending submissions first so the admin always sees what needs
        // review, then everything else alphabetically.
        $courses = Course::query()
            ->withCount('enrollments')
            ->with(['badge', 'creator'])
            ->orderByRaw("CASE WHEN approval_status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('title')
            ->get()
            ->map(fn (Course $c) => (object) [
                'id'         => $c->id,
                'title'      => $c->title,
                'students'   => (int) $c->enrollments_count,
                'faculty'    => 1,
                'badge'      => $c->badge->name ?? '—',
                'instructor' => $c->creator->name ?? $c->instructor,
                'status'     => $c->statusLabel(),
                'status_key' => $this->statusKey($c),
                'percent'    => (int) round((float) DB::table('enrollments')->where('course_id', $c->id)->avg('progress_percent')),
                'is_published' => (bool) $c->is_published,
                'is_featured'  => (bool) $c->is_featured,
            ])
            ->values();

        return view('Admin_Management_Course_&_Badges', compact('courses'));
    }

    /**
     * Admin › Course Detail — full information about a single course,
     * reached by clicking its card on the Courses & Badges page.
     */
    public function showCourse(int $id)
    {
        $c = Course::with(['badge', 'creator', 'modules.lessons', 'modules.quiz.questions'])
            ->withCount('enrollments')
            ->findOrFail($id);

        $course = (object) [
            'id'            => $c->id,
            'title'         => $c->title,
            'description'   => $c->description,
            'category'      => $c->category,
            'program'       => $c->program,
            'term'          => $c->term,
            'level'         => $c->level,
            'duration'      => $c->duration,
            'instructor'    => $c->creator->name ?? $c->instructor,
            'skills'        => $c->skills ?? [],
            'objectives'    => $c->objectives ?? [],
            'badge'         => $c->badge->name ?? '—',
            'status'        => $c->statusLabel(),
            'status_key'    => $this->statusKey($c),
            'students'      => (int) $c->enrollments_count,
            'faculty'       => 1,
            'percent'       => (int) round((float) DB::table('enrollments')->where('course_id', $c->id)->avg('progress_percent')),
            'modules_count' => $c->modules->count(),
            'lessons_count' => $c->modules->sum(fn ($m) => $m->lessons->count()),
            'thumbnail_url' => $c->thumbnail_url,
            'created_at'    => $c->created_at,
            'approved_at'   => $c->approved_at,
        ];

        $modules = $c->modules->map(fn ($m) => (object) [
            'title'       => $m->title,
            'description' => $m->description,
            'lessons'     => $m->lessons->map(fn ($l) => (object) [
                'title'       => $l->title,
                'type'        => $l->type,
                'duration'    => $l->duration,
                'description' => $l->description,
                'file_url'    => ! empty($l->file_url) ? asset($l->file_url) : null,
                'file_name'   => ! empty($l->file_url) ? basename($l->file_url) : null,
            ])->values(),
            'quiz' => $m->quiz ? (object) [
                'title'           => $m->quiz->title,
                'questions_count' => $m->quiz->questions->count(),
                'passing_score'   => $m->quiz->passing_score,
                'time_limit'      => $m->quiz->time_limit,
                'instructions'    => $m->quiz->instructions ?? '',
                'questions'       => $m->quiz->questions->map(fn ($q) => (object) [
                    'question'       => $q->question,
                    'type'           => $q->type ?? 'Multiple Choice',
                    'points'         => (int) $q->points,
                    'options'        => $q->options ?? [],
                    'correct_answer' => $q->correct_answer,
                ])->values(),
            ] : null,
        ])->values();

        return view('Admin_Course_Detail', compact('course', 'modules'));
    }

    /**
     * Approve a pending course → publishes it for students to browse
     * and enroll in.
     */
    public function approveCourse(int $id)
    {
        $course = Course::findOrFail($id);

        $course->approval_status = 'approved';
        $course->is_approved     = true;
        $course->is_published    = true;
        $course->approved_by     = Auth::id();
        $course->approved_at     = now();
        $course->save();

        return redirect()->route('admin.courses')
            ->with('success', '"' . $course->title . '" has been approved and is now published.');
    }

    /**
     * Deny a pending course → it stays hidden from students.
     */
    public function denyCourse(int $id)
    {
        $course = Course::findOrFail($id);

        $course->approval_status = 'denied';
        $course->is_approved     = false;
        $course->is_published    = false;
        $course->approved_by     = Auth::id();
        $course->approved_at     = now();
        $course->save();

        return redirect()->route('admin.courses')
            ->with('success', '"' . $course->title . '" has been denied.');
    }

    /**
     * Publish / unpublish a course — admin-only. Unpublished courses are
     * hidden from students even if they were previously approved.
     */
    public function togglePublishCourse(int $id)
    {
        $course = Course::findOrFail($id);

        $course->is_published = ! $course->is_published;
        if ($course->is_published) {
            // Publishing implies approval.
            $course->approval_status = 'approved';
            $course->is_approved     = true;
            $course->approved_by     = $course->approved_by ?? Auth::id();
            $course->approved_at     = $course->approved_at ?? now();
        }
        $course->save();

        return back()->with('success', '"' . $course->title . '" is now '
            . ($course->is_published ? 'published.' : 'unpublished — hidden from students.'));
    }

    /**
     * Feature / unfeature a course on the homepage — admin-only.
     */
    public function toggleFeatureCourse(int $id)
    {
        $course = Course::findOrFail($id);

        $course->is_featured = ! $course->is_featured;
        $course->save();

        return back()->with('success', '"' . $course->title . '" '
            . ($course->is_featured ? 'will be featured on the homepage.' : 'was removed from the homepage features.'));
    }

    /**
     * Permanently deletes a course and everything attached to it
     * (modules, lessons, quizzes, enrollments cascade via FK).
     */
    public function destroyCourse(int $id)
    {
        $course = Course::findOrFail($id);
        $title  = $course->title;

        $course->delete();

        return redirect()->route('admin.courses')
            ->with('success', '"' . $title . '" has been deleted.');
    }

    /**
     * Lowercase status key used for the coloured pills
     * (pending / approved / denied / draft).
     */
    private function statusKey(Course $c): string
    {
        if ($c->approval_status === 'approved' || $c->is_approved) {
            return 'approved';
        }

        return in_array($c->approval_status, ['pending', 'denied', 'draft'], true)
            ? $c->approval_status
            : 'pending';
    }

    // ── Analytics Report ──────────────────────────────────────────────────

    public function report()
    {
        $stats = [
            'total_students'   => User::where('role_id', User::ROLE_STUDENT)->count(),
            'badges_issued'    => DB::table('user_badges')->count(),
            'faculty_total'    => User::where('role_id', User::ROLE_FACULTY)->count(),
            'course_score_avg' => round((float) Course::avg('passing_score'), 2),
        ];

        [$enrollmentByCourse, $completionRate] = $this->courseCharts();

        return view('Admin_Analytics_Report', [
            'stats'              => $stats,
            'enrollmentByCourse' => $enrollmentByCourse,
            'completionRate'     => $completionRate,
        ]);
    }

    // ── Shared chart data ─────────────────────────────────────────────────

    /**
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection}
     */
    private function courseCharts(): array
    {
        $rows = DB::table('enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->select(
                'courses.title',
                DB::raw('COUNT(*) as learners'),
                DB::raw('AVG(enrollments.progress_percent) as avg_progress')
            )
            ->groupBy('courses.id', 'courses.title')
            ->orderByDesc('learners')
            ->limit(4)
            ->get();

        $max = max(1, (int) $rows->max('learners'));

        $enrollmentByCourse = $rows->map(fn ($row) => (object) [
            'label'   => \Illuminate\Support\Str::limit($row->title, 14, ''),
            'value'   => (int) $row->learners,
            'percent' => (int) round($row->learners / $max * 100),
        ])->values();

        $completionRate = $rows->map(fn ($row) => (object) [
            'label'   => \Illuminate\Support\Str::limit($row->title, 14, ''),
            'value'   => (int) round($row->avg_progress),
            'percent' => (int) round($row->avg_progress),
        ])->values();

        return [$enrollmentByCourse, $completionRate];
    }
}
