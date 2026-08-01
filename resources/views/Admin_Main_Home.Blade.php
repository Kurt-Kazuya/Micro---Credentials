<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upskill – Admin Dashboard</title>
    <style>
        /* ─── Reset ─────────────────────────────────────────────── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        /* ─── Tokens ─────────────────────────────────────────────── */
        :root {
            --navy:        #0d1b6e;
            --navy-light:  #1a2fa0;
            --navy-pale:   #e8eaf6;
            --teal:        #1a6b5a;
            --teal-bar:    #1f8a6e;
            --white:       #ffffff;
            --surface:     #f4f6fb;
            --border:      #e2e5f0;
            --text-main:   #0d1b6e;
            --text-muted:  #6b7280;
            --text-sub:    #9ca3af;
            --shadow-sm:   0 1px 4px rgba(13,27,110,.07);
            --shadow-md:   0 3px 10px rgba(13,27,110,.10);
            --radius-card: 12px;
            --radius-sm:   8px;
            --sidebar-w:   225px;
            --topbar-h:    58px;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--surface);
            color: var(--text-main);
            min-height: 100vh;
        }

        /* ─── TOP NAV ─────────────────────────────────────────────── */
        .topbar {
            position: fixed;
            inset: 0 0 auto 0;
            height: var(--topbar-h);
            background: var(--navy);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 22px;
            z-index: 200;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--white);
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: none;
        }

        .brand-logo {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .brand-logo svg { width: 20px; height: 20px; }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .search-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.22);
            border-radius: 20px;
            padding: 6px 16px;
        }

        .search-wrap svg { color: rgba(255,255,255,0.65); flex-shrink: 0; }

        .search-wrap input {
            background: none;
            border: none;
            outline: none;
            color: var(--white);
            width: 170px;
            font-size: 0.825rem;
        }

        .search-wrap input::placeholder { color: rgba(255,255,255,0.55); }

        .avatar-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            text-decoration: none;
            transition: background .2s;
        }

        .avatar-btn:hover { background: rgba(255,255,255,0.25); }

        /* ─── LAYOUT ─────────────────────────────────────────────── */
        .layout {
            display: flex;
            margin-top: var(--topbar-h);
            min-height: calc(100vh - var(--topbar-h));
        }

        /* ════════════════════════════════════════════════════════════
           SIDEBAR
           Structure → Image 1 (bold header + count subtitle + chevron
                                + left accent border on open section
                                + thin row dividers between items)
           Active item → Image 2 (teal rounded pill, no icons, plain text)
           ════════════════════════════════════════════════════════════ */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--white);
            border-right: 1px solid var(--border);
            position: fixed;
            top: var(--topbar-h);
            left: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 100;
            scrollbar-width: thin;
            scrollbar-color: var(--border) transparent;
        }

        /* ── Accordion section container ── */
        .sb-section {
            border-bottom: 1px solid var(--border);
        }

        /* ── Section header row (matches Image 1 module header) ── */
        .sb-section-hd {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 14px 14px 16px;
            cursor: pointer;
            user-select: none;
            /* Left accent border — visible only when section is open (Image 1) */
            border-left: 3px solid transparent;
            transition: background .18s, border-color .2s;
        }

        .sb-section-hd:hover { background: #f7f9ff; }

        /* Purple-indigo left border when expanded — mirrors Image 1 active module */
        .sb-section.open > .sb-section-hd {
            border-left-color: #5c6bc0;
        }

        .sb-hd-left {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        /* Section name — bold normal-case, matches Image 1 "Module 1: …" weight
           and Image 2 "Main / Management / Analytics" style */
        .sb-section-label {
            font-size: 0.875rem;
            font-weight: 700;
            color: #1a237e;
            letter-spacing: 0;
            text-transform: none;
        }

        /* Item-count subtitle — matches Image 1 "3 lessons" line */
        .sb-lesson-count {
            font-size: 0.695rem;
            color: var(--text-sub);
            font-weight: 400;
        }

        /* Chevron — rotates when open, matches Image 1 ^ / v */
        .sb-chevron {
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            transition: transform .28s ease;
            flex-shrink: 0;
        }

        .sb-section.open .sb-chevron { transform: rotate(180deg); }

        /* ── Items list ── */
        .sb-items {
            display: none;
            flex-direction: column;
            padding: 6px 10px 10px;
            gap: 1px;
        }

        .sb-section.open .sb-items { display: flex; }

        /* ── Individual nav item ──
           Thin bottom divider between rows  → Image 1 lesson rows
           No icons, plain text             → Image 2 style           */
        .sb-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 10px 9px 12px;
            text-decoration: none;
            color: #374151;
            font-size: 0.84rem;
            border-radius: 6px;
            /* Thin separator line between items — Image 1 row dividers */
            border-bottom: 1px solid #f0f2f9;
            transition: background .15s, color .15s;
        }

        .sb-item:last-child { border-bottom: none; }

        .sb-item:hover {
            background: #eef0fb;
            color: var(--navy);
            border-radius: 6px;
        }

        /* ── ACTIVE ITEM — teal rounded pill (Image 2 "Home" highlight) ── */
        .sb-item.active {
            background: #62d4cf;      /* teal pill colour from Image 2   */
            border-radius: 22px;      /* fully rounded pill shape         */
            color: #0d2060;
            font-weight: 600;
            border-bottom: none;      /* no divider on the active pill    */
        }

        .sb-item.active:hover { background: #4ecbc5; }

        .sb-item-text { flex: 1; }

        /* Right-side type label — adapts Image 1 "Video · 15m" pattern
           to admin context ("Dashboard", "Admin", "View")               */
        .sb-item-type {
            font-size: 0.67rem;
            color: var(--text-sub);
            white-space: nowrap;
            margin-left: 6px;
        }

        .sb-item.active .sb-item-type { color: #2d5098; }

        /* ─── MAIN CONTENT ─────────────────────────────────────── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            padding: 30px 28px 40px;
        }

        .page-heading { font-size: 1.25rem; font-weight: 800; color: var(--navy); margin-bottom: 3px; }
        .page-sub { font-size: 0.825rem; color: var(--text-muted); margin-bottom: 26px; }

        /* ─── STAT CARDS ─────────────────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 22px;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--radius-card);
            border: 1px solid var(--border);
            padding: 22px 18px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: box-shadow .2s, transform .2s;
        }

        .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }

        .stat-val {
            font-size: 1.95rem;
            font-weight: 800;
            color: var(--navy);
            line-height: 1;
            margin-bottom: 7px;
        }

        .stat-lbl {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        /* ─── TWO-COLUMN GRID ─────────────────────────────────────── */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }

        /* ─── CARD SHELL ─────────────────────────────────────────── */
        .card {
            background: var(--white);
            border-radius: var(--radius-card);
            border: 1px solid var(--border);
            padding: 20px;
            box-shadow: var(--shadow-sm);
        }

        .card-hd {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .card-title { font-size: 0.9rem; font-weight: 800; color: var(--navy); }

        .view-all {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--navy-light);
            text-decoration: none;
            transition: opacity .2s;
        }

        .view-all:hover { opacity: .7; }

        /* ─── COURSE LIST ─────────────────────────────────────────── */
        .course-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 0;
            border-bottom: 1px solid #f1f3fa;
        }

        .course-item:last-child { border-bottom: none; }

        .course-thumb {
            width: 42px;
            height: 42px;
            background: var(--navy-pale);
            border-radius: var(--radius-sm);
            border: 1px solid #c5cae9;
            flex-shrink: 0;
            overflow: hidden;
        }

        .course-body { flex: 1; min-width: 0; }

        .course-name {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--navy);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .course-meta { font-size: 0.7rem; color: var(--text-sub); margin-top: 2px; }

        .course-pct {
            border: 1.5px solid var(--navy);
            border-radius: var(--radius-sm);
            padding: 4px 9px;
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--navy);
            min-width: 46px;
            text-align: center;
            flex-shrink: 0;
        }

        /* ─── BADGES GRID ─────────────────────────────────────────── */
        .badges-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .badge-card {
            border: 1.5px solid #c5cae9;
            border-radius: 10px;
            padding: 13px 12px;
            text-align: center;
            transition: border-color .2s;
        }

        .badge-card:hover { border-color: var(--navy-light); }

        .badge-name { font-size: 0.8rem; font-weight: 700; color: var(--navy); }
        .badge-count { font-size: 0.72rem; color: var(--text-sub); margin-top: 3px; }

        /* ─── BAR CHARTS ─────────────────────────────────────────── */
        .chart-row { margin-bottom: 13px; }
        .chart-row:last-child { margin-bottom: 0; }

        .chart-lbl {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 5px;
        }

        .bar-track {
            height: 8px;
            background: #e4e8f5;
            border-radius: 4px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: var(--teal-bar);
            border-radius: 4px;
            transition: width .6s ease;
        }
    </style>
</head>
<body>

{{-- ════════════════════════════════════ TOP NAV ══════════════════════════════════ --}}
<nav class="topbar">
    <a href="{{ route('Homepage') }}" class="topbar-brand">
        <div class="brand-logo">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L3 7l9 5 9-5-9-5z" fill="#0d1b6e"/>
                <path d="M3 12l9 5 9-5" stroke="#0d1b6e" stroke-width="2" stroke-linecap="round"/>
                <path d="M3 17l9 5 9-5" stroke="#0d1b6e" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        UPSKILL
    </a>

    <div class="topbar-right">
        <form action="{{ route('search') }}" method="GET" class="search-wrap" role="search">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="q" placeholder="Search">
        </form>

        <a href="{{ route('admin.profile') }}" class="avatar-btn" title="My Profile">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8V21.6h19.2V19.2c0-3.2-6.4-4.8-9.6-4.8z"/>
            </svg>
        </a>
        <form action="{{ route('logout') }}" method="POST" style="display:inline-flex;align-items:center;">
            @csrf
            <button type="submit" style="background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;border-radius:999px;padding:8px 12px;font-weight:700;cursor:pointer;">Logout</button>
        </form>
    </div>
</nav>

<div class="layout">

{{-- ════════════════════════════════════ SIDEBAR ══════════════════════════════════ --}}
{{-- Structure : Image 1 — bold section header, item-count subtitle, chevron,
                           purple-indigo left-border on open section,
                           thin horizontal row dividers between items,
                           cream quiz row with QUIZ button, locked-section state.
     Active item: Image 2 — teal rounded pill, no icons, plain text labels.       --}}
<aside class="sidebar" id="sidebar">

    {{-- ── MAIN ──────────────────────────────────────────────────────────── --}}
    <div class="sb-section open" id="sec-main">
        <div class="sb-section-hd" onclick="toggleSection('sec-main')">
            <div class="sb-hd-left">
                <span class="sb-section-label">Main</span>
                <span class="sb-lesson-count">1 page</span>
            </div>
            <span class="sb-chevron">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="18 15 12 9 6 15"/>
                </svg>
            </span>
        </div>
        <div class="sb-items">
            {{-- Active item → teal pill (Image 2 "Home" style) --}}
            <a href="{{ route('admin.dashboard') }}" class="sb-item active">
                <span class="sb-item-text">Home</span>
            </a>
        </div>
    </div>

    {{-- ── MANAGEMENT ─────────────────────────────────────────────────────── --}}
    <div class="sb-section open" id="sec-manage">
        <div class="sb-section-hd" onclick="toggleSection('sec-manage')">
            <div class="sb-hd-left">
                <span class="sb-section-label">Management</span>
                <span class="sb-lesson-count">2 sections</span>
            </div>
            <span class="sb-chevron">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="18 15 12 9 6 15"/>
                </svg>
            </span>
        </div>
        <div class="sb-items">
            {{-- TODO: replace '#' with admin user-management route once defined --}}
            <a href="{{ route('admin.usermanagement') }}" class="sb-item">
                <span class="sb-item-text">User Management</span>
            </a>
            <a href="{{ route('admin.courses') }}" class="sb-item">
                <span class="sb-item-text">Courses &amp; Badges</span>
            </a>
        </div>
    </div>

    {{-- ── ANALYTICS ───────────────────────────────────────────────────── --}}
    <div class="sb-section open" id="sec-analytics">
        <div class="sb-section-hd" onclick="toggleSection('sec-analytics')">
            <div class="sb-hd-left">
                <span class="sb-section-label">Analytics</span>
                <span class="sb-lesson-count">1 section</span>
            </div>
            <span class="sb-chevron">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="18 15 12 9 6 15"/>
                </svg>
            </span>
        </div>
        <div class="sb-items">
            <a href="{{ route('admin.report') }}" class="sb-item">
                <span class="sb-item-text">Report</span>
            </a>
        </div>
    </div>



</aside>

{{-- ════════════════════════════════════ MAIN CONTENT ═════════════════════════════ --}}
<main class="main">

    <div class="page-heading">Welcome back, Admin</div>
    <div class="page-sub">Here's what's happening on your platform today.</div>

    {{-- ── STAT CARDS ──────────────────────────────────────────────────── --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-val" id="live-active-users">0</div>
            <div class="stat-lbl">Active Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" id="live-events-today">0</div>
            <div class="stat-lbl">Events Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" id="live-enrollments-today">0</div>
            <div class="stat-lbl">Enrollments Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" id="live-badges-today">0</div>
            <div class="stat-lbl">Badges Today</div>
        </div>
    </div>

    <div class="two-col" style="margin-bottom: 18px;">
        <div class="card">
            <div class="card-hd">
                <span class="card-title">Live Monitoring Feed</span>
            </div>
            <div id="live-activity-list" class="badges-grid"></div>
        </div>
        <div class="card">
            <div class="card-hd">
                <span class="card-title">Current Platform Snapshot</span>
            </div>
            <div class="chart-row">
                <div class="chart-lbl">Students on platform &ndash; {{ $stats['total_students'] }}</div>
            </div>
            <div class="chart-row">
                <div class="chart-lbl">Badges issued &ndash; {{ $stats['badges_issued'] }}</div>
            </div>
            <div class="chart-row">
                <div class="chart-lbl">Average quiz score &ndash; {{ $stats['course_score_avg'] }}%</div>
            </div>
            <div class="chart-row">
                <div class="chart-lbl">Enrollments recorded &ndash; {{ $stats['students_enrolled'] }}</div>
            </div>
        </div>
    </div>

    {{-- ── ACTIVE COURSES + RECENT BADGES ─────────────────────────────── --}}
    <div class="two-col">

        {{-- Active Courses --}}
        <div class="card">
            <div class="card-hd">
                <span class="card-title">Active Courses</span>
                <a href="{{ route('courses.browse') }}" class="view-all">View All</a>
            </div>

            @foreach ($activeCourses as $course)
            <div class="course-item">
                <div class="course-thumb">
                    @if ($course->thumbnail_url)
                        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}"
                             style="width:100%;height:100%;object-fit:cover;">
                    @endif
                </div>
                <div class="course-body">
                    <div class="course-name">{{ $course->title }}</div>
                    <div class="course-meta">{{ $course->meta }}</div>
                </div>
                <div class="course-pct">{{ $course->percent }}%</div>
            </div>
            @endforeach
        </div>

        {{-- Recent Badges --}}
        <div class="card">
            <div class="card-hd">
                <span class="card-title">Recent Badges</span>
            </div>
            <div class="badges-grid">
                @foreach ($recentBadges as $badge)
                <div class="badge-card">
                    <div class="badge-name">{{ $badge->name }}</div>
                    <div class="badge-count">{{ $badge->earned_count }} Earned</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── ENROLLMENT + COMPLETION CHARTS ─────────────────────────────── --}}
    <div class="two-col">

        {{-- Enrollment by Courses --}}
        <div class="card">
            <div class="card-hd">
                <span class="card-title">Enrollment by Courses</span>
            </div>
            @foreach ($enrollmentByCourse as $row)
            <div class="chart-row">
                <div class="chart-lbl">{{ $row->label }} &ndash; {{ $row->value }}</div>
                <div class="bar-track"><div class="bar-fill" style="width:{{ $row->percent }}%"></div></div>
            </div>
            @endforeach
        </div>

        {{-- Completion Rate --}}
        <div class="card">
            <div class="card-hd">
                <span class="card-title">Completion Rate</span>
            </div>
            @foreach ($completionRate as $row)
            <div class="chart-row">
                <div class="chart-lbl">{{ $row->label }} &ndash; {{ $row->value }}%</div>
                <div class="bar-track"><div class="bar-fill" style="width:{{ $row->percent }}%"></div></div>
            </div>
            @endforeach
        </div>
    </div>

</main>
</div>{{-- /layout --}}

{{-- ── SCRIPTS ─────────────────────────────────────────────────────────── --}}
<script>
    function toggleSection(id) {
        const section = document.getElementById(id);
        if (!section || section.classList.contains('locked')) return;
        section.classList.toggle('open');
    }

    function refreshLiveMonitoring() {
        const liveUrl = '{{ route('monitoring.live') }}';
        fetch(liveUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Monitoring endpoint unavailable');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('live-active-users').textContent = data.stats.active_users;
                document.getElementById('live-events-today').textContent = data.stats.events_today;
                document.getElementById('live-enrollments-today').textContent = data.stats.enrollments_today;
                document.getElementById('live-badges-today').textContent = data.stats.badges_today;

                const list = document.getElementById('live-activity-list');
                if (!list) return;

                list.innerHTML = '';
                data.activity.forEach(item => {
                    const card = document.createElement('div');
                    card.className = 'badge-card';
                    card.innerHTML = `<div class="badge-name">${item.title}</div><div class="badge-count">${item.detail} · ${item.time}</div>`;
                    list.appendChild(card);
                });
            })
            .catch(() => {
                const list = document.getElementById('live-activity-list');
                if (list) {
                    list.innerHTML = '<div class="badge-card"><div class="badge-name">Monitoring unavailable</div><div class="badge-count">The live feed could not be loaded.</div></div>';
                }
            });
    }

    refreshLiveMonitoring();
    setInterval(refreshLiveMonitoring, 15000);
</script>

</body>
</html>