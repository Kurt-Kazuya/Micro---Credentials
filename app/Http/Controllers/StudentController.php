<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\UserBadge;
use App\Support\UserPresenter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * StudentController — every Student_* page, now fed entirely from the
 * database. Every method passes the same variable shapes the Blade views
 * were built against (plain objects / collections), so no view changes
 * were needed.
 */
class StudentController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────────────

    public function dashboard()
    {
        $auth = Auth::user();

        $enrollments = Enrollment::with('course')
            ->where('user_id', $auth->id)
            ->get();

        $courses = $enrollments->map(fn (Enrollment $e) => (object) [
            'id'               => $e->course_id,
            'title'            => $e->course->title ?? 'Course',
            'category'         => $e->course->category ?? null,
            'thumbnail_url'    => ! empty($e->course->thumbnail_url) ? asset($e->course->thumbnail_url) : null,
            'progress_percent' => (int) $e->progress_percent,
        ])->values();

        $badges = UserBadge::with('badge')
            ->where('user_id', $auth->id)
            ->latest('earned_at')
            ->get()
            ->map(fn (UserBadge $ub) => (object) ['name' => $ub->badge->name ?? 'Badge'])
            ->values();

        return view('Student_dashboard', [
            'user'    => UserPresenter::student($auth),
            'stats'   => [
                'active_courses' => $enrollments->where('is_completed', false)->count(),
                'completed'      => $enrollments->where('is_completed', true)->count(),
                'badges_earned'  => $badges->count(),
                'certificates'   => $auth->certificates()->count(),
            ],
            'courses'  => $courses,
            'progress' => [],
            'badges'   => $badges,
        ]);
    }

    // ── Browse Courses ────────────────────────────────────────────────────

    public function browse(Request $request)
    {
        $filters = [
            'q'        => trim((string) $request->input('q', '')) ?: null,
            'category' => $request->input('category') ?: null,
            'level'    => $request->input('level') ?: null,
        ];

        $query = Course::query()
            ->where('is_published', true)
            ->when($filters['q'], function ($q) use ($filters) {
                $term = '%' . $filters['q'] . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('category', 'like', $term)
                        ->orWhere('instructor', 'like', $term);
                });
            })
            ->when($filters['category'], fn ($q) => $q->where('category', $filters['category']))
            ->when($filters['level'], fn ($q) => $q->where('level', $filters['level']))
            ->orderByDesc('is_featured')
            ->orderBy('title');

        $courses = $query->get()->map(fn (Course $c) => (object) [
            'id'            => $c->id,
            'title'         => $c->title,
            'description'   => $c->description,
            'category'      => $c->category,
            'level'         => $c->level,
            'instructor'    => $c->instructor,
            'duration'      => $c->duration,
            'lessons_count' => (int) ($c->lessons_count ?: $c->lessons()->count()),
            'thumbnail_url' => $c->thumbnail_url ? asset($c->thumbnail_url) : null,
        ])->values();

        $categories = Course::query()->where('is_published', true)
            ->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all();
        $levels = Course::query()->where('is_published', true)
            ->whereNotNull('level')->distinct()->orderBy('level')->pluck('level')->all();

        return view('Student_browse_Courses', [
            'user'       => UserPresenter::student(Auth::user()),
            'courses'    => $courses,
            'categories' => $categories,
            'levels'     => $levels ?: ['Beginner', 'Intermediate', 'Advanced'],
            'filters'    => $filters,
        ]);
    }

    // ── Course Description ────────────────────────────────────────────────

    public function show(int $id)
    {
        $auth   = Auth::user();
        $course = Course::with(['modules.lessons', 'quizzes.questions', 'creator'])->findOrFail($id);

        $instructor = $course->creator;

        // The description page lists the course's lesson rows.
        $modules = $course->lessons->map(fn (CourseLesson $l) => (object) [
            'title'       => $l->title,
            'description' => $l->type . ': ' . trim((string) $l->duration),
            'type'        => $l->type,
            'duration'    => $l->duration,
        ])->values();

        $quizModel = $course->quizzes->first();
        $quiz = $quizModel ? (object) [
            'id'              => $quizModel->id,
            'title'           => $quizModel->title,
            'questions_count' => $quizModel->questions->count(),
            'passing_score'   => $quizModel->passing_score,
        ] : null;

        $enrollment = Enrollment::where('user_id', $auth->id)->where('course_id', $course->id)->first();

        return view('Student_Course_Description', [
            'user'   => UserPresenter::student($auth),
            'course' => (object) [
                'id'             => $course->id,
                'title'          => $course->title,
                'description'    => $course->description,
                'category'       => $course->category,
                'level'          => $course->level,
                'is_featured'    => (bool) $course->is_featured,
                'instructor'     => $course->instructor,
                'duration'       => $course->duration,
                'lessons_count'  => (int) ($course->lessons_count ?: $course->lessons->count()),
                'enrolled_count' => (int) ($course->enrolled_count ?: $course->enrollments()->count()),
                'thumbnail_url'  => $course->thumbnail_url ? asset($course->thumbnail_url) : null,
                'passing_score'  => (int) $course->passing_score,
                'skills'         => $course->skills ?? [],
                'objectives'     => $course->objectives ?? [],
            ],
            'instructor_detail' => (object) [
                'name'       => $instructor?->name ?? $course->instructor ?? 'Faculty',
                'department' => 'College of Information Technology',
                'bio'        => $instructor?->bio ?? 'Faculty Member of the UPSKILL platform.',
                'avatar_url' => $instructor?->avatar_url,
            ],
            'modules'          => $modules,
            'quiz'             => $quiz,
            'is_enrolled'      => (bool) $enrollment,
            'progress_percent' => (int) ($enrollment->progress_percent ?? 0),
        ]);
    }

    // ── Enrollment ────────────────────────────────────────────────────────

    public function enroll(int $id)
    {
        $auth   = Auth::user();
        $course = Course::findOrFail($id);

        Enrollment::firstOrCreate(
            ['user_id' => $auth->id, 'course_id' => $course->id],
            ['enrolled_at' => now(), 'progress_percent' => 0, 'is_completed' => false]
        );

        $course->enrolled_count = $course->enrollments()->count();
        $course->save();

        return redirect()->route('courses.learn', $course->id);
    }

    /**
     * Learning / enrollment screen (Student_Course_Enrollment).
     */
    public function learn(int $id)
    {
        $auth   = Auth::user();
        $course = Course::with(['modules.lessons', 'modules.quiz.questions'])->findOrFail($id);

        $modules = $course->modules->map(fn ($m) => (object) [
            'id'      => $m->id,
            'title'   => $m->title,
            'lessons' => $m->lessons->map(fn (CourseLesson $l) => (object) [
                'id'            => $l->id,
                'title'         => $l->title,
                'type'          => $l->type,
                'duration'      => $l->duration,
                'description'   => $l->content ?: $l->title,
                'thumbnail_url' => null,
                'file_url'      => $l->file_url ? asset($l->file_url) : null,
                'file_name'     => $l->file_name,
            ])->values(),
            'quiz' => $m->quiz ? (object) [
                'id'              => $m->quiz->id,
                'title'           => $m->quiz->title,
                'questions_count' => $m->quiz->questions->count(),
                'passing_score'   => $m->quiz->passing_score,
                'questions'       => $m->quiz->questions->map(function ($q) {
                    $opts = collect($q->options ?? [])->values()->map(fn ($text, $i) => [
                        'letter' => chr(65 + $i),
                        'text'   => (string) $text,
                    ])->all();
                    $correctLetter = null;
                    if ($q->correct_answer !== null) {
                        $pos = array_search($q->correct_answer, $q->options ?? [], true);
                        if ($pos !== false) {
                            $correctLetter = chr(65 + (int) $pos);
                        }
                    }

                    return [
                        'label'    => \Illuminate\Support\Str::limit((string) $q->question, 40, ''),
                        'question' => (string) $q->question,
                        'options'  => $opts,
                        'correct'  => $correctLetter,
                    ];
                })->values()->all(),
            ] : null,
        ])->values();

        $enrollment = Enrollment::where('user_id', $auth->id)->where('course_id', $course->id)->first();

        // Previously saved player state, so returning students pick up
        // exactly where they left off (completed lessons + quiz scores).
        $state = $enrollment?->progress_state ?? [];

        return view('Student_Course_Enrollment', [
            'user'   => UserPresenter::student($auth),
            'course' => (object) [
                'id'               => $course->id,
                'title'            => $course->title,
                'category'         => $course->category,
                'thumbnail_url'    => $course->thumbnail_url ? asset($course->thumbnail_url) : null,
                'progress_percent' => (int) ($enrollment->progress_percent ?? 0),
            ],
            'modules'          => $modules,
            'current_lesson'   => $modules->first()?->lessons->first(),
            'current_module'   => $modules->first(),
            'total_lessons'    => $modules->sum(fn ($m) => $m->lessons->count()),
            'badge_count'      => $auth->badges()->count(),
            'progress_percent' => (int) ($enrollment->progress_percent ?? 0),
            'saved_progress'   => [
                'completed_lessons' => array_values($state['completed_lessons'] ?? []),
                'module_scores'     => (object) ($state['module_scores'] ?? []),
            ],
        ]);
    }

    /**
     * Individual lesson deep-link — the learning screen handles playback,
     * so this stays a redirect (as in the original routes).
     */
    public function lesson(int $courseId, int $lessonId)
    {
        return redirect()->route('courses.learn', $courseId);
    }

    public function quiz(int $id)
    {
        return redirect()->route('dashboard');
    }

    // ── Real-time progress saving ───────────────────────────────────────

    /**
     * Called via fetch() from the course player every time the student
     * marks a lesson complete or submits a quiz. Persists the exact player
     * state (completed lessons + per-module quiz scores) and keeps
     * enrollments.progress_percent current, so the dashboard, browse and
     * course-description pages always show the real percentage.
     */
    public function saveProgress(int $id, Request $request)
    {
        $data = $request->validate([
            'percent'              => 'nullable|integer|min:0|max:100',
            'completed_lessons'    => 'nullable|array',
            'completed_lessons.*'  => 'string|max:20',
            'module_scores'        => 'nullable|array',
            'module_scores.*'      => 'integer|min:0',
        ]);

        $auth   = Auth::user();
        $course = Course::with('modules.lessons')->findOrFail($id);

        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $auth->id, 'course_id' => $course->id],
            ['enrolled_at' => now(), 'progress_percent' => 0, 'is_completed' => false]
        );

        $percent = (int) ($data['percent'] ?? 0);

        // Progress never moves backwards (e.g. after a quiz retake).
        $enrollment->progress_percent = max((int) $enrollment->progress_percent, $percent);
        $enrollment->progress_state   = [
            'completed_lessons' => array_values($data['completed_lessons'] ?? []),
            'module_scores'     => $data['module_scores'] ?? [],
        ];
        $enrollment->save();

        // Mirror lesson completions into the lesson_completions table.
        // Player keys are "<moduleIdx>-<lessonIdx>" following the same
        // module/lesson ordering the page was rendered with.
        foreach (($data['completed_lessons'] ?? []) as $lid) {
            if (! preg_match('/^(\d+)-(\d+)$/', $lid, $mm)) {
                continue;
            }
            $lesson = $course->modules->get((int) $mm[1])?->lessons->values()->get((int) $mm[2]);
            if (! $lesson) {
                continue;
            }
            DB::table('lesson_completions')->insertOrIgnore([
                'user_id'      => $auth->id,
                'lesson_id'    => $lesson->id,
                'completed_at' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        $badgeAwarded = null;
        if ($percent >= 100) {
            $badgeAwarded = $this->finalizeCompletion($auth, $course, $enrollment);
        }

        return response()->json([
            'ok'            => true,
            'percent'       => (int) $enrollment->progress_percent,
            'badge_awarded' => $badgeAwarded,
        ]);
    }

    // ── Course completion -> badge award ────────────────────────────────

    /**
     * Called via fetch() from the course player the moment a student
     * finishes the last module quiz. Marks the enrollment complete and
     * awards the badge linked to the course (courses.badge_id).
     *
     * The Admin "Recent Badges" panel counts rows in user_badges, so it
     * climbs in real time as soon as a student earns a badge. Awards are
     * idempotent: one badge per student per course, no matter how many
     * times this endpoint is hit.
     */
    public function completeCourse(int $id)
    {
        $auth       = Auth::user();
        $course     = Course::findOrFail($id);
        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $auth->id, 'course_id' => $course->id],
            ['enrolled_at' => now(), 'progress_percent' => 0]
        );

        return response()->json([
            'ok'            => true,
            'badge_awarded' => $this->finalizeCompletion($auth, $course, $enrollment),
        ]);
    }

    /**
     * Marks the enrollment complete and awards the course's linked badge
     * (courses.badge_id). Idempotent: one badge per student per course.
     * Returns the awarded badge name (only the first time), or null.
     */
    private function finalizeCompletion($auth, Course $course, Enrollment $enrollment): ?string
    {
        $enrollment->progress_percent = 100;
        $enrollment->is_completed     = true;
        $enrollment->save();

        $badgeAwarded = null;

        if ($course->badge_id) {
            $userBadge = UserBadge::firstOrCreate(
                ['user_id' => $auth->id, 'badge_id' => $course->badge_id],
                ['earned_at' => now()]
            );

            if ($userBadge->wasRecentlyCreated) {
                $badgeAwarded = optional($course->badge)->name;

                AnalyticsEvent::create([
                    'user_id'     => $auth->id,
                    'event_type'  => 'badge_issued',
                    'entity_type' => 'badge',
                    'entity_id'   => $course->badge_id,
                    'metadata'    => ['detail' => $auth->name . ' earned the ' . $badgeAwarded . ' badge'],
                    'occurred_at' => now(),
                ]);
            }
        }

        if (! $enrollment->wasRecentlyCreated && ! $enrollment->getOriginal('is_completed')) {
            AnalyticsEvent::create([
                'user_id'     => $auth->id,
                'event_type'  => 'course_completed',
                'entity_type' => 'course',
                'entity_id'   => $course->id,
                'metadata'    => ['detail' => $auth->name . ' completed ' . $course->title],
                'occurred_at' => now(),
            ]);
        }

        return $badgeAwarded;
    }

    // ── Badges & Certificates ─────────────────────────────────────────────

    public function badges()
    {
        $auth = Auth::user();

        $badges = UserBadge::with('badge')
            ->where('user_id', $auth->id)
            ->latest('earned_at')
            ->get()
            ->map(fn (UserBadge $ub) => (object) [
                'name'        => $ub->badge->name ?? 'Badge',
                'description' => $ub->badge->description ?? '',
                'icon_url'    => $ub->badge->icon_url ?? null,
                'earned_at'   => $ub->earned_at,
            ])
            ->values();

        $enrollments = Enrollment::where('user_id', $auth->id)->get();

        return view('Student_MyBadges', [
            'user'  => UserPresenter::student($auth),
            'stats' => [
                'active_courses' => $enrollments->where('is_completed', false)->count(),
                'completed'      => $enrollments->where('is_completed', true)->count(),
                'badges_earned'  => $badges->count(),
                'certificates'   => $auth->certificates()->count(),
            ],
            'badges' => $badges,
        ]);
    }

    public function certificates()
    {
        $auth = Auth::user();

        $certificates = $auth->certificates()
            ->latest('issued_at')
            ->get()
            ->map(fn ($cert) => (object) [
                'title'        => $cert->title,
                'icon_url'     => null,
                'view_url'     => $cert->file_path ? asset($cert->file_path) : '#',
                'download_url' => $cert->file_path ? asset($cert->file_path) : '#',
            ])
            ->values();

        return view('Student_Certificates', [
            'user'         => UserPresenter::student($auth),
            'certificates' => $certificates,
        ]);
    }

    // ── Profile ───────────────────────────────────────────────────────────

    public function profile()
    {
        $auth = Auth::user();

        $enrolled  = Enrollment::where('user_id', $auth->id)->count();
        $completed = Enrollment::where('user_id', $auth->id)->where('is_completed', true)->count();

        return view('Student_Profile', [
            'user'     => UserPresenter::student($auth),
            'stats'    => [
                'courses_enrolled' => $enrolled,
                'badges_earned'    => $auth->badges()->count(),
                'certificates'     => $auth->certificates()->count(),
                'hours_learned'    => $auth->lessonCompletions()->count(),
            ],
            'progress'     => ['completed' => $completed, 'total' => $enrolled],
            'achievements' => [],
            'activities'   => [],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $auth = Auth::user();

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'role'          => ['nullable', 'string', 'max:80'],
            'phone'         => ['nullable', 'string', 'max:40'],
            'location'      => ['nullable', 'string', 'max:120'],
            'about'         => ['nullable', 'string', 'max:600'],
            'date_of_birth' => ['nullable', 'string', 'max:60'],
            'gender'        => ['nullable', 'string', 'max:30'],
            'education'     => ['nullable', 'string', 'max:120'],
            'bio'           => ['nullable', 'string', 'max:600'],
            'email'         => ['required', 'email', 'max:120', 'unique:users,email,' . $auth->id],
            'language'      => ['nullable', 'string', 'max:40'],
            'timezone'      => ['nullable', 'string', 'max:60'],
            'avatar_base64'    => ['nullable', 'string'],
            'current_password' => ['nullable', 'string'],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Optional password change — requires the current password.
        if (! empty($data['password'])) {
            if (! \Illuminate\Support\Facades\Hash::check($data['current_password'] ?? '', $auth->password)) {
                return back()
                    ->withErrors(['current_password' => 'Current password is incorrect.'])
                    ->withInput();
            }
            $auth->password = \Illuminate\Support\Facades\Hash::make($data['password']);
        }

        // Split the single display-name field back into first/last name.
        $parts             = preg_split('/\s+/', trim($data['name']), 2);
        $auth->first_name  = $parts[0];
        $auth->last_name   = $parts[1] ?? $auth->last_name;
        $auth->email       = $data['email'];
        $auth->phone       = $data['phone'] ?? null;
        $auth->location    = $data['location'] ?? null;
        $auth->about       = $data['about'] ?? null;
        $auth->gender      = $data['gender'] ?? null;
        $auth->education   = $data['education'] ?? null;
        $auth->bio         = $data['bio'] ?? null;
        $auth->language    = $data['language'] ?? null;
        $auth->timezone    = $data['timezone'] ?? null;

        if (! empty($data['role'])) {
            $auth->role_label = $data['role'];
        }

        if (! empty($data['date_of_birth'])) {
            try {
                $auth->date_of_birth = Carbon::parse($data['date_of_birth'])->format('Y-m-d');
            } catch (\Throwable $e) {
                $auth->date_of_birth = $data['date_of_birth'];
            }
        }

        // Avatar arrives as a client-resized base64 data-URL (~<50 KB).
        if (! empty($data['avatar_base64']) && str_starts_with($data['avatar_base64'], 'data:image/')) {
            $auth->avatar_url = $data['avatar_base64'];
        }

        $auth->profile_completed = true;
        $auth->save();

        // Nothing left to merge from the session now that the DB is the
        // source of truth.
        session()->forget('profile_data');

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }

    // ── Pathways ──────────────────────────────────────────────────────────

    public function pathways()
    {
        $auth = Auth::user();

        // ── The student's real learning footprint ────────────────────────
        $enrollments = Enrollment::with('course')->where('user_id', $auth->id)->get();
        $completed   = $enrollments->where('is_completed', true);
        $inProgress  = $enrollments->where('is_completed', false)->where('progress_percent', '>', 0);

        $completedTitles = $completed->map(fn ($e) => $e->course->title ?? '')->filter()->values();

        // Skills/competencies earned from completed courses (skills + category + title keywords)
        $competencies = $completed
            ->flatMap(fn ($e) => collect($e->course->skills ?? [])->concat([$e->course->category])->filter())
            ->unique()
            ->values();

        // ── Pick the student's desired pathway from what they're learning ─
        $pathways = DB::table('pathways')->where('is_active', true)->get();
        $pathway  = $this->bestPathwayFor($pathways, $enrollments, $competencies);

        $desiredComps = collect(json_decode($pathway->desired_competencies ?? '[]', true) ?: [])
            ->merge(json_decode($pathway->missing_competencies ?? '[]', true) ?: [])
            ->merge(json_decode($pathway->current_competencies ?? '[]', true) ?: [])
            ->filter()->unique()->values();

        // Which desired competencies the student already has / still misses
        $currentComps = $desiredComps->filter(fn ($c) => $this->studentHasCompetency($c, $competencies, $completedTitles))->values();
        $missingComps = $desiredComps->reject(fn ($c) => $this->studentHasCompetency($c, $competencies, $completedTitles))->values();

        // Readiness = share of desired competencies already earned
        $readiness = $desiredComps->count() > 0
            ? (int) round(($currentComps->count() / $desiredComps->count()) * 100)
            : 0;

        // ── Roadmap steps from the student's actual course progress ──────
        $palette = ['#2DD4CF', '#D8C84A', '#E5483D', '#8B5CF6', '#F59E0B'];
        $steps = $enrollments->take(4)->values()->map(function ($e, $i) use ($palette) {
            $status = $e->is_completed ? 'completed' : ($e->progress_percent > 0 ? 'current' : 'locked');

            return [
                'label'  => 'Goal ' . ($i + 1),
                'title'  => \Illuminate\Support\Str::limit($e->course->title ?? 'Course', 18, ''),
                'color'  => $status === 'locked' ? '#9CA3AF' : $palette[$i % count($palette)],
                'status' => $status,
            ];
        })->all();
        if ($steps === []) {
            $steps = json_decode($pathway->steps ?? '[]', true) ?: [];
        }

        // ── Smart recommendations ────────────────────────────────────────
        $recommendations = $this->buildRecommendations($auth, $enrollments, $missingComps, $pathway);

        return view('Student_MyPathways', [
            'user'    => UserPresenter::student($auth),
            'pathway' => [
                'steps'                    => $steps,
                'destination'              => $pathway->destination ?? 'Full Stack Web Developer',
                'destination_color'        => $pathway->destination_color ?? '#5FD93D',
                'connector_to_destination' => $pathway->connector_color ?? '#2563EB',
            ],
            'recommendations' => $recommendations,
            'desiredPathway'  => [
                'title'                => $pathway->desired_title ?? $pathway->name ?? 'Career Pathway',
                'current_competencies' => $currentComps->all() ?: ['—'],
                'missing_competencies' => $missingComps->all() ?: ['—'],
            ],
            'readinessPercent' => $readiness,
            'readinessLabel'   => $pathway->readiness_label ?? ($pathway->desired_title ?? 'this Pathway'),
        ]);
    }

    /**
     * Chooses the pathway that best matches what the student is actually
     * studying (course titles/categories vs. pathway names/destinations),
     * falling back to the first active pathway.
     */
    private function bestPathwayFor($pathways, $enrollments, $competencies)
    {
        if ($pathways->isEmpty()) {
            return (object) [];
        }

        $haystack = strtolower($enrollments->map(fn ($e) => ($e->course->title ?? '') . ' ' . ($e->course->category ?? '') . ' ' . implode(' ', $e->course->skills ?? []))->implode(' '));

        $best  = $pathways->first();
        $score = -1;
        foreach ($pathways as $p) {
            $s = 0;
            foreach (array_filter([$p->name ?? null, $p->destination ?? null, $p->desired_title ?? null]) as $kw) {
                foreach (preg_split('/\s+/', strtolower($kw)) as $word) {
                    if (strlen($word) > 3 && str_contains($haystack, $word)) {
                        $s++;
                    }
                }
            }
            if ($s > $score) {
                $score = $s;
                $best  = $p;
            }
        }

        return $best;
    }

    /** Loose competency match: skill text, category, or a completed course title containing it. */
    private function studentHasCompetency(string $competency, $competencies, $completedTitles): bool
    {
        $needle = strtolower(trim($competency));
        if ($needle === '') {
            return false;
        }
        foreach ($competencies as $c) {
            if (str_contains(strtolower($c), $needle) || str_contains($needle, strtolower($c))) {
                return true;
            }
        }
        foreach ($completedTitles as $t) {
            if (str_contains(strtolower($t), $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Recommendation engine ("AI"-style scoring): ranks every published
     * course the student hasn't finished by how well it closes their
     * competency gaps, how popular it is, and how relevant its skills are —
     * and reports the student's real completion for in-progress picks.
     */
    private function buildRecommendations($auth, $enrollments, $missingComps, $pathway): array
    {
        $doneIds   = $enrollments->where('is_completed', true)->pluck('course_id');
        $activeMap = $enrollments->where('is_completed', false)->keyBy('course_id');

        $candidates = \App\Models\Course::where('is_published', true)
            ->whereNotIn('id', $doneIds)
            ->get();

        $scored = $candidates->map(function ($course) use ($missingComps, $activeMap, $pathway) {
            $text = strtolower($course->title . ' ' . ($course->category ?? '') . ' ' . implode(' ', $course->skills ?? []));

            $score = 0;
            foreach ($missingComps as $comp) {
                foreach (preg_split('/\s+/', strtolower($comp)) as $word) {
                    if (strlen($word) > 3 && str_contains($text, $word)) {
                        $score += 3;   // directly closes a missing competency
                    }
                }
            }
            foreach (array_filter([$pathway->desired_title ?? null, $pathway->destination ?? null]) as $kw) {
                foreach (preg_split('/\s+/', strtolower($kw)) as $word) {
                    if (strlen($word) > 3 && str_contains($text, $word)) {
                        $score += 2;   // aligned with the desired pathway
                    }
                }
            }
            $score += min(2, (int) floor(($course->enrolled_count ?? 0) / 5));  // popularity nudge
            if ($activeMap->has($course->id)) {
                $score += 1;           // already started — encourage finishing
            }

            return ['course' => $course, 'score' => $score];
        })
        ->sortByDesc('score')
        ->take(4)
        ->values();

        return $scored->map(function ($row) use ($activeMap) {
            $enrollment = $activeMap->get($row['course']->id);

            return [
                'title'      => ($enrollment ? 'Finish: ' : 'Take: ') . \Illuminate\Support\Str::limit($row['course']->title, 32, ''),
                'completion' => (int) ($enrollment->progress_percent ?? 0),
            ];
        })->all();
    }

    // ── Analytics ─────────────────────────────────────────────────────────

    public function analytics()
    {
        $auth = Auth::user();

        $enrollments = Enrollment::with('course')->where('user_id', $auth->id)->get();

        $avgScore = QuizAttempt::where('user_id', $auth->id)->avg('score');

        $activeCourses = $enrollments->map(function (Enrollment $e) {
            $students = $e->course ? $e->course->enrollments()->count() : 0;
            $faculty  = 1;

            return (object) [
                'title'         => $e->course->title ?? 'Course',
                'meta'          => $students . ' Students · ' . $faculty . ' Faculty',
                'thumbnail_url' => $e->course->thumbnail_url ?? null,
                'percent'       => (int) $e->progress_percent,
            ];
        })->values();

        [$enrollmentByCourse, $completionRate] = $this->courseCharts();

        $recentBadges = UserBadge::query()
            ->join('badges', 'badges.id', '=', 'user_badges.badge_id')
            ->select('badges.name', DB::raw('COUNT(*) as earned_count'))
            ->groupBy('badges.name')
            ->orderByDesc('earned_count')
            ->limit(4)
            ->get()
            ->map(fn ($row) => (object) ['name' => $row->name, 'earned_count' => (int) $row->earned_count])
            ->values();

        return view('Student_Analytics', [
            'user'  => UserPresenter::student($auth),
            'stats' => [
                'active_courses' => $enrollments->where('is_completed', false)->count(),
                'badges_earned'  => $auth->badges()->count(),
                'score_avg'      => $avgScore !== null ? round((float) $avgScore, 1) : 0,
                'hours_enrolled' => $enrollments->count() * 7,
            ],
            'activeCourses'      => $activeCourses,
            'recentBadges'       => $recentBadges,
            'enrollmentByCourse' => $enrollmentByCourse,
            'completionRate'     => $completionRate,
        ]);
    }

    /**
     * Shared chart rows: top courses by enrollment, plus average
     * completion (progress) per course.
     *
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection}
     */
    private function courseCharts(): array
    {
        $rows = Enrollment::query()
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
            'label'   => $this->shortTitle($row->title),
            'value'   => (int) $row->learners,
            'percent' => (int) round($row->learners / $max * 100),
        ])->values();

        $completionRate = $rows->map(fn ($row) => (object) [
            'label'   => $this->shortTitle($row->title),
            'value'   => (int) round($row->avg_progress),
            'percent' => (int) round($row->avg_progress),
        ])->values();

        return [$enrollmentByCourse, $completionRate];
    }

    private function shortTitle(string $title): string
    {
        return \Illuminate\Support\Str::limit($title, 14, '');
    }
}
