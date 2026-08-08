<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Announcement;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\UserBadge;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * PageController — public / shared pages: Homepage, the Students
 * directory, the notifications feed, global search, and the JSON
 * endpoint that powers the live-monitoring widgets.
 */
class PageController extends Controller
{
    /**
     * Public landing page (all of its content lives in the Blade itself).
     */
    public function homepage()
    {
        // Homepage cards — fed from the database. Only admin-approved,
        // published courses appear; "Feature on Homepage" controls the
        // Featured strip, latest published fill the Latest strip.
        $cards = fn ($courses) => $courses->map(fn (Course $c) => [
            'title'       => $c->title,
            'description' => \Illuminate\Support\Str::limit((string) $c->description, 110),
            'professor'   => $c->instructor ?? 'Faculty',
            'hours'       => (int) filter_var($c->duration, FILTER_SANITIZE_NUMBER_INT),
            'rating'      => 0,
            'category'    => $c->category ?? 'General',
            'level'       => $c->level ?? 'Beginner',
            'image'       => $c->thumbnail_url ? asset($c->thumbnail_url) : null,
            'slug'        => url('/login'),
        ])->values()->all();

        $featured = Course::where('is_published', true)->where('is_featured', true)
            ->latest('approved_at')->limit(3)->get();
        if ($featured->count() < 3) {
            $featured = $featured->concat(
                Course::where('is_published', true)->where('is_featured', false)
                    ->latest('approved_at')->limit(3 - $featured->count())->get()
            );
        }

        // ── Hero counters — real, live site-wide numbers ─────────────
        $stats = [
            'courses'      => Course::where('is_published', true)->count(),
            'learners'     => User::where('role_id', User::ROLE_STUDENT)->count(),
            'certificates' => Certificate::count(),
            'badges'       => UserBadge::count(),
        ];

        // ── Announcements — real site activity (new courses, enrollment
        //    milestones, recent badges) plus any published announcements ──
        $announcements = $this->buildAnnouncements();

        return view('Homepage', [
            'featuredCourses' => $cards($featured),
            'latestCourses'   => $cards(
                Course::where('is_published', true)->latest('created_at')->limit(3)->get()
            ),
            'stats'           => $stats,
            'announcements'   => $announcements,
        ]);
    }

    /**
     * Build the homepage announcement feed from real site activity:
     * published admin announcements first, then auto-generated items for
     * newly published courses, this month's enrollment count, and the
     * latest badge awards.
     */
    private function buildAnnouncements()
    {
        $items = collect();

        // 1) Manually published announcements (if any exist).
        $manual = Announcement::where('is_published', true)
            ->latest('published_at')->limit(3)->get()
            ->map(fn (Announcement $a) => [
                'type'  => 'general',
                'label' => 'Announcement',
                'date'  => ($a->published_at ?? $a->created_at)?->format('F j, Y') ?? '',
                'title' => $a->title,
                'desc'  => \Illuminate\Support\Str::limit((string) $a->body, 160),
            ]);
        $items = $items->concat($manual);

        // 2) New courses published this month.
        Course::where('is_published', true)
            ->latest('approved_at')->limit(2)->get()
            ->each(function (Course $c) use ($items) {
                $items->push([
                    'type'  => 'general',
                    'label' => 'New Course',
                    'date'  => ($c->approved_at ?? $c->created_at)?->format('F j, Y') ?? '',
                    'title' => 'New Course Added: ' . $c->title,
                    'desc'  => \Illuminate\Support\Str::limit((string) $c->description, 160)
                                ?: 'A new course is now available in the catalog.',
                ]);
            });

        // 3) Enrollment activity this month.
        $monthEnroll = Enrollment::where('created_at', '>=', now()->startOfMonth())->count();
        if ($monthEnroll > 0) {
            $items->push([
                'type'  => 'event',
                'label' => 'Enrollment',
                'date'  => now()->format('F Y'),
                'title' => $monthEnroll . ' new ' . \Illuminate\Support\Str::plural('enrollment', $monthEnroll) . ' this month',
                'desc'  => 'Students are actively enrolling across our microcredential courses this month.',
            ]);
        }

        // 4) Latest badge award.
        $latestBadge = UserBadge::with('badge')->latest('earned_at')->first();
        if ($latestBadge) {
            $items->push([
                'type'  => 'general',
                'label' => 'Achievement',
                'date'  => $latestBadge->earned_at?->format('F j, Y') ?? '',
                'title' => 'Badge Awarded: ' . ($latestBadge->badge->name ?? 'Badge'),
                'desc'  => 'A learner just earned the "' . ($latestBadge->badge->name ?? 'Badge') . '" badge.',
            ]);
        }

        return $items->take(5)->values();
    }

    /**
     * Public catalog — every published course ("View all Courses").
     */
    public function explore()
    {
        $cards = Course::where('is_published', true)
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->get()
            ->map(fn (Course $c) => [
                'title'       => $c->title,
                'description' => \Illuminate\Support\Str::limit((string) $c->description, 110),
                'professor'   => $c->instructor ?? 'Faculty',
                'hours'       => (int) filter_var($c->duration, FILTER_SANITIZE_NUMBER_INT),
                'rating'      => 0,
                'category'    => $c->category ?? 'General',
                'level'       => $c->level ?? 'Beginner',
                'image'       => $c->thumbnail_url ? asset($c->thumbnail_url) : null,
                'slug'        => url('/login'),
            ])->values()->all();

        return view('Explore_Courses', ['courses' => $cards]);
    }

    /**
     * Students directory — real students (role_id = 3) with the courses
     * each one is enrolled in, replacing the Blade's built-in dummy list.
     */
    public function students()
    {
        $students = User::query()
            ->where('role_id', User::ROLE_STUDENT)
            ->where('is_active', true)
            ->with(['enrollments.course'])
            ->orderBy('first_name')
            ->get()
            ->map(function (User $u) {
                return (object) [
                    'name'       => $u->name,
                    'student_id' => $u->student_id ?? $u->user_code,
                    'avatar_url' => $u->avatar_url,
                    'courses'    => $u->enrollments->map(fn ($e) => [
                        'title'     => $e->course->title ?? 'Course',
                        'completed' => (bool) $e->is_completed,
                    ])->values()->all(),
                ];
            });

        return view('Student_List', ['students' => $students]);
    }

    /**
     * Notifications feed — pulled from the notifications table for the
     * signed-in user; falls back to the sample items for guests so the
     * page always renders.
     */
    public function notifications()
    {
        $auth = Auth::user();

        // 1) Stored notifications for this user (highest priority).
        if ($auth) {
            $stored = Notification::query()
                ->where('user_id', $auth->id)
                ->latest()
                ->limit(30)
                ->get()
                ->map(fn (Notification $n) => (object) [
                    'title'   => $n->title,
                    'message' => $n->message,
                    'time'    => $n->created_at?->diffForHumans() ?? '',
                    'type'    => $n->type,
                    'unread'  => ! $n->is_read,
                ]);
            if ($stored->isNotEmpty()) {
                return view('notifications', ['notifications' => $stored]);
            }
        }

        // 2) Otherwise build a live activity feed so the bell is never empty.
        $notifications = collect();

        if ($auth && (int) $auth->role_id === User::ROLE_FACULTY) {
            // Faculty: what is happening with THEIR courses.
            $courseIds = Course::where('created_by', $auth->id)->pluck('id');

            Enrollment::with(['user', 'course'])->whereIn('course_id', $courseIds)
                ->latest()->limit(5)->get()
                ->each(fn ($e) => $notifications->push((object) [
                    'title'   => 'New student enrolled',
                    'message' => ($e->user->name ?? 'A student') . ' enrolled in "' . ($e->course->title ?? 'your course') . '".',
                    'time'    => $e->created_at?->diffForHumans() ?? '',
                    'type'    => 'enrollment',
                    'unread'  => true,
                ]));

            UserBadge::with(['user', 'badge'])->whereHas('user.enrollments', fn ($q) => $q->whereIn('course_id', $courseIds))
                ->latest('earned_at')->limit(3)->get()
                ->each(fn ($ub) => $notifications->push((object) [
                    'title'   => 'Badge awarded',
                    'message' => ($ub->user->name ?? 'A student') . ' earned the "' . ($ub->badge->name ?? 'Badge') . '" badge.',
                    'time'    => $ub->earned_at?->diffForHumans() ?? '',
                    'type'    => 'badge',
                    'unread'  => true,
                ]));
        } else {
            // Student / guest: their own badges + new courses on the site.
            if ($auth) {
                UserBadge::with('badge')->where('user_id', $auth->id)
                    ->latest('earned_at')->limit(4)->get()
                    ->each(fn ($ub) => $notifications->push((object) [
                        'title'   => 'Badge earned',
                        'message' => 'You earned the "' . ($ub->badge->name ?? 'Badge') . '" badge. Congratulations!',
                        'time'    => $ub->earned_at?->diffForHumans() ?? '',
                        'type'    => 'badge',
                        'unread'  => true,
                    ]));
            }

            Course::where('is_published', true)->latest('approved_at')->limit(3)->get()
                ->each(fn ($c) => $notifications->push((object) [
                    'title'   => 'New course available',
                    'message' => '"' . $c->title . '" was just published and is open for enrollment.',
                    'time'    => ($c->approved_at ?? $c->created_at)?->diffForHumans() ?? '',
                    'type'    => 'course',
                    'unread'  => true,
                ]));
        }

        if ($notifications->isEmpty()) {
            $notifications->push((object) [
                'title'   => 'All caught up',
                'message' => 'There is no new activity right now. Check back later for updates.',
                'time'    => '',
                'type'    => 'system',
                'unread'  => false,
            ]);
        }

        return view('notifications', ['notifications' => $notifications->take(10)->values()]);
    }

    /**
     * Global navbar search → forwards to the course browser.
     */
    public function search(Request $request)
    {
        $query = trim((string) $request->input('q', ''));

        return redirect()->route('courses.browse', $query ? ['q' => $query] : []);
    }

    public function forgotPassword()
    {
        return view('login');
    }

    /**
     * Live-monitoring JSON feed used by the admin/faculty dashboards.
     */
    public function monitoringLive()
    {
        $activeUsers = (int) DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
            ->distinct()
            ->count('user_id');

        $today = Carbon::today();

        $stats = [
            'active_users'       => $activeUsers,
            'events_today'       => AnalyticsEvent::whereDate('occurred_at', $today)->count(),
            'enrollments_today'  => DB::table('enrollments')->whereDate('enrolled_at', $today)->count(),
            'badges_today'       => DB::table('user_badges')->whereDate('earned_at', $today)->count(),
        ];

        $labels = [
            'enrollment'       => 'New student enrolled',
            'badge_issued'     => 'Badge issued',
            'course_completed' => 'Course completed',
            'lesson_completed' => 'Lesson completed',
            'quiz_passed'      => 'Quiz passed',
        ];

        // Build the live feed from REAL activity so it always updates:
        // recent enrollments, badge awards, and course completions — merged
        // with any recorded analytics events, newest first.
        $feed = collect();

        Enrollment::with(['user', 'course'])->latest()->limit(6)->get()
            ->each(fn ($e) => $feed->push([
                'title' => 'New student enrolled',
                'detail'=> ($e->user->name ?? 'A student') . ' · ' . ($e->course->title ?? 'a course'),
                'time'  => ($e->enrolled_at ?? $e->created_at)?->diffForHumans() ?? '',
                'ts'    => ($e->enrolled_at ?? $e->created_at)?->getTimestamp() ?? 0,
            ]));

        UserBadge::with(['user', 'badge'])->latest('earned_at')->limit(6)->get()
            ->each(fn ($ub) => $feed->push([
                'title' => 'Badge issued',
                'detail'=> ($ub->user->name ?? 'A student') . ' earned the ' . ($ub->badge->name ?? 'Badge') . ' badge',
                'time'  => $ub->earned_at?->diffForHumans() ?? '',
                'ts'    => $ub->earned_at?->getTimestamp() ?? 0,
            ]));

        Enrollment::with(['user', 'course'])->where('is_completed', true)
            ->latest('updated_at')->limit(4)->get()
            ->each(fn ($e) => $feed->push([
                'title' => 'Course completed',
                'detail'=> ($e->user->name ?? 'A student') . ' completed ' . ($e->course->title ?? 'a course'),
                'time'  => $e->updated_at?->diffForHumans() ?? '',
                'ts'    => $e->updated_at?->getTimestamp() ?? 0,
            ]));

        AnalyticsEvent::query()->with('user')->latest('occurred_at')->limit(6)->get()
            ->each(function (AnalyticsEvent $event) use ($labels, $feed) {
                $actor = $event->user->name ?? 'A user';
                $feed->push([
                    'title' => $labels[$event->event_type] ?? ucfirst(str_replace('_', ' ', $event->event_type)),
                    'detail'=> $event->metadata['detail'] ?? ($actor . ' · ' . str_replace('_', ' ', $event->event_type)),
                    'time'  => $event->occurred_at?->diffForHumans() ?? '',
                    'ts'    => $event->occurred_at?->getTimestamp() ?? 0,
                ]);
            });

        $activity = $feed->sortByDesc('ts')->take(8)->values()
            ->map(fn ($row) => ['title' => $row['title'], 'detail' => $row['detail'], 'time' => $row['time']])
            ->all();

        // Same zero-inclusive badge counters the dashboard renders, so the
        // 15-second polling can refresh the Recent Badges panel live.
        $recentBadges = DB::table('badges')
            ->leftJoin('user_badges', 'user_badges.badge_id', '=', 'badges.id')
            ->select('badges.name', DB::raw('COUNT(user_badges.id) as earned_count'))
            ->groupBy('badges.id', 'badges.name')
            ->orderByDesc('earned_count')
            ->orderBy('badges.name')
            ->limit(4)
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'earned_count' => (int) $row->earned_count])
            ->values()
            ->all();

        return response()->json(compact('stats', 'activity', 'recentBadges'));
    }
}
