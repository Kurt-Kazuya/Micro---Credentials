<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upskill – Courses &amp; Badges</title>
    <style>
        /* ─── Reset ─────────────────────────────────────────────── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        /* ─── Tokens (shared with Admin_Main_Home / UserManage) ──── */
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
            --shadow-md:   0 3px 12px rgba(13,27,110,.11);
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

        .topbar-right { display: flex; align-items: center; gap: 14px; }

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
           SIDEBAR — identical to Admin_Main_Home / UserManage.
           Active pill sits on "Courses & Badges".
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

        .sb-section { border-bottom: 1px solid var(--border); }

        .sb-section-hd {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 14px 14px 16px;
            cursor: pointer;
            user-select: none;
            border-left: 3px solid transparent;
            transition: background .18s, border-color .2s;
        }

        .sb-section-hd:hover { background: #f7f9ff; }

        .sb-section.open > .sb-section-hd { border-left-color: #5c6bc0; }

        .sb-hd-left { display: flex; flex-direction: column; gap: 3px; }

        .sb-section-label {
            font-size: 0.875rem;
            font-weight: 700;
            color: #1a237e;
            letter-spacing: 0;
            text-transform: none;
        }

        .sb-lesson-count {
            font-size: 0.695rem;
            color: var(--text-sub);
            font-weight: 400;
        }

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

        .sb-items {
            display: none;
            flex-direction: column;
            padding: 6px 10px 10px;
            gap: 1px;
        }

        .sb-section.open .sb-items { display: flex; }

        .sb-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 10px 9px 12px;
            text-decoration: none;
            color: #374151;
            font-size: 0.84rem;
            border-radius: 6px;
            border-bottom: 1px solid #f0f2f9;
            transition: background .15s, color .15s;
        }

        .sb-item:last-child { border-bottom: none; }

        .sb-item:hover { background: #eef0fb; color: var(--navy); }

        /* Active item — teal pill */
        .sb-item.active {
            background: #62d4cf;
            border-radius: 22px;
            color: #0d2060;
            font-weight: 600;
            border-bottom: none;
        }

        .sb-item.active:hover { background: #4ecbc5; }

        .sb-item-text { flex: 1; }

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
            padding: 30px 34px 44px;
        }

        .page-heading { font-size: 1.25rem; font-weight: 800; color: var(--navy); margin-bottom: 3px; }
        .page-sub { font-size: 0.825rem; color: var(--text-muted); margin-bottom: 26px; }

        /* ─── COURSE CARD GRID (matches mockup: 2-up cards) ─────── */
        .course-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 26px;
        }

        @media (max-width: 1050px) {
            .course-grid { grid-template-columns: 1fr; }
        }

        /* ─── SINGLE COURSE CARD ────────────────────────────────── */
        .course-card {
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 14px;
            padding: 26px 26px 30px;
            min-height: 190px;
            box-shadow: var(--shadow-sm);
        }

        .course-card-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--navy);
            line-height: 1.28;
            margin-bottom: 16px;
        }

        .course-card-meta {
            font-size: 0.8rem;
            color: var(--text-sub);
            font-weight: 600;
            line-height: 1.55;
            margin-bottom: 18px;
        }

        /* ─── PROGRESS BAR ──────────────────────────────────────── */
        .progress-track {
            height: 6px;
            background: #e4e8f5;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-fill {
            height: 100%;
            background: var(--teal-bar);
            border-radius: 4px;
            transition: width .6s ease;
        }

        .course-card-pct {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--navy);
        }

        /* ─── CLICKABLE CARD ────────────────────────────────────── */
        .course-card { cursor: pointer; transition: box-shadow .2s, transform .2s, border-color .2s; }
        .course-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); border-color: #c7cdf0; }

        /* ─── APPROVAL STATUS PILLS ─────────────────────────────── */
        .approval-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 999px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .approval-pill .dot { width: 8px; height: 8px; border-radius: 50%; }
        .approval-pending  { background: #fff7ed; color: #b45309; border: 1px solid #fed7aa; }
        .approval-pending .dot { background: #f59e0b; }
        .approval-approved { background: #ecfdf5; color: #15803d; border: 1px solid #a7f3d0; }
        .approval-approved .dot { background: #22c55e; }
        .approval-denied   { background: #fff1f2; color: #b91c1c; border: 1px solid #fecdd3; }
        .approval-denied .dot { background: #ef4444; }
        .approval-draft    { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .approval-draft .dot { background: #94a3b8; }

        /* Pending cards get a subtle amber edge so they stand out */
        .course-card.is-pending { border-color: #fcd9a0; background: #fffdf8; }

        /* ─── APPROVE / DENIED BUTTONS ──────────────────────────── */
        .course-actions { display: flex; gap: 10px; margin-top: 16px; }
        .btn-approve, .btn-deny {
            flex: 1;
            border: none;
            border-radius: 9px;
            padding: 10px 0;
            font-size: 0.84rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .18s, transform .15s;
        }
        .btn-approve { background: #16a34a; color: #fff; }
        .btn-approve:hover { background: #15803d; transform: translateY(-1px); }
        .btn-deny { background: #fff1f2; color: #b91c1c; border: 1.5px solid #fecdd3; }
        .btn-deny:hover { background: #ffe4e6; transform: translateY(-1px); }

        /* flash message */
        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
            border-radius: 10px;
            padding: 11px 16px;
            font-size: 0.86rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .empty-state {
            padding: 34px 24px;
            border-radius: 14px;
            background: var(--white);
            border: 1.5px solid var(--border);
            color: var(--text-muted);
            text-align: center;
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
{{-- Identical to Admin_Main_Home — active pill sits on "Courses & Badges" --}}
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
            <a href="{{ route('admin.dashboard') }}" class="sb-item">
                <span class="sb-item-text">Home</span>
            </a>
        </div>
    </div>

    {{-- ── MANAGEMENT ─────────────────────────────────────────────────────── --}}
    <div class="sb-section open" id="sec-manage">
        <div class="sb-section-hd" onclick="toggleSection('sec-manage')">
            <div class="sb-hd-left">
                <span class="sb-section-label">Management</span>
                <span class="sb-lesson-count">3 sections</span>
            </div>
            <span class="sb-chevron">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="18 15 12 9 6 15"/>
                </svg>
            </span>
        </div>
        <div class="sb-items">
            <a href="{{ route('admin.usermanagement') }}" class="sb-item">
                <span class="sb-item-text">User Management</span>
            </a>
            {{-- Active pill on Courses & Badges --}}
            <a href="{{ route('admin.courses') }}" class="sb-item active">
                <span class="sb-item-text">Courses &amp; Badges</span>
            </a>
            <a href="{{ route('admin.facultycodes') }}" class="sb-item">
                <span class="sb-item-text">Faculty Codes</span>
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

    <div class="page-heading">Courses &amp; Badges</div>
    <div class="page-sub">Manage every course and its associated completion badge.</div>

    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── COURSE CARD GRID ─────────────────────────────────────────────── --}}
    {{-- Click a card to open the full course detail page. Courses submitted
         by faculty appear here with Approve / Denied buttons until reviewed. --}}
    @if ($courses->isEmpty())
        <div class="empty-state">No courses yet. Courses created by faculty will appear here for review.</div>
    @else
    <div class="course-grid">
        @foreach ($courses as $course)
        <div class="course-card {{ $course->status_key === 'pending' ? 'is-pending' : '' }}"
             onclick="window.location='{{ route('admin.courses.show', $course->id) }}'">

            <span class="approval-pill approval-{{ $course->status_key }}">
                <span class="dot"></span>{{ $course->status }}
            </span>

            <div class="course-card-title">{{ $course->title }}</div>

            <div class="course-card-meta">
                {{ $course->students }} Students . {{ $course->faculty }} Faculty .<br>
                Badge: {{ $course->badge }}<br>
                <span style="color:var(--text-sub);">By {{ $course->instructor ?? 'Faculty' }}</span>
            </div>

            <div class="progress-track">
                <div class="progress-fill" style="width: {{ $course->percent }}%"></div>
            </div>

            <div class="course-card-pct">{{ $course->percent }}% Complete</div>

            @if ($course->status_key === 'pending')
            <div class="course-actions">
                <form method="POST" action="{{ route('admin.courses.approve', $course->id) }}" onclick="event.stopPropagation();" style="flex:1;display:flex;">
                    @csrf
                    <button type="submit" class="btn-approve" onclick="event.stopPropagation();">Approve</button>
                </form>
                <form method="POST" action="{{ route('admin.courses.deny', $course->id) }}" onclick="event.stopPropagation();" style="flex:1;display:flex;">
                    @csrf
                    <button type="submit" class="btn-deny" onclick="event.stopPropagation(); return confirm('Deny this course? It will not be published.');">Denied</button>
                </form>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

</main>
</div>{{-- /layout --}}

{{-- ── SCRIPTS ─────────────────────────────────────────────────────────── --}}
<script>
    /* Accordion toggle — shared with the other admin pages. */
    function toggleSection(id) {
        const section = document.getElementById(id);
        if (!section || section.classList.contains('locked')) return;
        section.classList.toggle('open');
    }
</script>

</body>
</html>