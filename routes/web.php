<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\StudentController;
use App\Http\Middleware\RoleBasedAccess;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════════════════════
// UPSKILL — route map
//
// Every URL and route NAME is identical to the original file, so all of
// the Blade views (which reference routes by name) work unchanged.
// The only difference: the dummy/session closures have been replaced by
// real controllers backed by the database.
// ══════════════════════════════════════════════════════════════════════════

// ── STUDENT VIEW COMPOSER ─────────────────────────────────────────────────
// Kept from the original routes file: merges any session-stored student
// profile edits into the $user object on Student_* pages. Profile updates
// are now persisted to the database (and the session key is cleared), so
// this simply has nothing to merge during normal operation — but it stays
// as a safety net so the topbar never breaks.
app('view')->composer('Student_*', function (\Illuminate\View\View $view) {
    try {
        $saved = session('profile_data', []);
    } catch (\Throwable $e) {
        return; // session not available yet (e.g. during boot)
    }

    if (empty($saved)) {
        return;
    }

    $data = $view->getData();
    if (! isset($data['user'])) {
        return;
    }

    $user = $data['user'];

    foreach (['name', 'role', 'avatar_url', 'email', 'phone', 'location'] as $field) {
        if (array_key_exists($field, $saved) && ! empty($saved[$field])) {
            $user->$field = $saved[$field];
        }
    }

    $view->with('user', $user);
});

// ── Public pages ──────────────────────────────────────────────────────────

Route::get('/', [PageController::class, 'homepage'])->name('Homepage');

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

// Faculty registration — requires a valid, unused Faculty Code from the Admin
Route::get('/faculty-register', [RegisterController::class, 'showFaculty'])->name('faculty.register');
Route::post('/faculty-register', [RegisterController::class, 'storeFaculty'])->name('faculty.register.store');

// Students directory — now fed real students from the database.
Route::get('/students', [PageController::class, 'students'])->name('students.index');

// Navbar section links → smooth-scroll targets on the homepage.
Route::get('/announcements', fn () => redirect('/#announcements'));
Route::get('/microcredentials', fn () => redirect('/#featured'));

// Public course catalog — "View all Courses" on the homepage.
Route::get('/explore', [PageController::class, 'explore'])->name('explore');

Route::get('/notifications', [PageController::class, 'notifications'])->name('notifications.index');
Route::get('/search', [PageController::class, 'search'])->name('search');
Route::get('/forgot-password', [PageController::class, 'forgotPassword'])->name('password.request');
Route::get('/monitoring/live', [PageController::class, 'monitoringLive'])->name('monitoring.live');

// ══════════════════════════════════════════════════════════════════════════
// ADMIN ROUTES
// ══════════════════════════════════════════════════════════════════════════

Route::middleware(RoleBasedAccess::class . ':admin')->group(function () {
    Route::get('/Admin-dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/Admin-profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::patch('/Admin-profile', [AdminController::class, 'updateProfile'])->name('admin.profile.update');

    Route::get('/Admin-usermanagement', [AdminController::class, 'userManagement'])->name('admin.usermanagement');
    Route::post('/Admin-usermanagement/users', [AdminController::class, 'storeUser'])->name('admin.users.store');

    Route::get('/Admin-facultycodes', [AdminController::class, 'facultyCodes'])->name('admin.facultycodes');
    Route::post('/Admin-facultycodes', [AdminController::class, 'generateFacultyCode'])->name('admin.facultycodes.generate');
    Route::post('/Admin-facultycodes/{id}/delete', [AdminController::class, 'deleteFacultyCode'])
        ->whereNumber('id')->name('admin.facultycodes.delete');

    Route::get('/Admin-courses', [AdminController::class, 'courses'])->name('admin.courses');
    Route::get('/Admin-courses/{id}', [AdminController::class, 'showCourse'])
        ->whereNumber('id')->name('admin.courses.show');
    Route::post('/Admin-courses/{id}/approve', [AdminController::class, 'approveCourse'])
        ->whereNumber('id')->name('admin.courses.approve');
    Route::post('/Admin-courses/{id}/deny', [AdminController::class, 'denyCourse'])
        ->whereNumber('id')->name('admin.courses.deny');
    Route::post('/Admin-courses/{id}/delete', [AdminController::class, 'destroyCourse'])
        ->whereNumber('id')->name('admin.courses.destroy');
    Route::post('/Admin-courses/{id}/publish', [AdminController::class, 'togglePublishCourse'])
        ->whereNumber('id')->name('admin.courses.publish');
    Route::post('/Admin-courses/{id}/feature', [AdminController::class, 'toggleFeatureCourse'])
        ->whereNumber('id')->name('admin.courses.feature');
    Route::get('/Admin-report', [AdminController::class, 'report'])->name('admin.report');
});

// ══════════════════════════════════════════════════════════════════════════
// STUDENT ROUTES
// ══════════════════════════════════════════════════════════════════════════

Route::middleware(RoleBasedAccess::class . ':student')->group(function () {
    Route::get('/courses', fn () => redirect()->route('courses.browse'))->name('courses.index');

    Route::get('/preview-dashboard', [StudentController::class, 'dashboard'])->name('dashboard');

    Route::get('/courses/browse', [StudentController::class, 'browse'])->name('courses.browse');
    Route::get('/courses/{id}', [StudentController::class, 'show'])->whereNumber('id')->name('courses.show');
    Route::post('/courses/{id}/enroll', [StudentController::class, 'enroll'])->whereNumber('id')->name('courses.enroll');
    Route::get('/courses/{id}/learn', [StudentController::class, 'learn'])->whereNumber('id')->name('courses.learn');
    Route::post('/courses/{id}/progress', [StudentController::class, 'saveProgress'])
        ->whereNumber('id')->name('courses.progress');
    Route::post('/courses/{id}/complete', [StudentController::class, 'completeCourse'])
        ->whereNumber('id')->name('courses.complete');
    Route::get('/courses/{courseId}/lesson/{lessonId}', [StudentController::class, 'lesson'])
        ->whereNumber('courseId')->whereNumber('lessonId')->name('courses.lesson');
    Route::get('/quiz/{id}', [StudentController::class, 'quiz'])->whereNumber('id')->name('quiz.show');

    Route::get('/courses/enrolled', [StudentController::class, 'enrolledCourses'])->name('courses.enrolled');
    Route::get('/badges', [StudentController::class, 'badges'])->name('badges.index');
    Route::get('/certificates', [StudentController::class, 'certificates'])->name('certificates.index');

    Route::get('/profile', [StudentController::class, 'profile'])->name('profile.show');
    Route::patch('/profile', [StudentController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/complete', [StudentController::class, 'completeProfile'])->name('profile.complete');

    Route::get('/pathways', [StudentController::class, 'pathways'])->name('pathways.index');
    Route::get('/analytics', [StudentController::class, 'analytics'])->name('analytics.index');
});

// ══════════════════════════════════════════════════════════════════════════
// FACULTY ROUTES
// ══════════════════════════════════════════════════════════════════════════

Route::middleware(RoleBasedAccess::class . ':faculty')->group(function () {
    Route::get('/Faculty-dashboard', [FacultyController::class, 'dashboard'])->name('faculty.dashboard');

    Route::get('/Faculty-profile', [FacultyController::class, 'profile'])->name('faculty.profile');
    Route::patch('/Faculty-profile', [FacultyController::class, 'updateProfile'])->name('faculty.profile.update');

    Route::get('/Faculty-analytics', [FacultyController::class, 'analytics'])->name('faculty.analytics');

    Route::get('/Faculty-mycourses', [FacultyController::class, 'courses'])->name('faculty.courses');
    Route::get('/Faculty-mycourses/manage/{id?}', [FacultyController::class, 'manage'])
        ->whereNumber('id')->name('faculty.courses.manage');

    Route::get('/Faculty-createcourse', [FacultyController::class, 'createForm'])->name('faculty.create');
    Route::post('/Faculty-createcourse', [FacultyController::class, 'createStore'])->name('faculty.create.store');

    Route::post('/Faculty-mycourses/manage/{id}/modules', [FacultyController::class, 'storeModule'])
        ->whereNumber('id')->name('faculty.module.store');
    Route::post('/Faculty-mycourses/manage/{id}/modules/{key}/delete', [FacultyController::class, 'deleteModule'])
        ->whereNumber('id')->name('faculty.module.delete');

    Route::post('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/lessons', [FacultyController::class, 'storeLesson'])
        ->whereNumber('id')->whereNumber('moduleIndex')->name('faculty.lesson.store');
    Route::post('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/lessons/{key}/delete', [FacultyController::class, 'deleteLesson'])
        ->whereNumber('id')->whereNumber('moduleIndex')->name('faculty.lesson.delete');

    Route::get('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/quiz/create', [FacultyController::class, 'quizCreate'])
        ->whereNumber('id')->whereNumber('moduleIndex')->name('faculty.quiz.create');
    Route::post('/Faculty-mycourses/manage/{id}/modules/{moduleIndex}/quiz', [FacultyController::class, 'storeQuiz'])
        ->whereNumber('id')->whereNumber('moduleIndex')->name('faculty.quiz.store');
});
