<?php

use App\Http\Middleware\RoleBasedAccess;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════════════════════
// STUDENT VIEW COMPOSER
// Runs on STUDENT view renders only ('Student_*') and merges saved student
// profile data (avatar, name, role) from the session into the $user object —
// so the topbar avatar and username stay up-to-date on all STUDENT pages.
//
// ⚠ Deliberately scoped to 'Student_*' (NOT '*'): the student and faculty
//   sides are separate. Faculty pages use their own session key
//   ('faculty_profile' via facultyProfileUser()), so student profile edits
//   must never bleed into Faculty (or Admin) views.
// ══════════════════════════════════════════════════════════════════════════
app('view')->composer('Student_*', function (\Illuminate\View\View $view) {
    try {
        $saved = session('profile_data', []);
    } catch (\Throwable $e) {
        return; // session not available yet (e.g. during boot)
    }

    if (empty($saved)) return;

    $data = $view->getData();
    if (! isset($data['user'])) return;

    $user = $data['user'];

    // Merge whichever fields were saved in the session
    foreach (['name', 'role', 'avatar_url', 'email', 'phone', 'location'] as $field) {
        if (array_key_exists($field, $saved) && ! empty($saved[$field])) {
            $user->$field = $saved[$field];
        }
    }

    $view->with('user', $user);
});

Route::get('/', fn() => view('Homepage'))->name('Homepage');

// Login routes
Route::get('/login', [\App\Http\Controllers\LoginController::class, 'show'])->name('login');
Route::post('/login', [\App\Http\Controllers\LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [\App\Http\Controllers\LoginController::class, 'destroy'])->name('logout');

// Registration routes
Route::get('/register', [\App\Http\Controllers\RegisterController::class, 'show'])->name('register');
Route::post('/register', [\App\Http\Controllers\RegisterController::class, 'store'])->name('register.store');

// Students directory — linked from the navbar "Students" button.
// The view ships with its own dummy students; when the DB is ready,
// pass a real collection: view('Student_List', ['students' => $students])
// where each student has ->name and ->student_id.
Route::get('/students', fn() => view('Student_List'))->name('students.index');

// Navbar "Announcement" and "Microcredentials" links point to /announcements
// and /microcredentials. On the Homepage a script intercepts these clicks and
// smooth-scrolls to the matching sections — but from ANY OTHER page (e.g.
// /students) the browser really navigates here, which used to 404. These
// redirects send visitors back to the right homepage sections instead.
Route::get('/announcements',    fn() => redirect('/#announcements'));
Route::get('/microcredentials', fn() => redirect('/#featured'));

Route::get('/notifications', function () {
    $notifications = collect([
        (object) [
            'title' => 'Course update available',
            'message' => 'A new lesson was added to your enrolled course.',
            'time' => '10 min ago',
            'type' => 'course',
            'unread' => true,
        ],
        (object) [
            'title' => 'New announcement',
            'message' => 'The faculty team posted a new milestone update.',
            'time' => '1 hour ago',
            'type' => 'announcement',
            'unread' => true,
        ],
        (object) [
            'title' => 'System reminder',
            'message' => 'Your badge portfolio was refreshed.',
            'time' => 'Yesterday',
            'type' => 'system',
            'unread' => false,
        ],
    ]);

    return view('notifications', compact('notifications'));
})->name('notifications.index');

Route::get('/search', function () {
    $query = trim((string) request('q', ''));

    return redirect()->route('courses.browse', $query ? ['q' => $query] : []);
})->name('search');

Route::get('/forgot-password', function () {
    return view('login');
})->name('password.request');

Route::get('/monitoring/live', function () {
    $activeUsers = max(1, 
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        0
    );

    $stats = [
        'active_users' => $activeUsers,
        'events_today' => 18,
        'enrollments_today' => 6,
        'badges_today' => 4,
    ];

    $activity = [
        ['title' => 'New student enrolled', 'detail' => 'Ana Lopez joined the Full-Stack course', 'time' => '2 min ago'],
        ['title' => 'Badge issued', 'detail' => 'Database Master badge awarded', 'time' => '12 min ago'],
        ['title' => 'Course completed', 'detail' => 'Juan Dela Cruz marked a milestone', 'time' => '24 min ago'],
    ];

    return response()->json(compact('stats', 'activity'));
})->name('monitoring.live');

// ══════════════════════════════════════════════════════════════════════════
// ADMIN ROUTES
// ══════════════════════════════════════════════════════════════════════════

Route::middleware(RoleBasedAccess::class.':admin')->group(function () {

// Admin Main Dashboard — accessible at /Admin-dashboard
Route::get('/Admin-dashboard', function () {
    return view('Admin_Main_Home', [
        'stats' => [
            'total_students'    => 402,
            'badges_issued'     => 217,
            'course_score_avg'  => 99.7,
            'students_enrolled' => 392,
        ],
        'activeCourses' => collect([
            (object) ['title' => 'Introduction to Artificial Intelligence', 'meta' => '48 Students · 4 Faculty', 'thumbnail_url' => null, 'percent' => 62],
            (object) ['title' => 'Database Fundamentals',                   'meta' => '63 Students · 2 Faculty', 'thumbnail_url' => null, 'percent' => 70],
            (object) ['title' => 'Introduction to Artificial Intelligence', 'meta' => '48 Students · 4 Faculty', 'thumbnail_url' => null, 'percent' => 85],
            (object) ['title' => 'Database Fundamentals',                   'meta' => '63 Students · 2 Faculty', 'thumbnail_url' => null, 'percent' => 62],
        ]),
        'recentBadges' => collect([
            (object) ['name' => 'Database Earned', 'earned_count' => 41],
            (object) ['name' => 'AI Pioneer',      'earned_count' => 29],
            (object) ['name' => 'Web Wizard',      'earned_count' => 38],
            (object) ['name' => 'Certified Pro',   'earned_count' => 15],
        ]),
        'enrollmentByCourse' => collect([
            (object) ['label' => 'Database Fund', 'value' => 63, 'percent' => 100],
            (object) ['label' => 'Web Dev Boot',  'value' => 57, 'percent' => 90],
            (object) ['label' => 'Intro to AI',   'value' => 48, 'percent' => 76],
            (object) ['label' => 'ML Essentials', 'value' => 34, 'percent' => 54],
        ]),
        'completionRate' => collect([
            (object) ['label' => 'Database Fund', 'value' => 91, 'percent' => 91],
            (object) ['label' => 'Web Dev Boot',  'value' => 84, 'percent' => 84],
            (object) ['label' => 'Intro to AI',   'value' => 77, 'percent' => 77],
            (object) ['label' => 'ML Essentials', 'value' => 62, 'percent' => 62],
        ]),
    ]);
})->name('admin.dashboard');

Route::get('/Admin-profile', function () {
    $defaults = [
        'name' => 'Admin User',
        'role' => 'Administrator',
        'phone' => null,
        'email' => 'admin@example.com',
        'location' => null,
        'about' => 'Administrator of the UPSKILL platform.',
        'bio' => 'Oversees course management, student progress, analytics, and badge issuance.',
        'avatar_url' => null,
    ];

    $user = (object) array_merge($defaults, session('admin_profile', []));

    return view('Admin_Profile', compact('user'));
})->name('admin.profile');

Route::patch('/Admin-profile', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name' => 'required|string|max:100',
        'email' => 'required|email|max:120',
        'phone' => 'nullable|string|max:40',
        'location' => 'nullable|string|max:120',
        'role' => 'nullable|string|max:80',
        'about' => 'nullable|string|max:600',
        'bio' => 'nullable|string|max:600',
    ]);

    session(['admin_profile' => $data]);

    return redirect()->route('admin.profile')->with('success', 'Admin profile updated successfully.');
})->name('admin.profile.update');

// Admin User Management — accessible at /Admin-usermanagement
Route::get('/Admin-usermanagement', function (\Illuminate\Http\Request $request) {
    $query = trim((string) $request->input('q', ''));
    $sort = $request->input('sort', 'created_at');
    $direction = $request->input('direction', 'desc');

    $usersQuery = \App\Models\User::query()
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
        'users' => $usersQuery->get(),
        'q' => $query,
        'sort' => $sort,
        'direction' => $direction,
    ]);
})->name('admin.usermanagement');

Route::post('/Admin-usermanagement/users', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'username' => ['required', 'string', 'max:255', 'unique:users'],
        'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        'role_id' => ['required', 'integer', 'in:1,2,3'],
    ]);

    $userCodeService = app(\App\Services\UserCodeService::class);
    $generatedCode = $userCodeService->generateForRole((int) $validated['role_id']);

    $user = \App\Models\User::create([
        'first_name' => $validated['first_name'],
        'last_name' => $validated['last_name'],
        'email' => $validated['email'],
        'username' => $validated['username'],
        'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        'role_id' => (int) $validated['role_id'],
        'student_id' => $generatedCode,
        'user_code' => $generatedCode,
        'is_active' => true,
    ]);

    return redirect()->route('admin.usermanagement')->with('success', 'User created successfully.');
})->name('admin.users.store');

// ══════════════════════════════════════════════════════════════════════════
// Admin › Courses & Badges  —  standalone admin management page
// URL: /Admin-courses      Route name: admin.courses
// Self-contained: it does NOT link to, share with, or redirect into any
// student view. This is the admin surface for managing courses & badges.
// View: resources/views/Admin_Management_Course_&_Badges.blade.php
// Card fields: id, title, students, faculty, badge, percent, selected
// ══════════════════════════════════════════════════════════════════════════
Route::get('/Admin-courses', function () {
    return view('Admin_Management_Course_&_Badges', [
        'courses' => collect([
            (object) ['id' => 1, 'title' => 'Introduction to Artificial Intelligence', 'students' => 48, 'faculty' => 4, 'badge' => 'AI Pioneer', 'percent' => 84],
            (object) ['id' => 2, 'title' => 'Database Fundamentals',                    'students' => 48, 'faculty' => 4, 'badge' => 'AI Pioneer', 'percent' => 91],
            (object) ['id' => 3, 'title' => 'Web Development',                           'students' => 48, 'faculty' => 4, 'badge' => 'AI Pioneer', 'percent' => 84],
            (object) ['id' => 4, 'title' => 'Machine Learning Essentials',              'students' => 48, 'faculty' => 4, 'badge' => 'AI Pioneer', 'percent' => 91],
        ]),
    ]);
})->name('admin.courses');

// ══════════════════════════════════════════════════════════════════════════
// Admin › Analytics › Report  —  standalone admin analytics page
// URL: /Admin-report       Route name: admin.report
// Self-contained: it does NOT link to, share with, or redirect into any
// student view. This is the admin analytics / reporting surface.
// View: resources/views/Admin_Analytics_Report.blade.php
// ══════════════════════════════════════════════════════════════════════════
Route::get('/Admin-report', function () {
    $stats = [
        'total_students' => (int) \App\Models\User::query()->where('role_id', 3)->count(),
        'badges_issued' => (int) \Illuminate\Support\Facades\DB::table('user_badges')->count(),
        'faculty_total' => (int) \App\Models\User::query()->where('role_id', 2)->count(),
        'course_score_avg' => round((float) \Illuminate\Support\Facades\DB::table('courses')->avg('passing_score'), 2),
    ];

    return view('Admin_Analytics_Report', [
        'stats' => $stats,
        // enrollment: bar width is value ÷ highest value (63) × 100
        'enrollmentByCourse' => collect([
            (object) ['label' => 'Database Fund', 'value' => 63, 'percent' => 100],
            (object) ['label' => 'Web Dev Boot',  'value' => 57, 'percent' => 90],
            (object) ['label' => 'Intro to AI',   'value' => 48, 'percent' => 76],
            (object) ['label' => 'ML Essentials', 'value' => 34, 'percent' => 54],
        ]),
        // completion: bar width equals the percentage value
        'completionRate' => collect([
            (object) ['label' => 'Database Fund', 'value' => 91, 'percent' => 91],
            (object) ['label' => 'Web Dev Boot',  'value' => 84, 'percent' => 84],
            (object) ['label' => 'Intro to AI',   'value' => 77, 'percent' => 77],
            (object) ['label' => 'ML Essentials', 'value' => 62, 'percent' => 62],
        ]),
    ]);
})->name('admin.report');

});

// --- TEMPORARY stubs (remove once real routes exist) ---
foreach ([
    'home',
    'profile.edit',
    'notifications.index', 'search',
] as $name) {
    if (! Route::has($name)) {
        Route::get('/_stub/'.$name, fn () => '')->name($name);
    }
}

// Student "Courses" convenience route → student Browse Courses (student side only)
Route::middleware(RoleBasedAccess::class.':student')->group(function () {
Route::get('/courses', fn() => redirect()->route('courses.browse'))->name('courses.index');

Route::get('/preview-dashboard', function () {
    return view('Student_dashboard', [
        'user' => (object) ['name' => 'Ana'],
        'stats' => [
            'active_courses' => 2,
            'completed'      => 1,
            'badges_earned'  => 3,
            'certificates'   => 1,
        ],
        'courses' => collect([
            (object) ['id' => 1, 'title' => 'Full-Stack Web Dev with Laravel', 'category' => null, 'thumbnail_url' => null, 'progress_percent' => 0],
            (object) ['id' => 2, 'title' => 'Introduction to AI', 'category' => 'Artificial Intelligence', 'thumbnail_url' => null, 'progress_percent' => 0],
        ]),
        'progress' => [],
        'badges'   => [],
    ]);
})->name('dashboard');

// --- Browse Courses ---
Route::get('/courses/browse', function () {
    return view('Student_browse_Courses', [
        'user' => (object) ['name' => 'Ana'],
        'courses' => collect([
            (object) [
                'id' => 1, 'title' => 'Full - Stack Web Development with Laravel',
                'description'   => 'Master modern web development using Laravel framework, MySQL and Blade templating. Build real-world applications from scratch.',
                'category'      => 'Web Development', 'level' => 'Intermediate',
                'instructor'    => 'Prof. Juan Dela Cruz', 'duration' => '40h',
                'lessons_count' => 4, 'thumbnail_url' => null,
            ],
            (object) [
                'id' => 2, 'title' => 'Computer Networking Fundamentals',
                'description'   => 'Master modern web development using Laravel framework, MySQL and Blade templating. Build real-world applications from scratch.',
                'category'      => 'Web Development', 'level' => 'Intermediate',
                'instructor'    => 'Prof. Juan Dela Cruz', 'duration' => '40h',
                'lessons_count' => 4, 'thumbnail_url' => null,
            ],
        ]),
        'categories' => ['Web Development', 'Networking'],
        'levels'     => ['Beginner', 'Intermediate', 'Advanced'],
        'filters'    => ['q' => null, 'category' => null, 'level' => null],
    ]);
})->name('courses.browse');

// ✏️  NEW: Single course description page
Route::get('/courses/{id}', function ($id) {
    // Dummy courses (same objects as browse, with extra fields)
    $allCourses = [
        1 => (object) [
            'id'             => 1,
            'title'          => 'Full - Stack Web Development with Laravel',
            'description'    => 'Explore the fundamentals of Artificial Intelligence, machine learning algorithms and their real-world applications.',
            'category'       => 'Web Development',
            'level'          => 'Intermediate',
            'is_featured'    => true,
            'instructor'     => 'Prof. Juan Dela Cruz',
            'duration'       => '40h',
            'lessons_count'  => 2,
            'enrolled_count' => 4,
            'thumbnail_url'  => null,
            'passing_score'  => 75,
            'skills'         => ['Laravel', 'PHP', 'MySQL', 'MVC'],
            'objectives'     => [
                'Build Full-web Applications',
                'Understand MVC Architecture',
                'Master Laravel Eloquent ORM',
                'Deploy applications in production',
            ],
        ],
        2 => (object) [
            'id'             => 2,
            'title'          => 'Computer Networking Fundamentals',
            'description'    => 'Learn the core concepts of computer networking, protocols, and network design.',
            'category'       => 'Networking',
            'level'          => 'Beginner',
            'is_featured'    => false,
            'instructor'     => 'Prof. Juan Dela Cruz',
            'duration'       => '30h',
            'lessons_count'  => 3,
            'enrolled_count' => 10,
            'thumbnail_url'  => null,
            'passing_score'  => 75,
            'skills'         => ['TCP/IP', 'DNS', 'Routing', 'Switching'],
            'objectives'     => [
                'Understand OSI and TCP/IP models',
                'Configure basic network devices',
                'Troubleshoot common network issues',
            ],
        ],
    ];

    $course = $allCourses[$id] ?? $allCourses[1]; // fallback to course 1

    return view('Student_Course_Description', [
        'user'              => (object) ['name' => 'Ana'],
        'course'            => $course,
        'instructor_detail' => (object) [
            'name'       => 'Prof. Juan Dela Cruz',
            'department' => 'College of Information Technology',
            'bio'        => 'Senior Faculty Member specializing in web development using Laravel.',
            'avatar_url' => null,
        ],
        'modules' => collect([
            (object) ['title' => 'Database & Eloquent ORM',   'description' => 'Master database interactions with Eloquent', 'type' => 'Test',  'duration' => '20m'],
            (object) ['title' => 'Migrations and Schema',     'description' => 'Video: 15m',                                 'type' => 'Video', 'duration' => '20m'],
            (object) ['title' => 'Eloquent Relationships',    'description' => 'Test: 25m',                                  'type' => 'Test',  'duration' => '25m'],
        ]),
        'quiz' => (object) [
            'id'              => 1,
            'title'           => 'Database & Eloquent ORM',
            'questions_count' => 3,
            'passing_score'   => 75,
        ],
        'is_enrolled'      => false,   // false = show "Enroll Now" button
        'progress_percent' => 0,
    ]);
})->name('courses.show');

// Enroll POST → redirect to the enrollment/learning page
Route::post('/courses/{id}/enroll', function ($id) {
    // TODO: real enrollment logic (e.g. DB insert) goes here
    return redirect()->route('courses.learn', $id);
})->name('courses.enroll');

// ── Course Enrollment / Learning Page ──────────────────────────────────────
Route::get('/courses/{id}/learn', function ($id) {

    // Dummy modules — each has an id, title, lessons[], and optional quiz
    $modules = collect([
        (object) [
            'id'      => 1,
            'title'   => 'Laravel Fundamentals',
            'lessons' => collect([
                (object) ['id' => 1, 'title' => 'Introduction to Laravel',  'type' => 'Video', 'duration' => '15m', 'thumbnail_url' => null],
                (object) ['id' => 2, 'title' => 'Routing and Controllers',  'type' => 'Text',  'duration' => '15m', 'thumbnail_url' => null],
                (object) ['id' => 3, 'title' => 'Blade Templates',          'type' => 'Text',  'duration' => '15m', 'thumbnail_url' => null],
            ]),
            'quiz' => (object) [
                'id'              => 1,
                'title'           => 'Laravel Fundamentals Quiz',
                'questions_count' => 3,
                'passing_score'   => 75,
            ],
        ],
        (object) [
            'id'      => 2,
            'title'   => 'Database & Eloquent ORM',
            'lessons' => collect([
                (object) ['id' => 4, 'title' => 'Database Migrations',       'type' => 'Video', 'duration' => '20m', 'thumbnail_url' => null],
                (object) ['id' => 5, 'title' => 'Eloquent Relationships',    'type' => 'Text',  'duration' => '25m', 'thumbnail_url' => null],
            ]),
            'quiz' => (object) [
                'id'              => 2,
                'title'           => 'Database & Eloquent ORM Quiz',
                'questions_count' => 3,
                'passing_score'   => 75,
            ],
        ],
    ]);

    $allCourses = [
        1 => (object) ['id' => 1, 'title' => 'Full - Stack Web Development with Laravel', 'category' => 'Web Development', 'thumbnail_url' => null, 'progress_percent' => 0],
        2 => (object) ['id' => 2, 'title' => 'Computer Networking Fundamentals',          'category' => 'Networking',       'thumbnail_url' => null, 'progress_percent' => 0],
    ];

    $course        = $allCourses[$id] ?? $allCourses[1];
    $currentLesson = $modules->first()?->lessons->first();   // start at lesson 1
    $currentModule = $modules->first();
    $totalLessons  = $modules->sum(fn($m) => $m->lessons->count());

    return view('Student_Course_Enrollment', [
        'user'             => (object) ['name' => 'Ana'],
        'course'           => $course,
        'modules'          => $modules,
        'current_lesson'   => $currentLesson,
        'current_module'   => $currentModule,
        'total_lessons'    => $totalLessons,
        'badge_count'      => 1,
        'progress_percent' => 0,
    ]);
})->name('courses.learn');

// Individual lesson view (stub — reuses enrollment page for now)
Route::get('/courses/{courseId}/lesson/{lessonId}', function ($courseId, $lessonId) {
    return redirect()->route('courses.learn', $courseId);
})->name('courses.lesson');

// Quiz stub
Route::get('/quiz/{id}', fn($id) => redirect()->route('dashboard'))->name('quiz.show');

Route::get('/badges', function () {
    return view('Student_MyBadges', [
        'user' => (object) ['name' => 'Ana'],
        'stats' => [
            'active_courses' => 2,
            'completed'      => 1,
            'badges_earned'  => 3,
            'certificates'   => 1,
        ],
        'badges' => collect([
            (object) ['name' => 'Database Master', 'description' => 'Completed Database and Eloquent Model', 'icon_url' => null, 'earned_at' => now()->subHours(17)],
            (object) ['name' => 'Database Master', 'description' => 'Completed Database and Eloquent Model', 'icon_url' => null, 'earned_at' => now()->subHours(17)],
            (object) ['name' => 'Database Master', 'description' => 'Completed Database and Eloquent Model', 'icon_url' => null, 'earned_at' => now()->subHours(17)],
            (object) ['name' => 'Database Master', 'description' => 'Completed Database and Eloquent Model', 'icon_url' => null, 'earned_at' => now()->subHours(17)],
        ]),
    ]);
})->name('badges.index');

Route::get('/certificates', function () {
    return view('Student_Certificates', [
        'user' => (object) ['name' => 'Ana'],
        'certificates' => collect([
            (object) ['title' => 'AWS Certified Solutions Architect',              'icon_url' => null, 'view_url' => '#', 'download_url' => '#'],
            (object) ['title' => 'CompTIA A+',                                     'icon_url' => null, 'view_url' => '#', 'download_url' => '#'],
            (object) ['title' => 'CompTIA Network+',                               'icon_url' => null, 'view_url' => '#', 'download_url' => '#'],
            (object) ['title' => 'CompTIA Security+',                              'icon_url' => null, 'view_url' => '#', 'download_url' => '#'],
            (object) ['title' => 'Cisco CCNA',                                     'icon_url' => null, 'view_url' => '#', 'download_url' => '#'],
            (object) ['title' => 'Microsoft Certified: Azure Fundamentals',        'icon_url' => null, 'view_url' => '#', 'download_url' => '#'],
            (object) ['title' => 'Oracle Certified Professional: Java SE',         'icon_url' => null, 'view_url' => '#', 'download_url' => '#'],
            (object) ['title' => 'Google IT Support Professional',                 'icon_url' => null, 'view_url' => '#', 'download_url' => '#'],
            (object) ['title' => 'Certified Kubernetes Administrator',             'icon_url' => null, 'view_url' => '#', 'download_url' => '#'],
            (object) ['title' => 'PMP - Project Management Professional',          'icon_url' => null, 'view_url' => '#', 'download_url' => '#'],
            (object) ['title' => 'Certified Ethical Hacker (CEH)',                 'icon_url' => null, 'view_url' => '#', 'download_url' => '#'],
            (object) ['title' => 'Microsoft Certified: Azure Developer Associate', 'icon_url' => null, 'view_url' => '#', 'download_url' => '#'],
        ]),
    ]);
})->name('certificates.index');

// ── Profile ────────────────────────────────────────────────────────────────

Route::get('/profile', function () {

    // ── Default / seed data ───────────────────────────────────────────────
    $defaults = [
        'name'          => 'Ana Maria',
        'role'          => 'Student',
        'phone'         => '09345678912',
        'email'         => 'Ana123@gmail.com',
        'joined_at'     => \Carbon\Carbon::parse('2021-01-15'),   // always Carbon
        'location'      => 'San, Juan, Pangasinan',
        'avatar_url'    => null,
        'about'         => 'Passionate about Web Development and App Development. Problem Solving Love to Learn new Skills and More',
        'date_of_birth' => 'October 12, 2003',
        'gender'        => 'Male',
        'education'     => 'BS Information Technology',
        'bio'           => 'Photo Editing Skilled. Frontend Web Designer for Wordpress. Can Edit Multiple Frames in Just 1 Hour',
        'language'      => 'English',
        'timezone'      => 'Asia/Manila',
    ];

    // ── Merge with any session-persisted edits ────────────────────────────
    $saved  = session('profile_data', []);
    $merged = array_merge($defaults, $saved);

    // joined_at must always be a Carbon instance (never from form input)
    $merged['joined_at'] = $defaults['joined_at'];

    // Normalise date_of_birth to a human-readable format for display
    if (!empty($merged['date_of_birth'])) {
        try {
            $merged['date_of_birth'] = \Carbon\Carbon::parse($merged['date_of_birth'])->format('F j, Y');
        } catch (\Throwable $e) { /* keep as-is if it can't be parsed */ }
    }

    return view('Student_Profile', [
        'user'         => (object) $merged,
        'progress'     => ['completed' => 12, 'total' => 20],
        'achievements' => [],
        'activities'   => [],
    ]);
})->name('profile.show');

// ✅ Handle Edit Profile form — persists to session until DB is wired up
Route::patch('/profile', function (\Illuminate\Http\Request $request) {

    // Start from what was already in session so untouched fields are kept
    $existing = session('profile_data', []);

    // Overwrite with every field the form sent
    $updated = array_merge($existing, $request->only([
        'name', 'role', 'phone', 'location',
        'about', 'date_of_birth', 'gender', 'education', 'bio',
        'email', 'language', 'timezone',
    ]));

    // ── Avatar ─────────────────────────────────────────────────────────────
    // JS resized the image client-side and stored it as a base64 data-URL in
    // the hidden "avatar_base64" field — so no PHP file-upload limits apply.
    if ($request->filled('avatar_base64')) {
        $dataUrl = $request->input('avatar_base64');
        // Basic sanity check: must be an image data-URL
        if (str_starts_with($dataUrl, 'data:image/')) {
            $updated['avatar_url'] = $dataUrl;
        }
    }

    // Persist the full updated profile to session
    session(['profile_data' => $updated]);

    // If the user is authenticated, persist these fields to their user record
    if (\Illuminate\Support\Facades\Auth::check()) {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (array_key_exists('name', $updated)) {
            $user->first_name = $updated['name'];
        }
        foreach (['gender','date_of_birth','age','phone','school_enrolled','hobby','address','email','bio','location'] as $k) {
            if (array_key_exists($k, $updated)) {
                $user->{$k} = $updated[$k];
            }
        }
        // mark profile completed flag if available
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'profile_completed')) {
            $user->profile_completed = true;
        }
        $user->save();

        // Clear session-stored profile data now that it's persisted
        session()->forget('profile_data');
    }

    return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');

    // ── TODO (when DB is ready) ────────────────────────────────────────────
    // Replace the two lines above with:
    //   $user = \Illuminate\Support\Facades\Auth::user();
    //   $user->fill($updated)->save();
    //   return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
})->name('profile.update');

// ──────────────────────────────────────────────────────────────────────────

Route::get('/pathways', function () {
    return view('Student_MyPathways', [
        'user' => (object) ['name' => 'Ana'],
        'pathway' => [
            'steps' => [
                ['label' => 'Goal 1', 'title' => 'Web Development', 'color' => '#2DD4CF', 'status' => 'completed'],
                ['label' => 'Goal 2', 'title' => 'Laravel',         'color' => '#D8C84A', 'status' => 'completed'],
                ['label' => 'Goal 3', 'title' => 'SQL',             'color' => '#E5483D', 'status' => 'current'],
                ['label' => 'Goal 4', 'title' => 'Locked',          'color' => '#9CA3AF', 'status' => 'locked'],
            ],
            'destination'               => 'Full Stack Web Developer',
            'destination_color'         => '#5FD93D',
            'connector_to_destination'  => '#2563EB',
        ],
        'recommendations' => [
            ['title' => 'Take Blade Courses', 'completion' => 0],
            ['title' => 'Take SQL Courses',   'completion' => 0],
            ['title' => 'Networking Course',  'completion' => 0],
            ['title' => 'HTML & CSS',         'completion' => 0],
        ],
        'desiredPathway' => [
            'title'                  => 'Data Analyst',
            'current_competencies'   => ['Networking Course', 'HTML & CSS'],
            'missing_competencies'   => ['Python', 'Statistics'],
        ],
        'readinessPercent' => 60,
        'readinessLabel'   => 'Data Analytics',
    ]);
})->name('pathways.index');

Route::get('/analytics', function () {
    return view('Student_Analytics', [
        'user' => (object) ['name' => 'Ana'],
        'stats' => [
            'active_courses'  => 2,
            'badges_earned'   => 4,
            'score_avg'       => 99.7,
            'hours_enrolled'  => 14,
        ],
        'activeCourses' => collect([
            (object) ['title' => 'Introduction to Artificial Intelligence', 'meta' => '35 Students · 4 Faculty', 'thumbnail_url' => null, 'percent' => 62],
            (object) ['title' => 'Database Fundamentals',                   'meta' => '45 Students · 2 Faculty', 'thumbnail_url' => null, 'percent' => 70],
            (object) ['title' => 'Introduction to Artificial Intelligence', 'meta' => '38 Students · 4 Faculty', 'thumbnail_url' => null, 'percent' => 85],
            (object) ['title' => 'Database Fundamentals',                   'meta' => '43 Students · 2 Faculty', 'thumbnail_url' => null, 'percent' => 62],
        ]),
        'recentBadges' => collect([
            (object) ['name' => 'Database Earned', 'earned_count' => 61],
            (object) ['name' => 'AI Pioneer',      'earned_count' => 28],
            (object) ['name' => 'Web Wizard',      'earned_count' => 38],
            (object) ['name' => 'Certified Pro',   'earned_count' => 15],
        ]),
        'enrollmentByCourse' => collect([
            (object) ['label' => 'Database Fund', 'value' => 63, 'percent' => 63],
            (object) ['label' => 'Web Dev Boot',  'value' => 57, 'percent' => 57],
            (object) ['label' => 'Intro to AI',   'value' => 48, 'percent' => 48],
            (object) ['label' => 'ML Essentials', 'value' => 34, 'percent' => 34],
        ]),
        'completionRate' => collect([
            (object) ['label' => 'Database Fund', 'value' => 91, 'percent' => 91],
            (object) ['label' => 'Web Dev Boot',  'value' => 84, 'percent' => 84],
            (object) ['label' => 'Intro to AI',   'value' => 77, 'percent' => 77],
            (object) ['label' => 'ML Essentials', 'value' => 62, 'percent' => 62],
        ]),
    ]);
})->name('analytics.index');

});
// ══════════════════════════════════════════════════════════════════════════
// FACULTY ROUTES
// ══════════════════════════════════════════════════════════════════════════

Route::middleware(RoleBasedAccess::class.':faculty')->group(function () {

// Faculty Dashboard — accessible at /Faculty-dashboard
// ⚠ STANDALONE PAGE: not connected to any other page or route.
//   Every button/link inside the view is a placeholder (href="#" or a
//   plain <button>) — nothing here links into student or admin pages.
// View: resources/views/Faculty_dashboard.blade.php
// ── Faculty profile user (shared by ALL faculty pages) ────────────────────
// One source of truth so the topbar avatar / name stay in sync everywhere
// after edits made in the Edit Profile drawer (session 'faculty_profile').
// Replace with the authenticated user once auth + DB are wired up.
if (! function_exists('facultyProfileUser')) {
    function facultyProfileUser(): object
    {
        $defaults = [
            'name'           => 'Prof. Juan Dela Cruz',
            'role'           => 'Faculty',
            'phone'          => '09345678912',
            'email'          => 'juandelacruz@gmail.com',
            'joined'         => 'Jan 15, 2021',
            'location'       => 'San Juan, Pangasinan',
            'birth_date_raw' => '2003-10-12',
            'gender'         => 'Male',
            'education'      => 'BS Information Technology',
            'bio'            => 'Photo Editing Skilled. Frontend Web Designer for Wordpress. Can Edit Multiple Frames in Just 1 Hour.',
            'about'          => 'Passionate about Web Developing and App Developing. Problem Solving. Love to Learn new Skills and More.',
            'language'       => 'English',
            'timezone'       => '(GMT + 8:00) Asia/Manila',
            'avatar_url'     => null,
        ];
        $profile = array_merge($defaults, session('faculty_profile', []));
        $profile['birth_date'] = \Carbon\Carbon::parse($profile['birth_date_raw'])->format('F j, Y');

        return (object) $profile;
    }
}

Route::get('/Faculty-dashboard', function () {
    return view('Faculty_dashboard', [
        'user' => facultyProfileUser(),
        'stats' => [
            'total_courses'  => 5,
            'published'      => 3,
            'total_students' => 5,
            'enrollments'    => 7,
        ],
        'courses' => collect([
            (object) [
                'title'          => 'Full - Stack Web Development with Laravel',
                'status'         => 'Published',
                'students_count' => 4,
                'modules_count'  => 2,
                'thumbnail_url'  => null,
            ],
            (object) [
                'title'          => 'Introduction to Computing',
                'status'         => 'Draft',
                'students_count' => 0,
                'modules_count'  => 1,
                'thumbnail_url'  => null,
            ],
        ]),
    ]);
})->name('faculty.dashboard');

// ── Faculty › My Profile — accessible at /Faculty-profile ─────────────────
// ⚠ STANDALONE within the faculty side — reached ONLY from the sidebar
//   "Profile" link / topbar avatar on the Faculty Dashboard, My Courses and
//   Create Courses pages. All buttons on the page itself (Change email /
//   password, Filter, selects) are intentionally non-functioning for now.
// View: resources/views/Faculty_Profile.blade.php
Route::get('/Faculty-profile', function () {
    return view('Faculty_Profile', [
        'user' => facultyProfileUser(),
        'languages' => ['English', 'Filipino'],
        'timezones' => [
            '(GMT + 8:00) Asia/Manila',
            '(GMT + 5:30) Asia/Kolkata',
            '(GMT + 0:00) UTC',
        ],
        // Last 6 months — teaching performance (%)
        'performance' => [
            ['label' => 'Jan', 'percent' => 58],
            ['label' => 'Feb', 'percent' => 66],
            ['label' => 'Mar', 'percent' => 78],
            ['label' => 'Apr', 'percent' => 70],
            ['label' => 'May', 'percent' => 74],
            ['label' => 'Jun', 'percent' => 86],
        ],
        // This week — hours of activity per day
        'activity' => [
            ['label' => 'Mon', 'hours' => 5],
            ['label' => 'Tue', 'hours' => 6],
            ['label' => 'Wed', 'hours' => 4],
            ['label' => 'Thu', 'hours' => 7],
            ['label' => 'Fri', 'hours' => 8],
            ['label' => 'Sat', 'hours' => 3],
            ['label' => 'Sun', 'hours' => 2],
        ],
        // Course statistics table — the SAME existing faculty courses shown
        // on the Dashboard / My Courses pages (seed + session-created), with
        // no students enrolled yet: 0 students, no ratings, 0% completion.
        'profileCourses' => collect(facultyAllCourses())->values()->map(function ($course) {
            return (object) [
                'title'         => $course->title,
                'category'      => $course->level ?? 'Course',
                'students'      => 0,
                'rating'        => null,
                'completion'    => 0,
                'earnings'      => '$0',
                'status'        => $course->status ?? 'Draft',
                'thumbnail_url' => $course->thumbnail_url ?? null,
            ];
        }),
    ]);
})->name('faculty.profile');

// ✅ Handle the Edit Profile drawer — validates and persists the whole
//    profile (personal info, about me, account settings, avatar, password)
//    to the session (key 'faculty_profile') until the DB is wired up,
//    then redirects back to the profile with a success toast.
Route::patch('/Faculty-profile', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name'           => 'required|string|max:100',
        'phone'          => 'nullable|string|max:30',
        'location'       => 'nullable|string|max:120',
        'role'           => 'nullable|string|max:60',
        'about'          => 'nullable|string|max:600',
        'date_of_birth'  => 'nullable|date',
        'gender'         => 'nullable|string|max:30',
        'education'      => 'nullable|string|max:120',
        'bio'            => 'nullable|string|max:600',
        'email'          => 'required|email|max:120',
        'current_password'      => 'nullable|string',
        'password'              => 'nullable|string|min:8|confirmed',
        'language'       => 'nullable|string|max:40',
        'timezone'       => 'nullable|string|max:60',
        'avatar_base64'  => 'nullable|string',
    ]);

    $profile = session('faculty_profile', []);

    // Password change (optional — leave blank to keep current password).
    // A password saved earlier in this session must be confirmed first.
    if (! empty($data['password'])) {
        $storedHash = $profile['password_hash'] ?? null;
        if ($storedHash && ! \Illuminate\Support\Facades\Hash::check($data['current_password'] ?? '', $storedHash)) {
            return back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }
        $profile['password_hash'] = \Illuminate\Support\Facades\Hash::make($data['password']);
    }

    // Text fields — only overwrite with non-null values
    foreach (['name', 'phone', 'location', 'role', 'about', 'gender', 'education', 'bio', 'email', 'language', 'timezone'] as $field) {
        if (isset($data[$field])) {
            $profile[$field] = $data[$field];
        }
    }

    if (! empty($data['date_of_birth'])) {
        $profile['birth_date_raw'] = $data['date_of_birth'];
    }

    // Avatar arrives as a client-resized base64 data URL (~<50 KB) so it
    // fits comfortably in the session; works directly in CSS/img.
    if (! empty($data['avatar_base64'])) {
        $profile['avatar_url'] = $data['avatar_base64'];
    }

    session(['faculty_profile' => $profile]);

    return redirect()->route('faculty.profile')->with('success', 'Profile updated successfully!');
})->name('faculty.profile.update');

// ── Faculty › Analytics — accessible at /Faculty-analytics ────────────────
// ⚠ STANDALONE within the faculty side — reached ONLY from the sidebar
//   "Analytics" link on the Faculty Dashboard, My Courses, Create Courses
//   and Profile pages. Timeline filters are non-functioning for now.
//   Replace the dummy numbers below with real queries later.
// View: resources/views/Faculty_Analytics.blade.php
Route::get('/Faculty-analytics', function () {
    return view('Faculty_Analytics', [
        'user'            => facultyProfileUser(),
        'onlineNow'       => 0,
        'activeThisMonth' => 26,
        'stats' => [
            'total_learners' => 28,
            'certificates'   => 18,
            'lessons_done'   => 582,
        ],
        // All-time academy statistics (line/area chart)
        'academyStats' => [
            ['label' => "Aug 1,\n2025", 'value' => 9],
            ['label' => "Sep 1,\n2025", 'value' => 9],
            ['label' => "Oct 1,\n2025", 'value' => 9],
            ['label' => "Nov 1,\n2025", 'value' => 9],
            ['label' => "Dec 1,\n2025", 'value' => 10],
            ['label' => "Jan 1,\n2026", 'value' => 10],
            ['label' => "Feb 1,\n2026", 'value' => 11],
            ['label' => "Mar 1,\n2026", 'value' => 12],
            ['label' => "Apr 1,\n2026", 'value' => 15],
            ['label' => "May 1,\n2026", 'value' => 19],
            ['label' => "Jun 1,\n2026", 'value' => 23],
            ['label' => "Jul 1,\n2026", 'value' => 28],
        ],
        // Learner success donut breakdown
        'learnerSuccess' => [
            ['label' => 'Completed one or more courses',         'count' => 21],
            ['label' => 'Got through at least half of a course', 'count' => 5],
            ['label' => 'Started a course',                      'count' => 2],
        ],
    ]);
})->name('faculty.analytics');

// ── Faculty dummy course data (shared by the routes below) ────────────────
// One source of truth so the Manage screen always shows the SAME course
// that was clicked on the My Courses list. Courses created via the
// Create Courses form are stored in the session (key 'faculty_courses')
// and merged in. Replace all of this with DB queries later.
if (! function_exists('facultyDummyCourses')) {
    function facultyDummyCourses(): array
    {
        return [
            1 => (object) [
                'id'             => 1,
                'title'          => 'Introduction to Artificial Intelligence',
                'description'    => 'Explore the Fundamentals of Artificial Intelligence, machine learning algorithms and their Real - world applications',
                'status'         => 'Published',
                'level'          => 'Intermediate',
                'students_count' => 4,
                'modules_count'  => 1,
                'lessons_count'  => 1,
                'thumbnail_url'  => null,
            ],
            2 => (object) [
                'id'             => 2,
                'title'          => 'Introduction to Computing',
                'description'    => 'Explore the Fundamentals of Artificial Intelligence, machine learning algorithms and their Real - world applications',
                'status'         => 'Published',
                'level'          => 'Beginner',
                'students_count' => 4,
                'modules_count'  => 1,
                'lessons_count'  => 1,
                'thumbnail_url'  => null,
            ],
            3 => (object) [
                'id'             => 3,
                'title'          => 'Full - Stack Web Development',
                'description'    => 'Explore the Fundamentals of Artificial Intelligence, machine learning algorithms and their Real - world applications',
                'status'         => 'Published',
                'level'          => 'Intermediate',
                'students_count' => 4,
                'modules_count'  => 1,
                'lessons_count'  => 1,
                'thumbnail_url'  => null,
            ],
            4 => (object) [
                'id'             => 4,
                'title'          => 'Laravel Fundamentals',
                'description'    => 'Explore the Fundamentals of Artificial Intelligence, machine learning algorithms and their Real - world applications',
                'status'         => 'Published',
                'level'          => 'Intermediate',
                'students_count' => 4,
                'modules_count'  => 1,
                'lessons_count'  => 1,
                'thumbnail_url'  => null,
            ],
            5 => (object) [
                'id'             => 5,
                'title'          => 'Introduction to Computing',
                'description'    => 'Explore the Fundamentals of Artificial Intelligence, machine learning algorithms and their Real - world applications',
                'status'         => 'Published',
                'level'          => 'Beginner',
                'students_count' => 4,
                'modules_count'  => 1,
                'lessons_count'  => 1,
                'thumbnail_url'  => null,
            ],
        ];
    }
}

// All faculty courses: dummy seed + any created via the Create Courses form
if (! function_exists('facultyAllCourses')) {
    function facultyAllCourses(): array
    {
        $all = facultyDummyCourses();
        foreach (session('faculty_courses', []) as $id => $data) {
            $all[$id] = (object) $data;
        }
        return $all;
    }
}

// Faculty › My Courses — accessible at /Faculty-mycourses
// ⚠ STANDALONE: only connected within the faculty side. Every other
//   button/link is a placeholder.
// View: resources/views/Faculty_My_Courses.blade.php  (mode: 'list')
Route::get('/Faculty-mycourses', function () {
    return view('Faculty_My_Courses', [
        'mode'    => 'list',
        'user'    => facultyProfileUser(),
        'courses' => collect(facultyAllCourses())->values(),
    ]);
})->name('faculty.courses');

// Faculty › My Courses › Manage — /Faculty-mycourses/manage/{id?}
// Shown after clicking a course's "Manage" button, and right after a new
// course is saved from the Create Courses form. Renders the SAME
// Faculty_My_Courses blade in 'manage' mode (no separate blade file).
// Newly created courses have NO modules / students / quiz data yet, so
// the view shows: "No Students enrolled yet", "No Modules yet".
// ⚠ All buttons/inputs inside are placeholders — not wired up yet.
Route::get('/Faculty-mycourses/manage/{id?}', function ($id = null) {
    $allCourses = facultyAllCourses();
    $course     = $id !== null && isset($allCourses[$id]) ? $allCourses[$id] : $allCourses[1];   // fallback to course 1

    // Seed courses (id 1-5) get the dummy modules/students below;
    // courses created via the form start completely empty.
    $isSeedCourse = array_key_exists($course->id, facultyDummyCourses());

    $modules = $isSeedCourse ? collect([
        (object) [
            'title'    => 'Laravel Fundamentals',
            'subtitle' => 'Core concepts of Laravel Framework',
            'lessons'  => collect([
                (object) ['title' => 'Introduction to Laravel', 'meta' => 'Video · 15m', 'thumbnail_url' => null],
                (object) ['title' => 'Routing and Controllers', 'meta' => 'Text · 15m',  'thumbnail_url' => null],
                (object) ['title' => 'Blade Templates',         'meta' => 'Text · 15m',  'thumbnail_url' => null],
            ]),
            'quiz' => (object) [
                'title'           => 'Laravel Fundamentals Quiz',
                'questions_count' => 3,
                'passing_score'   => 75,
            ],
        ],
        (object) [
            'title'    => 'Database & Eloquent ORM',
            'subtitle' => 'Master database interactions with Eloquent',
            'lessons'  => collect([
                (object) ['title' => 'Migrations and Schema',  'meta' => 'Video · 15m', 'thumbnail_url' => null],
                (object) ['title' => 'Eloquent Relationships', 'meta' => 'Test · 25m',  'thumbnail_url' => null],
            ]),
            'quiz' => (object) [
                'title'           => 'Database & Eloquent ORM',
                'questions_count' => 3,
                'passing_score'   => 75,
            ],
        ],
    ]) : collect();

    $students = $isSeedCourse ? collect([
        (object) ['name' => 'Kurt Palavino', 'student_id' => '22 - LN - 0712', 'status' => 'Active',    'avatar_url' => null],
        (object) ['name' => 'Anna Reyes',    'student_id' => '21 - LN - 0789', 'status' => 'Completed', 'avatar_url' => null],
        (object) ['name' => 'Mike Abdul',    'student_id' => '22 - LN - 9856', 'status' => 'Completed', 'avatar_url' => null],
    ]) : collect();

    $quizAverages = $isSeedCourse ? collect([
        (object) ['title' => 'Database ORM Quiz', 'percent' => 100],
    ]) : collect();

    // Merge in any modules added via the "+ Add Modules" form (session)
    $seedModuleCount = $modules->count();
    foreach (session('faculty_modules', [])[$course->id] ?? [] as $m) {
        $modules->push((object) [
            'title'    => $m['title'],
            'subtitle' => $m['subtitle'],
            'lessons'  => collect(),
            'quiz'     => null,
        ]);
    }

    // Give every module a STABLE position ('idx') and key ('seed-N'/'sess-N').
    // Lessons and quizzes in the session are stored under these positions, so
    // they must never shift — even when a module gets deleted (see below).
    foreach ($modules as $i => $m) {
        $m->idx = $i;
        $m->key = $i < $seedModuleCount ? 'seed-' . $i : 'sess-' . ($i - $seedModuleCount);
    }

    // Tag every seed lesson with a stable key ('seed-N') and hide the ones
    // the user has deleted (tracked in session 'faculty_deleted_lessons').
    $deleted = session('faculty_deleted_lessons', [])[$course->id] ?? [];
    foreach ($modules as $moduleIndex => $module) {
        $module->lessons = $module->lessons
            ->values()
            ->map(function ($l, $i) {
                $l->key = 'seed-' . $i;
                return $l;
            })
            ->reject(fn ($l) => in_array($l->key, $deleted[$moduleIndex] ?? []))
            ->values();
    }

    // Merge in any lessons added via the inline "Add Lesson" form (session),
    // grouped by course id and module position. Each gets a 'sess-N' key.
    foreach (session('faculty_lessons', [])[$course->id] ?? [] as $moduleIndex => $lessons) {
        if (! isset($modules[$moduleIndex])) continue;
        foreach ($lessons as $j => $l) {
            $modules[$moduleIndex]->lessons->push((object) [
                'key'           => 'sess-' . $j,
                'title'         => $l['title'],
                'meta'          => $l['meta'],
                'file_url'      => $l['file_url'] ?? null,
                'thumbnail_url' => isset($l['thumbnail']) && $l['thumbnail'] ? asset($l['thumbnail']) : null,
            ]);
        }
    }

    // Merge in any quizzes added via the inline "Add Quiz" form (session),
    // grouped by course id and module position.
    foreach (session('faculty_quizzes', [])[$course->id] ?? [] as $moduleIndex => $q) {
        if (! isset($modules[$moduleIndex])) continue;
        // A session-saved quiz overrides the seed quiz (edits win)
        $modules[$moduleIndex]->quiz = (object) [
            'title'           => $q['title'],
            'questions_count' => $q['questions_count'],
            'passing_score'   => $q['passing_score'],
        ];
    }

    // Hide modules the user has deleted (tracked in session
    // 'faculty_deleted_modules'). This happens AFTER lessons/quizzes are
    // merged by position, so the remaining modules keep their stable idx.
    $deletedModules = session('faculty_deleted_modules', [])[$course->id] ?? [];
    $modules = $modules->reject(fn ($m) => in_array($m->key, $deletedModules))->values();

    // ?add_lesson={moduleIndex} → show the inline Add Lesson form on that module
    $addLessonIndex = request()->has('add_lesson') ? (int) request('add_lesson') : null;

    return view('Faculty_My_Courses', [
        'mode'           => 'manage',
        'user'           => facultyProfileUser(),
        'course'         => $course,
        'modules'        => $modules,
        'students'       => $students,
        'quizAverages'   => $quizAverages,
        'addLessonIndex' => $addLessonIndex,
    ]);
})->name('faculty.courses.manage');

// Faculty › Create Courses — accessible at /Faculty-createcourse
// ⚠ STANDALONE within the faculty side — reached from the "Create
//   Courses" sidebar links / buttons on the Faculty Dashboard and
//   Faculty My Courses pages. Saving POSTs to faculty.create.store below.
// View: resources/views/Faculty_Create_Courses.blade.php
Route::get('/Faculty-createcourse', function () {
    return view('Faculty_Create_Courses', [
        'user' => facultyProfileUser(),
        'categories' => [
            'Web Development',
            'Artificial Intelligence',
            'Databases',
            'Networking',
            'Computer Fundamentals',
            'Project Management',
        ],
        'programs' => [
            'BS Information Technology',
            'BS Computer Science',
            'BS Information Systems',
        ],
        'terms' => [
            '1st Semester 2026 - 2027',
            '2nd Semester 2026 - 2027',
            'Summer 2027',
        ],
        'levels' => ['Beginner', 'Intermediate', 'Advanced'],
    ]);
})->name('faculty.create');

// ✅ Handle the Create Courses form — persists to session until the DB
//    is wired up, then redirects to the Managing Course screen for the
//    newly created course (shown with its empty state: 0 Students,
//    0 Modules, "No Students enrolled yet", "No Modules yet").
Route::post('/Faculty-createcourse', function (\Illuminate\Http\Request $request) {
    $created = session('faculty_courses', []);

    // New ids start at 100 so they never clash with the dummy seed (1-5)
    $newId = empty($created) ? 100 : (max(array_keys($created)) + 1);

    // "Submit for approval" → Pending · "Draft" → Draft
    $status = $request->input('status') === 'draft' ? 'Draft' : 'Pending';

    $created[$newId] = [
        'id'             => $newId,
        'title'          => trim($request->input('title', '')) ?: 'Untitled Course',
        'description'    => trim($request->input('description', '')),
        'status'         => $status,
        'level'          => $request->input('level') ?: 'Beginner',
        'category'       => $request->input('category'),
        'program'        => $request->input('program'),
        'term'           => $request->input('term'),
        'duration'       => $request->input('duration'),
        'passing_score'  => $request->input('passing_score'),
        'students_count' => 0,
        'modules_count'  => 0,
        'lessons_count'  => 0,
        'thumbnail_url'  => null,
    ];

    session(['faculty_courses' => $created]);

    return redirect()->route('faculty.courses.manage', $newId);

    // ── TODO (when DB is ready) ────────────────────────────────────────
    // Replace the session logic above with a Course::create([...]) call
    // and redirect to the manage route with the new model's id.
})->name('faculty.create.store');

// ✅ Handle the "+ Add Modules" form on the Managing Course screen —
//    persists the module to the session (key 'faculty_modules', grouped
//    by course id) until the DB is wired up, then reloads the same
//    Managing Course screen showing the new module card
//    (with "+ Add Lesson", ✕ and "No quiz yet. / Add Quiz").
Route::post('/Faculty-mycourses/manage/{id}/modules', function (\Illuminate\Http\Request $request, $id) {
    $allModules = session('faculty_modules', []);

    $allModules[(int) $id][] = [
        'title'    => trim($request->input('module_title', '')) ?: 'Untitled Module',
        'subtitle' => trim($request->input('module_description', '')),
    ];

    session(['faculty_modules' => $allModules]);

    return redirect()->route('faculty.courses.manage', $id);

    // ── TODO (when DB is ready) ────────────────────────────────────────
    // Replace the session logic above with a Module::create([...]) call
    // scoped to the course, then redirect back to the manage route.
})->name('faculty.module.store');

// ✅ Handle the inline "Add Lesson" form on the Managing Course screen —
//    persists the lesson to the session (key 'faculty_lessons', grouped
//    by course id + module position) until the DB is wired up, then
//    reloads the same Managing Course screen with the lesson listed
//    inside its module and the form closed.
Route::post('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/lessons', function (\Illuminate\Http\Request $request, $id, $moduleIndex) {
    $allLessons = session('faculty_lessons', []);

    $duration = (int) $request->input('duration', 0);

    // ✅ Handle the uploaded file. There's no type dropdown anymore — the
    //    lesson type is detected from the file's extension. Files are saved
    //    to public/uploads/lessons (no storage:link needed) and only the
    //    path is kept in the session. Unsupported extensions are ignored.
    //    Lessons without a file become Text lessons (using the textarea).
    $type     = 'Text';
    $fileUrl  = null;
    $fileName = null;
    if ($request->hasFile('lesson_file') && $request->file('lesson_file')->isValid()) {
        $file    = $request->file('lesson_file');
        $ext     = strtolower($file->getClientOriginalExtension());
        $typeMap = [
            'pdf'  => 'PDF',
            'mp4'  => 'Video', 'webm' => 'Video', 'ogg' => 'Video',
            'mov'  => 'Video', 'avi'  => 'Video', 'mkv' => 'Video',
            'jpg'  => 'Image', 'jpeg' => 'Image', 'png' => 'Image',
            'gif'  => 'Image', 'webp' => 'Image', 'bmp' => 'Image', 'svg' => 'Image',
            'txt'  => 'Text',
        ];
        if (isset($typeMap[$ext])) {
            $dir = public_path('uploads/lessons');
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $name = uniqid('lesson_') . '.' . $ext;
            $file->move($dir, $name);
            $type     = $typeMap[$ext];
            $fileUrl  = 'uploads/lessons/' . $name;
            $fileName = $file->getClientOriginalName();
        }
    }

    $allLessons[(int) $id][(int) $moduleIndex][] = [
        'title'     => trim($request->input('lesson_title', '')) ?: 'Untitled Lesson',
        'type'      => $type,
        'meta'      => $type . ($duration > 0 ? ' · ' . $duration . 'm' : ''),
        'content'   => trim($request->input('lesson_content', '')),   // Text lessons
        'file_url'  => $fileUrl,
        'file_name' => $fileName,
        // Image lessons use their own upload as the row thumbnail
        'thumbnail' => ($type === 'Image' && $fileUrl) ? $fileUrl : null,
    ];

    session(['faculty_lessons' => $allLessons]);

    return redirect()->route('faculty.courses.manage', $id);

    // ── TODO (when DB is ready) ────────────────────────────────────────
    // Replace the session logic above with a Lesson::create([...]) call
    // (keeping the same file-move logic or switching to the Storage
    // facade) scoped to the module, then redirect back.
})->name('faculty.lesson.store');

// ✅ Handle the inline "Add Quiz" form on the Managing Course screen —
//    persists the quiz to the session (key 'faculty_quizzes', one quiz
//    per module, grouped by course id + module position) until the DB
//    is wired up, then reloads the same Managing Course screen with the
//    quiz shown in the module footer ("0 Questions · Pass X%" + Edit Quiz).
Route::post('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/quiz', function (\Illuminate\Http\Request $request, $id, $moduleIndex) {
    $allQuizzes = session('faculty_quizzes', []);

    $passing = (int) $request->input('passing_score', 0);
    if ($passing < 1 || $passing > 100) $passing = 75;   // sensible default

    // Parse every question card — blank questions (no text) are skipped.
    // Each type stores what its display collects:
    //   Multiple Choice → typed choices + 'correct' radio index
    //   True or False   → fixed ['True','False'] + 'tf_correct' radio index
    //   Identification  → a single typed 'answer'
    $questions = [];
    foreach ($request->input('questions', []) as $qi) {
        $text = trim($qi['text'] ?? '');
        if ($text === '') continue;

        $qType = $qi['type'] ?? 'Multiple Choice';
        if ($qType === 'Identification') {
            $choices = [];
            $correct = null;
            $answer  = trim($qi['answer'] ?? '');
        } elseif ($qType === 'True or False') {
            $choices = ['True', 'False'];
            $correct = $qi['tf_correct'] ?? null;
            $answer  = null;
        } else {
            $choices = array_values(array_filter(array_map('trim', $qi['choices'] ?? [])));
            $correct = $qi['correct'] ?? null;
            $answer  = null;
        }

        $questions[] = [
            'text'    => $text,
            'type'    => $qType,
            'points'  => (int) ($qi['points'] ?? 0),
            'choices' => $choices,
            'correct' => $correct,
            'answer'  => $answer,
        ];
    }

    $allQuizzes[(int) $id][(int) $moduleIndex] = [
        'title'           => trim($request->input('quiz_title', '')) ?: 'Untitled Quiz',
        'passing_score'   => $passing,
        'questions_count' => count($questions),
        // Extra builder fields kept for later (Edit Quiz / DB migration):
        'items'           => (int) $request->input('items', 1),
        'attempts'        => $request->input('attempts'),
        'time_limit'      => (int) $request->input('time_limit', 0),
        'instructions'    => trim($request->input('instructions', '')),
        'questions'       => $questions,
    ];

    session(['faculty_quizzes' => $allQuizzes]);

    return redirect()->route('faculty.courses.manage', $id);

    // ── TODO (when DB is ready) ────────────────────────────────────────
    // Replace the session logic above with Quiz::create([...]) plus
    // Question/Choice records scoped to the module, then redirect back.
})->name('faculty.quiz.store');

// ✅ Handle the lesson ✕ buttons on the Managing Course screen —
//    deletes the lesson, then reloads the same screen.
//    Lesson keys: 'sess-N' = lesson added via the Add Lesson form
//    (removed from session 'faculty_lessons'); 'seed-N' = dummy seed
//    lesson (its key is remembered in session 'faculty_deleted_lessons'
//    so it stays hidden). Replace all of this with a DB delete later.
Route::post('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/lessons/{key}/delete', function ($id, $moduleIndex, $key) {
    $id = (int) $id;
    $moduleIndex = (int) $moduleIndex;

    if (str_starts_with($key, 'sess-')) {
        // Remove a session-added lesson and reindex the rest
        $allLessons = session('faculty_lessons', []);
        $j = (int) substr($key, 5);
        if (isset($allLessons[$id][$moduleIndex][$j])) {
            unset($allLessons[$id][$moduleIndex][$j]);
            $allLessons[$id][$moduleIndex] = array_values($allLessons[$id][$moduleIndex]);
            session(['faculty_lessons' => $allLessons]);
        }
    } elseif (str_starts_with($key, 'seed-')) {
        // Hide a seed lesson by remembering its key
        $deleted = session('faculty_deleted_lessons', []);
        if (! in_array($key, $deleted[$id][$moduleIndex] ?? [])) {
            $deleted[$id][$moduleIndex][] = $key;
            session(['faculty_deleted_lessons' => $deleted]);
        }
    }

    return redirect()->route('faculty.courses.manage', $id);

    // ── TODO (when DB is ready) ────────────────────────────────────────
    // Replace the session logic above with Lesson::destroy($lessonId).
})->name('faculty.lesson.delete');

// ✅ Handle the module ✕ buttons on the Managing Course screen —
//    deletes the whole module (its lessons and quiz disappear with it),
//    then reloads the same screen. Works by remembering the module's key
//    ('seed-N' or 'sess-N') in session 'faculty_deleted_modules'; the
//    module's session lessons/quiz stay stored but are never shown again.
//    Replace all of this with a DB delete later.
Route::post('/Faculty-mycourses/manage/{id}/modules/{key}/delete', function ($id, $key) {
    $id = (int) $id;

    $deleted = session('faculty_deleted_modules', []);
    if (! in_array($key, $deleted[$id] ?? [])) {
        $deleted[$id][] = $key;
        session(['faculty_deleted_modules' => $deleted]);
    }

    return redirect()->route('faculty.courses.manage', $id);

    // ── TODO (when DB is ready) ────────────────────────────────────────
    // Replace the session logic above with Module::destroy($moduleId),
    // cascading to its lessons and quiz.
})->name('faculty.module.delete');

});

// Faculty › Add Quiz builder — /Faculty-mycourses/manage/{id}/modules/{moduleIndex}/quiz/create
// Shown when a module's "Add Quiz" button is clicked. Renders the SAME
// Faculty_My_Courses blade in 'quiz' mode (no separate blade file):
// quiz settings + Question 1 with choices. "Back to Modules" and
// Save Quiz both return to the Managing Course screen.
Route::get('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/quiz/create', function ($id, $moduleIndex) {
    $allCourses = facultyAllCourses();
    $course     = $allCourses[$id] ?? $allCourses[1];
    $moduleIndex = (int) $moduleIndex;

    // Resolve the module's title for the "Add Quiz : <module>" heading.
    // Seed courses have 2 fixed seed modules, then session modules follow.
    $titles = array_key_exists($course->id, facultyDummyCourses())
        ? ['Laravel Fundamentals', 'Database & Eloquent ORM']
        : [];
    foreach (session('faculty_modules', [])[$course->id] ?? [] as $m) {
        $titles[] = $m['title'];
    }

    // Load the existing quiz (if any) so the builder opens PREFILLED for
    // editing. Session-saved quizzes win; seed quizzes are the fallback.
    $quiz = null;
    $sessionQuiz = session('faculty_quizzes', [])[$course->id][$moduleIndex] ?? null;
    if ($sessionQuiz) {
        // Quizzes saved before multi-question support had a single 'question'
        $questions = $sessionQuiz['questions']
            ?? (isset($sessionQuiz['question']) && $sessionQuiz['question'] ? [$sessionQuiz['question']] : []);

        $quiz = (object) [
            'title'         => $sessionQuiz['title'],
            'items'         => $sessionQuiz['items'] ?? null,
            'passing_score' => $sessionQuiz['passing_score'],
            'attempts'      => $sessionQuiz['attempts'] ?? null,
            'time_limit'    => $sessionQuiz['time_limit'] ?? null,
            'instructions'  => $sessionQuiz['instructions'] ?? '',
            'questions'     => $questions,
        ];
    } elseif (array_key_exists($course->id, facultyDummyCourses())) {
        // Seed quizzes on the two seed modules
        $seedQuizzes = [
            0 => ['title' => 'Laravel Fundamentals Quiz', 'passing_score' => 75],
            1 => ['title' => 'Database & Eloquent ORM',   'passing_score' => 75],
        ];
        if (isset($seedQuizzes[$moduleIndex])) {
            $quiz = (object) array_merge($seedQuizzes[$moduleIndex], [
                'items' => null, 'attempts' => null, 'time_limit' => null,
                'instructions' => '', 'questions' => [],
            ]);
        }
    }

    return view('Faculty_My_Courses', [
        'mode'        => 'quiz',
        'user'        => facultyProfileUser(),
        'course'      => $course,
        'moduleIdx'   => $moduleIndex,
        'moduleTitle' => $titles[$moduleIndex] ?? 'Module',
        'quiz'        => $quiz,
    ]);
})->name('faculty.quiz.create');