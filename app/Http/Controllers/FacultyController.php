<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Support\UserPresenter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * FacultyController — all Faculty_* pages, backed by the database.
 *
 * Course ownership is courses.created_by; modules / lessons / quizzes are
 * real rows in course_modules, course_lessons, quizzes and quiz_questions.
 * The Faculty_My_Courses Blade drives its forms with $module->idx and
 * $lesson->key — we feed it the real record ids ('mod-#' / 'les-#' keys),
 * so the view works unchanged.
 */
class FacultyController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────────────

    public function dashboard()
    {
        $auth = Auth::user();

        $courses = Course::where('created_by', $auth->id)
            ->withCount(['modules', 'enrollments'])
            ->latest()
            ->get();

        $stats = [
            'total_courses'  => $courses->count(),
            'published'      => $courses->filter(fn (Course $c) => $c->statusLabel() === 'Published')->count(),
            'total_students' => Enrollment::whereIn('course_id', $courses->pluck('id'))->distinct()->count('user_id'),
            'enrollments'    => Enrollment::whereIn('course_id', $courses->pluck('id'))->count(),
        ];

        return view('Faculty_dashboard', [
            'user'    => UserPresenter::faculty($auth),
            'stats'   => $stats,
            'courses' => $courses->map(fn (Course $c) => (object) [
                'id'             => $c->id,
                'title'          => $c->title,
                'status'         => $c->statusLabel(),
                'students_count' => (int) $c->enrollments_count,
                'modules_count'  => (int) $c->modules_count,
                'thumbnail_url'  => $c->thumbnail_url,
            ])->values(),
        ]);
    }

    // ── Enrolled Students (this faculty's courses only) ─────────────────

    public function students()
    {
        $auth = Auth::user();

        $students = Enrollment::with(['user', 'course'])
            ->whereHas('course', fn ($q) => $q->where('created_by', $auth->id))
            ->get()
            ->groupBy('user_id')
            ->map(function ($rows) {
                $user = $rows->first()->user;

                return (object) [
                    'name'       => $user->name ?? 'Student',
                    'student_id' => $user->student_id ?? $user->user_code ?? '',
                    'avatar_url' => $user->avatar_url,
                    'courses'    => $rows->map(fn ($e) => (object) [
                        'title'     => $e->course->title ?? 'Course',
                        'percent'   => (int) $e->progress_percent,
                        'completed' => (bool) $e->is_completed || (int) $e->progress_percent >= 100,
                    ])->values(),
                ];
            })
            ->sortBy('name')
            ->values();

        return view('Faculty_Students', [
            'user'     => UserPresenter::faculty($auth),
            'students' => $students,
        ]);
    }

    // ── Profile ───────────────────────────────────────────────────────────

    public function profile()
    {
        $auth    = Auth::user();
        $courses = Course::where('created_by', $auth->id)->withCount('enrollments')->get();

        // Teaching performance — monthly enrollments over the last 6
        // months, normalised to a percentage for the bar chart.
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
        $counts = $months->map(fn (Carbon $m) => Enrollment::whereIn('course_id', $courses->pluck('id')->all() ?: [0])
            ->whereYear('enrolled_at', $m->year)->whereMonth('enrolled_at', $m->month)->count());
        $peak   = max(1, (int) $counts->max());

        $performance = $months->map(fn (Carbon $m) => [
            'label'   => $m->format('M'),
            'percent' => (int) round($counts->shift() / $peak * 100),
        ])->all();

        // Weekly activity — analytics events on this faculty member's
        // courses, bucketed per weekday.
        $weekStart = now()->startOfWeek();
        $dowCounts = DB::table('analytics_events')
            ->where('occurred_at', '>=', $weekStart)
            ->pluck('occurred_at')
            ->map(fn ($ts) => (int) Carbon::parse($ts)->dayOfWeek) // 0=Sun … 6=Sat
            ->countBy();

        $activity = collect(range(0, 6))->map(function ($i) use ($dowCounts) {
            $dow = ($i + 1) % 7; // Mon=1 … Sun=0

            return ['label' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'][$i], 'hours' => (int) ($dowCounts[$dow] ?? 0)];
        })->all();

        return view('Faculty_Profile', [
            'user'      => UserPresenter::faculty($auth),
            'languages' => ['English', 'Filipino'],
            'timezones' => [
                '(GMT + 8:00) Asia/Manila',
                '(GMT + 5:30) Asia/Kolkata',
                '(GMT + 0:00) UTC',
            ],
            'performance'    => $performance,
            'activity'       => $activity,
            'profileCourses' => $courses->map(fn (Course $c) => (object) [
                'title'         => $c->title,
                'category'      => $c->level ?? 'Course',
                'students'      => (int) $c->enrollments_count,
                'rating'        => null,
                'completion'    => (int) round((float) Enrollment::where('course_id', $c->id)->avg('progress_percent')),
                'earnings'      => '$0',
                'status'        => $c->statusLabel(),
                'thumbnail_url' => $c->thumbnail_url,
            ])->values(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $auth = Auth::user();

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'phone'            => ['nullable', 'string', 'max:30'],
            'location'         => ['nullable', 'string', 'max:120'],
            'role'             => ['nullable', 'string', 'max:60'],
            'about'            => ['nullable', 'string', 'max:600'],
            'date_of_birth'    => ['nullable', 'date'],
            'gender'           => ['nullable', 'string', 'max:30'],
            'education'        => ['nullable', 'string', 'max:120'],
            'bio'              => ['nullable', 'string', 'max:600'],
            'email'            => ['required', 'email', 'max:120', 'unique:users,email,' . $auth->id],
            'current_password' => ['nullable', 'string'],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'],
            'language'         => ['nullable', 'string', 'max:40'],
            'timezone'         => ['nullable', 'string', 'max:60'],
            'avatar_base64'    => ['nullable', 'string'],
        ]);

        // Optional password change — requires the current password.
        if (! empty($data['password'])) {
            if (! Hash::check($data['current_password'] ?? '', $auth->password)) {
                return back()
                    ->withErrors(['current_password' => 'Current password is incorrect.'])
                    ->withInput();
            }
            $auth->password = Hash::make($data['password']);
        }

        $parts            = preg_split('/\s+/', trim($data['name']), 2);
        $auth->first_name = $parts[0];
        $auth->last_name  = $parts[1] ?? $auth->last_name;
        $auth->email      = $data['email'];
        $auth->phone      = $data['phone'] ?? null;
        $auth->location   = $data['location'] ?? null;
        $auth->about      = $data['about'] ?? null;
        $auth->gender     = $data['gender'] ?? null;
        $auth->education  = $data['education'] ?? null;
        $auth->bio        = $data['bio'] ?? null;
        $auth->language   = $data['language'] ?? null;
        $auth->timezone   = $data['timezone'] ?? null;

        if (! empty($data['role'])) {
            $auth->role_label = $data['role'];
        }
        if (! empty($data['date_of_birth'])) {
            $auth->date_of_birth = Carbon::parse($data['date_of_birth'])->format('Y-m-d');
        }
        if (! empty($data['avatar_base64']) && str_starts_with($data['avatar_base64'], 'data:image/')) {
            $auth->avatar_url = $data['avatar_base64'];
        }

        $auth->profile_completed = true;
        $auth->save();

        return redirect()->route('faculty.profile')->with('success', 'Profile updated successfully!');
    }

    // ── Analytics ─────────────────────────────────────────────────────────

    public function analytics()
    {
        $auth       = Auth::user();
        $courseIds  = Course::where('created_by', $auth->id)->pluck('id')->all() ?: [0];
        $enrollBase = Enrollment::whereIn('course_id', $courseIds);

        // Cumulative learner growth, one point per month for the last year.
        $academyStats = collect(range(11, 0))->map(function ($i) use ($courseIds) {
            $month = now()->subMonths($i);

            return [
                'label' => $month->format('M j,') . "\n" . $month->format('Y'),
                'value' => Enrollment::whereIn('course_id', $courseIds)
                    ->where('enrolled_at', '<=', $month->copy()->endOfMonth())
                    ->distinct()->count('user_id'),
            ];
        })->all();

        // Learner success breakdown from real progress values.
        $progressRows = (clone $enrollBase)
            ->select('user_id', DB::raw('MAX(progress_percent) as best'), DB::raw('MAX(is_completed) as done'))
            ->groupBy('user_id')
            ->get();

        $learnerSuccess = [
            ['label' => 'Completed one or more courses',         'count' => $progressRows->where('done', 1)->count()],
            ['label' => 'Got through at least half of a course', 'count' => $progressRows->where('done', 0)->where('best', '>=', 50)->count()],
            ['label' => 'Started a course',                      'count' => $progressRows->where('done', 0)->where('best', '<', 50)->count()],
        ];

        return view('Faculty_Analytics', [
            'user'            => UserPresenter::faculty($auth),
            'onlineNow'       => (int) DB::table('sessions')->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
                ->distinct()->count('user_id'),
            'activeThisMonth' => (clone $enrollBase)
                ->where('enrolled_at', '>=', now()->startOfMonth())
                ->distinct()->count('user_id'),
            'stats' => [
                'total_learners' => (clone $enrollBase)->distinct()->count('user_id'),
                'certificates'   => DB::table('certificates')->whereIn('course_id', $courseIds)->count(),
                'lessons_done'   => DB::table('lesson_completions')
                    ->join('course_lessons', 'course_lessons.id', '=', 'lesson_completions.lesson_id')
                    ->whereIn('course_lessons.course_id', $courseIds)->count(),
            ],
            'academyStats'   => $academyStats,
            'learnerSuccess' => $learnerSuccess,
        ]);
    }

    // ── My Courses (list) ─────────────────────────────────────────────────

    public function courses()
    {
        $auth = Auth::user();

        $courses = Course::where('created_by', $auth->id)
            ->withCount(['modules', 'lessons', 'enrollments'])
            ->latest()
            ->get()
            ->map(fn (Course $c) => (object) [
                'id'             => $c->id,
                'title'          => $c->title,
                'description'    => $c->description,
                'status'         => $c->statusLabel(),
                'level'          => $c->level,
                'students_count' => (int) $c->enrollments_count,
                'modules_count'  => (int) $c->modules_count,
                'lessons_count'  => (int) $c->lessons_count,
                'thumbnail_url'  => $c->thumbnail_url,
            ])->values();

        return view('Faculty_My_Courses', [
            'mode'    => 'list',
            'user'    => UserPresenter::faculty($auth),
            'courses' => $courses,
        ]);
    }

    // ── Manage Course ─────────────────────────────────────────────────────

    public function manage(?int $id = null)
    {
        $auth = Auth::user();

        $course = $id !== null
            ? Course::where('created_by', $auth->id)->findOrFail($id)
            : Course::where('created_by', $auth->id)->oldest()->firstOrFail();

        $course->load(['modules.lessons', 'modules.quiz.questions']);

        // Modules — idx/key carry the real ids so every Blade form
        // (add lesson, add quiz, delete) targets the right records.
        $modules = $course->modules->map(function (CourseModule $m) {
            return (object) [
                'idx'      => $m->id,
                'key'      => 'mod-' . $m->id,
                'title'    => $m->title,
                'subtitle' => $m->description,
                'lessons'  => $m->lessons->map(fn (CourseLesson $l) => (object) [
                    'key'           => 'les-' . $l->id,
                    'title'         => $l->title,
                    'meta'          => $l->metaLabel(),
                    'file_url'      => $l->file_url,
                    'thumbnail_url' => ($l->type === 'Image' && $l->file_url) ? asset($l->file_url) : null,
                ])->values(),
                'quiz' => $m->quiz ? (object) [
                    'title'           => $m->quiz->title,
                    'questions_count' => $m->quiz->questions->count(),
                    'passing_score'   => $m->quiz->passing_score,
                ] : null,
            ];
        })->values();

        // Enrolled students for the right-hand column.
        $students = Enrollment::with('user')
            ->where('course_id', $course->id)
            ->get()
            ->map(fn (Enrollment $e) => (object) [
                'name'       => $e->user->name ?? 'Student',
                'student_id' => $e->user->student_id ?? $e->user->user_code ?? '',
                'status'     => $e->is_completed ? 'Completed' : 'Active',
                'avatar_url' => $e->user->avatar_url ?? null,
            ])
            ->values();

        // Average score per quiz in this course.
        $quizAverages = Quiz::where('course_id', $course->id)
            ->get()
            ->map(function (Quiz $q) {
                $avg = DB::table('quiz_attempts')->where('quiz_id', $q->id)->avg('score');

                return $avg !== null
                    ? (object) ['title' => $q->title, 'percent' => (int) round($avg)]
                    : null;
            })
            ->filter()
            ->values();

        return view('Faculty_My_Courses', [
            'mode'    => 'manage',
            'user'    => UserPresenter::faculty($auth),
            'course'  => (object) [
                'id'             => $course->id,
                'title'          => $course->title,
                'description'    => $course->description,
                'status'         => $course->statusLabel(),
                'level'          => $course->level,
                'students_count' => $students->count(),
                'modules_count'  => $modules->count(),
                'lessons_count'  => $modules->sum(fn ($m) => $m->lessons->count()),
                'thumbnail_url'  => $course->thumbnail_url,
            ],
            'modules'      => $modules,
            'students'     => $students,
            'quizAverages' => $quizAverages,
        ]);
    }

    // ── Create Course ─────────────────────────────────────────────────────

    public function createForm()
    {
        return view('Faculty_Create_Courses', [
            'user' => UserPresenter::faculty(Auth::user()),
            // Category now carries the programs (the separate Program
            // dropdown was removed).
            'categories' => [
                'BS Information Technology',
                'BS Computer Science',
                'BS Information Systems',
                'Web Development',
                'Artificial Intelligence',
                'Databases',
                'Networking',
                'Computer Fundamentals',
                'Project Management',
            ],
            'terms' => [
                '1st Semester 2026 - 2027',
                '2nd Semester 2026 - 2027',
                'Summer 2027',
            ],
            'levels' => ['Beginner', 'Intermediate', 'Advanced'],
        ]);
    }

    public function createStore(Request $request)
    {
        $auth = Auth::user();

        $data = $request->validate([
            'title'               => ['nullable', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'category'            => ['nullable', 'string', 'max:120'],
            'program'             => ['nullable', 'string', 'max:120'],
            'term'                => ['nullable', 'string', 'max:120'],
            'level'               => ['nullable', 'string', 'max:60'],
            'duration'            => ['nullable', 'numeric', 'min:0'],
            'passing_score'       => ['nullable', 'integer', 'min:0', 'max:100'],
            'learning_objectives' => ['nullable', 'string'],
            'skills'              => ['nullable', 'string'],
            'prerequisites'       => ['nullable', 'string', 'max:120'],
            // NOTE: no "boolean" rule — a checked HTML checkbox sends the
            // string "on", which fails boolean validation and silently
            // bounced the form back. $request->boolean() handles it below.
            'feature_homepage'    => ['nullable'],
            'thumbnail'           => ['nullable', 'image', 'max:51200'],   // 50 MB
            // The Blade radios use "draft" and "submit" (submit = pending approval)
            'status'              => ['nullable', 'string', 'in:draft,submit,pending'],
        ]);

        try {
            $course = $this->storeNewCourse($request, $data, $auth);
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors([
                'create' => 'The course could not be saved: ' . $e->getMessage(),
            ]);
        }

        return redirect()->route('faculty.courses.manage', $course->id);
    }

    /**
     * Creates the Course row (and optional thumbnail upload) for the
     * currently logged-in faculty member.
     */
    private function storeNewCourse(Request $request, array $data, $auth): Course
    {
        $title = trim($data['title'] ?? '') !== '' ? trim($data['title']) : 'Untitled Course';

        // Optional thumbnail upload → public/uploads/thumbnails
        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
            $dir = public_path('uploads/thumbnails');
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $name = uniqid('thumb_') . '.' . strtolower($request->file('thumbnail')->getClientOriginalExtension());
            $request->file('thumbnail')->move($dir, $name);
            $thumbnailUrl = 'uploads/thumbnails/' . $name;
        }

        $hours = (int) ($data['duration'] ?? 0);

        return Course::create([
            'title'           => $title,
            'slug'            => $this->uniqueSlug($title),
            'description'     => trim($data['description'] ?? ''),
            'skills'          => $this->linesToArray($data['skills'] ?? null),
            'objectives'      => $this->linesToArray($data['learning_objectives'] ?? null),
            'category'        => $data['category'] ?? null,
            'program'         => $data['program'] ?? null,
            'term'            => $data['term'] ?? null,
            'level'           => $data['level'] ?? 'Beginner',
            'duration'        => $hours > 0 ? $hours . 'h' : null,
            'passing_score'   => (int) ($data['passing_score'] ?? 75),
            'instructor'      => $auth->name,
            'created_by'      => $auth->id,
            'is_featured'     => false,   // only the admin can feature a course on the homepage
            'thumbnail_url'   => $thumbnailUrl,
            'is_published'    => false,
            'approval_status' => ($data['status'] ?? 'submit') === 'draft' ? 'draft' : 'pending',
            'is_approved'     => false,
        ]);
    }

    // ── Modules / Lessons / Quizzes ───────────────────────────────────────

    public function storeModule(Request $request, int $id)
    {
        $course = $this->ownedCourse($id);

        $data = $request->validate([
            'module_title'       => ['nullable', 'string', 'max:255'],
            'module_description' => ['nullable', 'string', 'max:500'],
        ]);

        CourseModule::create([
            'course_id'   => $course->id,
            'title'       => trim($data['module_title'] ?? '') !== '' ? trim($data['module_title']) : 'Untitled Module',
            'description' => trim($data['module_description'] ?? ''),
            'order'       => ((int) $course->modules()->max('order')) + 1,
        ]);

        return redirect()->route('faculty.courses.manage', $course->id);
    }

    public function deleteModule(int $id, string $key)
    {
        $course = $this->ownedCourse($id);

        if (preg_match('/^mod-(\d+)$/', $key, $m)) {
            CourseModule::where('course_id', $course->id)->where('id', (int) $m[1])->delete();
        }

        return redirect()->route('faculty.courses.manage', $course->id);
    }

    public function storeLesson(Request $request, int $id, int $moduleIndex)
    {
        $course = $this->ownedCourse($id);
        $module = CourseModule::where('course_id', $course->id)->findOrFail($moduleIndex);

        // If the browser sent the file but PHP rejected it before Laravel
        // ever saw it (upload_max_filesize / post_max_size exceeded, or a
        // partial upload), hasFile() is false while the field IS present.
        // Fail loudly instead of silently saving nothing.
        if ($request->request->has('lesson_file') || $request->files->has('lesson_file')) {
            $file = $request->file('lesson_file');
            if ($file && ! $file->isValid()) {
                return back()->withInput()->withErrors([
                    'lesson_file' => 'The file upload failed (' . $this->uploadErrorMessage($file->getError())
                        . '). If it is a large video, raise upload_max_filesize and post_max_size in php.ini.',
                ])->with('open_lesson_form', $module->id);
            }
        }

        $data = $request->validate([
            'lesson_title'   => ['nullable', 'string', 'max:255'],
            'lesson_content' => ['nullable', 'string'],
            'duration'       => ['nullable', 'integer', 'min:0'],
            'lesson_file'    => ['nullable', 'file', 'max:2048000'],   // 2000 MB
        ]);

        try {
            $this->storeLessonRecord($request, $data, $course, $module);
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()
                ->withErrors(['lesson_file' => 'The lesson could not be saved: ' . $e->getMessage()])
                ->with('open_lesson_form', $module->id);
        }

        return redirect()->route('faculty.courses.manage', $course->id);
    }

    /**
     * Saves the uploaded lesson file (any type) and creates the
     * CourseLesson row for the given module.
     */
    private function storeLessonRecord(Request $request, array $data, Course $course, CourseModule $module): void
    {
        $duration = (int) ($data['duration'] ?? 0);

        // Lesson type is detected from the uploaded file — any extension is
        // accepted; unknown types become a downloadable "File" lesson.
        $type     = 'Text';
        $fileUrl  = null;
        $fileName = null;
        if ($request->hasFile('lesson_file') && $request->file('lesson_file')->isValid()) {
            $file = $request->file('lesson_file');
            $ext  = strtolower($file->getClientOriginalExtension() ?: 'bin');

            $type = match (true) {
                $ext === 'pdf'                                          => 'PDF',
                in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'm4v'], true) => 'Video',
                in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true) => 'Image',
                $ext === 'txt'                                          => 'Text',
                default                                                 => 'File',
            };

            $dir = public_path('uploads/lessons');
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $name = uniqid('lesson_') . '.' . $ext;
            $file->move($dir, $name);
            $fileUrl  = 'uploads/lessons/' . $name;
            $fileName = $file->getClientOriginalName();
        }

        CourseLesson::create([
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title'     => trim($data['lesson_title'] ?? '') !== '' ? trim($data['lesson_title']) : 'Untitled Lesson',
            'type'      => $type,
            'duration'  => $duration > 0 ? $duration . 'm' : null,
            'content'   => trim($data['lesson_content'] ?? ''),
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
            'order'     => ((int) $module->lessons()->max('order')) + 1,
        ]);

        $course->lessons_count = $course->lessons()->count();
        $course->save();
    }

    /** Human-readable PHP upload error. */
    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE   => 'the file exceeds the server upload_max_filesize limit',
            UPLOAD_ERR_FORM_SIZE  => 'the file exceeds the form size limit',
            UPLOAD_ERR_PARTIAL    => 'the file was only partially uploaded',
            UPLOAD_ERR_NO_FILE    => 'no file was received',
            UPLOAD_ERR_NO_TMP_DIR => 'the server has no temporary upload folder',
            UPLOAD_ERR_CANT_WRITE => 'the server could not write the file to disk',
            default               => 'upload error code ' . $code,
        };
    }

    public function deleteLesson(int $id, int $moduleIndex, string $key)
    {
        $course = $this->ownedCourse($id);

        if (preg_match('/^les-(\d+)$/', $key, $m)) {
            CourseLesson::where('course_id', $course->id)->where('id', (int) $m[1])->delete();
        }

        $course->lessons_count = $course->lessons()->count();
        $course->save();

        return redirect()->route('faculty.courses.manage', $course->id);
    }

    /**
     * Quiz builder screen (Faculty_My_Courses in 'quiz' mode), prefilled
     * with the module's existing quiz when there is one.
     */
    public function quizCreate(int $id, int $moduleIndex)
    {
        $course = $this->ownedCourse($id);
        $module = CourseModule::where('course_id', $course->id)->findOrFail($moduleIndex);

        $quiz = null;
        $quizModel = Quiz::with('questions')->where('module_id', $module->id)->first();
        if ($quizModel) {
            $quiz = (object) [
                'title'         => $quizModel->title,
                'items'         => $quizModel->questions->count(),
                'passing_score' => $quizModel->passing_score,
                'attempts'      => $quizModel->attempts,
                'time_limit'    => $quizModel->time_limit,
                'instructions'  => $quizModel->instructions ?? '',
                'questions'     => $quizModel->questions->map(function (QuizQuestion $q) {
                    $options = $q->options ?? [];
                    $correct = $q->correct_answer !== null ? array_search($q->correct_answer, $options, true) : null;

                    return [
                        'text'    => $q->question,
                        'type'    => $q->type ?? 'Multiple Choice',
                        'points'  => (int) $q->points,
                        'choices' => $options,
                        'correct' => $correct === false ? null : $correct,
                        'answer'  => ($q->type === 'Identification') ? $q->correct_answer : null,
                    ];
                })->values()->all(),
            ];
        }

        return view('Faculty_My_Courses', [
            'mode'        => 'quiz',
            'user'        => UserPresenter::faculty(Auth::user()),
            'course'      => (object) ['id' => $course->id, 'title' => $course->title],
            'moduleIdx'   => $module->id,
            'moduleTitle' => $module->title,
            'quiz'        => $quiz,
        ]);
    }

    public function storeQuiz(Request $request, int $id, int $moduleIndex)
    {
        $course = $this->ownedCourse($id);
        $module = CourseModule::where('course_id', $course->id)->findOrFail($moduleIndex);

        $passing = (int) $request->input('passing_score', 0);
        if ($passing < 1 || $passing > 100) {
            $passing = 75;
        }

        // Parse every question card — blank questions are skipped.
        $questions = [];
        foreach ($request->input('questions', []) as $qi) {
            $text = trim($qi['text'] ?? '');
            if ($text === '') {
                continue;
            }

            $qType = $qi['type'] ?? 'Multiple Choice';
            if ($qType === 'Identification') {
                $choices = [];
                $correct = trim($qi['answer'] ?? '');
            } elseif ($qType === 'True or False') {
                $choices = ['True', 'False'];
                $correct = isset($qi['tf_correct']) && $qi['tf_correct'] !== null
                    ? ($choices[(int) $qi['tf_correct']] ?? null)
                    : null;
            } else {
                $choices = array_values(array_filter(array_map('trim', $qi['choices'] ?? [])));
                $correct = isset($qi['correct']) && $qi['correct'] !== null && $qi['correct'] !== ''
                    ? ($choices[(int) $qi['correct']] ?? null)
                    : null;
            }

            $questions[] = [
                'question'       => $text,
                'type'           => $qType,
                'points'         => (int) ($qi['points'] ?? 0),
                'options'        => $choices,
                'correct_answer' => $correct,
            ];
        }

        // One quiz per module — update in place or create, then rebuild
        // its question set.
        $quiz = Quiz::updateOrCreate(
            ['module_id' => $module->id],
            [
                'course_id'     => $course->id,
                'title'         => trim($request->input('quiz_title', '')) !== '' ? trim($request->input('quiz_title')) : 'Untitled Quiz',
                'passing_score' => $passing,
                'attempts'      => $request->input('attempts'),
                'time_limit'    => (int) $request->input('time_limit', 0),
                'instructions'  => trim($request->input('instructions', '')),
                'is_active'     => true,
            ]
        );

        $quiz->questions()->delete();
        foreach ($questions as $q) {
            $quiz->questions()->create($q);
        }

        return redirect()->route('faculty.courses.manage', $course->id);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Fetch a course owned by the current faculty member (or 404).
     */
    private function ownedCourse(int $id): Course
    {
        return Course::where('created_by', Auth::id())->findOrFail($id);
    }

    /**
     * Split a textarea (one item per line, commas also ok) into a clean
     * array — used for skills / learning objectives.
     *
     * @return list<string>|null
     */
    private function linesToArray(?string $text): ?array
    {
        $items = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) $text))));

        return $items === [] ? null : $items;
    }

    private function uniqueSlug(string $title): string
    {
        $base  = Str::slug($title) ?: 'course';
        $slug  = $base;
        $i     = 2;
        while (Course::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
