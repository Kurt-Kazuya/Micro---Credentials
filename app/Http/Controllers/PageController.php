<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Course;
use App\Models\Notification;
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

        return view('Homepage', [
            'featuredCourses' => $cards($featured),
            'latestCourses'   => $cards(
                Course::where('is_published', true)->latest('created_at')->limit(3)->get()
            ),
        ]);
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
                    'student_id' => $u->user_code ?? $u->student_id,
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
        if (Auth::check()) {
            $notifications = Notification::query()
                ->where('user_id', Auth::id())
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

            if ($notifications->isNotEmpty()) {
                return view('notifications', compact('notifications'));
            }
        }

        $notifications = collect([
            (object) [
                'title'   => 'Course update available',
                'message' => 'A new lesson was added to your enrolled course.',
                'time'    => '10 min ago',
                'type'    => 'course',
                'unread'  => true,
            ],
            (object) [
                'title'   => 'New announcement',
                'message' => 'The faculty team posted a new milestone update.',
                'time'    => '1 hour ago',
                'type'    => 'announcement',
                'unread'  => true,
            ],
            (object) [
                'title'   => 'System reminder',
                'message' => 'Your badge portfolio was refreshed.',
                'time'    => 'Yesterday',
                'type'    => 'system',
                'unread'  => false,
            ],
        ]);

        return view('notifications', compact('notifications'));
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

        $activity = AnalyticsEvent::query()
            ->with('user')
            ->latest('occurred_at')
            ->limit(8)
            ->get()
            ->map(function (AnalyticsEvent $event) use ($labels) {
                $actor  = $event->user->name ?? 'A user';
                $detail = $event->metadata['detail'] ?? ($actor . ' · ' . str_replace('_', ' ', $event->event_type));

                return [
                    'title'  => $labels[$event->event_type] ?? ucfirst(str_replace('_', ' ', $event->event_type)),
                    'detail' => $detail,
                    'time'   => $event->occurred_at?->diffForHumans() ?? '',
                ];
            })
            ->values()
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
