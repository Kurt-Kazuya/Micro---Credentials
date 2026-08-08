{{--
    resources/views/dashboard.blade.php

    Single self-contained Blade view (no layout file needed).

    Expected data from the controller, e.g.:

    return view('dashboard', [
        'user'   => $user,                 // Auth::user() - needs ->name
        'stats'  => [
            'active_courses' => $activeCoursesCount,
            'completed'      => $completedCount,
            'badges_earned'  => $badgesEarnedCount,
            'certificates'   => $certificatesCount,
        ],
        'courses'  => $enrolledCourses,     // collection of Course models
        'progress' => $progressItems,       // collection for the "Progress" side panel
        'badges'   => $earnedBadges,        // collection for the "Badges" side panel
    ]);

    Each $course is expected to expose:
        ->id, ->title, ->category, ->thumbnail_url, ->progress_percent
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Upskill</title>
<style>
    :root{
        --navy:#13176b;
        --navy-deep:#0c0f4d;
        --gold:#dba617;
        --gold-dark:#c4930f;
        --cyan:#7fe9e3;
        --thumb:#d8e3f8;
        --ink:#13176b;
        --muted:#6b7280;
        --line:#e5e7eb;
        --shadow: 0 10px 25px rgba(19,23,107,0.08);
    }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI", Roboto, Helvetica, Arial, sans-serif;color:var(--ink);margin:0;background:#fff;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}

    /* Topbar */
    .topbar{background:var(--navy);display:flex;align-items:center;justify-content:space-between;padding:14px 28px;gap:20px;}
    .brand{display:flex;align-items:center;gap:14px;color:#fff;white-space:nowrap;}
    .brand .logo{width:46px;height:46px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .brand .logo svg{width:30px;height:30px;}
    .brand .logo img{width:100%;height:100%;object-fit:contain;border-radius:50%;padding:3px;}
    .brand h1{font-size:24px;letter-spacing:1px;margin:0;font-weight:800;}
    .nav-pills{display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;align-items:center;}
    .nav-pills a{background:transparent;color:#fff;font-weight:700;padding:10px 18px;border-radius:10px;font-size:15px;transition:color .15s ease, background-color .15s ease;}
    .nav-pills a:hover{color:var(--gold);}
    .nav-pills a.is-active{color:var(--gold);background:rgba(255,255,255,0.08);}
    .search-box{display:flex;align-items:center;gap:10px;background:#fff;border-radius:999px;padding:10px 18px;min-width:240px;color:var(--muted);}
    .search-box input{border:none;outline:none;font-size:15px;width:100%;color:var(--ink);background:transparent;}
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}

    /* Layout */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}

    /* Sidebar */
    .sidebar{background:var(--navy);padding:26px 16px;display:flex;flex-direction:column;gap:6px;margin:24px 10px 24px 24px;border-radius:22px;box-shadow:0 16px 34px rgba(19,23,107,0.28);height:fit-content;position:sticky;top:20px;}
    .side-link{display:flex;align-items:center;gap:14px;padding:14px;border-radius:14px;font-weight:700;font-size:16px;color:#fff;transition:color .15s ease;}
    /* hover: text + icon turn gold (non-active links) */
    .side-link:not(.active):hover{color:var(--gold);}
    .side-link:not(.active):hover .side-icon-box svg{color:var(--gold);}
    .side-link svg{width:26px;height:26px;flex-shrink:0;}
    .side-link.active{background:var(--cyan);color:var(--navy);}
    .side-icon-box{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .side-link.active .side-icon-box svg{color:var(--navy);width:26px;height:26px;}
    .side-link:not(.active) .side-icon-box svg{color:#fff;width:26px;height:26px;transition:color .15s ease;}

    /* Main */
    .main{padding:32px 36px 60px;}
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:28px;}
    .page-head h2{font-size:30px;margin:0 0 6px;color:var(--navy);}
    .page-head p{margin:0;color:var(--muted);font-size:15px;}
    .btn-outline{background:#fff;border:1.5px solid #c9ccdb;color:#9aa0b4;font-weight:600;padding:12px 24px;border-radius:10px;font-size:15px;}

    /* Stats */
    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;margin-bottom:36px;}
    /* Gradient stat cards (same design as course Analytics) */
    .stat-card{position:relative;border-radius:18px;box-shadow:var(--shadow);padding:30px 22px;text-align:center;color:#fff;overflow:hidden;min-height:120px;display:flex;flex-direction:column;align-items:center;justify-content:center;}
    .stat-card.c-navy{background:linear-gradient(135deg,var(--navy) 0%,#2a30b0 100%);}
    .stat-card.c-gold{background:linear-gradient(135deg,var(--gold-dark) 0%,#f0c14b 100%);}
    .stat-card.c-cyan{background:linear-gradient(135deg,#2fb3ab 0%,var(--cyan) 100%);}
    .stat-card.c-deep{background:linear-gradient(135deg,var(--navy-deep) 0%,var(--navy) 100%);}
    .stat-top{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:14px;position:relative;z-index:1;}
    .stat-top svg{width:42px;height:42px;color:#fff;}
    .stat-top .num{font-size:44px;font-weight:800;color:#fff;line-height:1;}
    .stat-card .label{font-weight:800;font-size:17px;color:#fff;position:relative;z-index:1;}

    /* Content grid */
    .content-grid{display:grid;grid-template-columns:1fr 360px;gap:28px;}
    .courses-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;}
    .courses-head h3{font-size:24px;margin:0;color:var(--navy);}
    .courses-head .enroll-more{font-weight:700;color:var(--navy);font-size:17px;}

    .course-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:18px;display:flex;align-items:center;gap:18px;margin-bottom:20px;}
    .thumb{width:78px;height:78px;border-radius:12px;background:var(--thumb);flex-shrink:0;background-size:cover;background-position:center;}
    .course-info{flex:1;min-width:0;}
    .course-info h4{margin:0 0 4px;font-size:17px;color:var(--navy);}
    .course-info .cat{font-size:13px;color:var(--muted);margin-bottom:8px;}
    .progress-track{flex:1;height:7px;border-radius:999px;background:#e3e6f0;overflow:hidden;}
    .progress-fill{height:100%;background:var(--navy);border-radius:999px;}
    .pct{font-size:12px;color:var(--muted);margin-top:6px;}
    .btn-start{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:10px 22px;border-radius:10px;font-size:15px;flex-shrink:0;}
    .empty-state{border:1px dashed var(--line);border-radius:16px;padding:30px;text-align:center;color:var(--muted);}

    /* Side panels */
    .panel{border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);overflow:hidden;margin-bottom:24px;min-height:230px;}
    .panel-head{background:var(--gold);color:var(--navy);font-weight:800;font-size:22px;padding:14px 22px;display:flex;align-items:center;justify-content:space-between;}
    .panel-body{padding:18px 22px;color:var(--muted);font-size:14px;}
    .panel-body ul{margin:0;padding-left:18px;}
    .panel-body li{margin-bottom:8px;}

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:row;overflow-x:auto;position:static;margin:14px;border-radius:16px;}
        .stats{grid-template-columns:repeat(2,1fr);}
        .content-grid{grid-template-columns:1fr;}
    }

    /* ── Course areas (Not Yet Completed / Completed Courses) ── */
    .courses-area-title{margin:20px 0 12px;font-size:15px;font-weight:800;color:var(--navy);letter-spacing:.3px;}
    .empty-state-slim{padding:10px 2px 4px;}
    .btn-start.btn-completed{background:var(--green, #15803d);}

    /* ── Progress panel items ── */
    .prog-item{margin-bottom:16px;}
    .prog-item:last-child{margin-bottom:0;}
    .prog-item-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:6px;}
    .prog-item-title{font-weight:700;color:var(--navy);font-size:13px;}
    .prog-item-pct{font-weight:800;color:var(--gold-dark);font-size:13px;white-space:nowrap;}
    .prog-bar{height:8px;border-radius:999px;background:#e8ecf7;overflow:hidden;}
    .prog-bar-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--navy),#3b41c8);transition:width .4s ease;}
    .prog-bar-fill.prog-bar-done{background:linear-gradient(90deg,#1d9e6b,#34d399);}
    .prog-item-sub{margin-top:5px;font-size:11.5px;color:var(--muted);}

    /* ── About Me first-login modal ── */
    .am-overlay{position:fixed;inset:0;z-index:1000;background:rgba(12,15,77,.55);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;padding:20px;}
    .am-card{background:#fff;border-radius:20px;box-shadow:0 24px 60px rgba(12,15,77,.35);width:100%;max-width:520px;max-height:92vh;overflow-y:auto;}
    .am-head{background:linear-gradient(135deg,var(--navy),var(--navy-deep));color:#fff;padding:22px 26px;border-radius:20px 20px 0 0;}
    .am-head h3{margin:0;font-size:20px;letter-spacing:.3px;}
    .am-head p{margin:6px 0 0;font-size:13px;color:#c9cdf5;}
    .am-body{padding:22px 26px 26px;}
    .am-field{margin-bottom:14px;}
    .am-field label{display:block;font-size:12.5px;font-weight:700;color:var(--navy);margin-bottom:5px;}
    .am-field input,.am-field select,.am-field textarea{width:100%;border:1.5px solid var(--line);border-radius:10px;padding:9px 12px;font-size:13.5px;font-family:inherit;color:var(--ink);outline:none;transition:border-color .2s;}
    .am-field input:focus,.am-field select:focus,.am-field textarea:focus{border-color:var(--gold);}
    .am-field textarea{resize:vertical;min-height:64px;}
    .am-hint{font-size:11px;color:var(--muted);margin-top:4px;}
    .am-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .am-errors{background:#fde8e8;border:1px solid #f5b5b5;color:#b91c1c;border-radius:10px;padding:10px 14px;font-size:12.5px;margin-bottom:14px;}
    .am-actions{display:flex;gap:10px;margin-top:18px;}
    .am-save{flex:1;background:var(--gold);color:#fff;border:none;border-radius:12px;padding:12px;font-size:14px;font-weight:800;cursor:pointer;letter-spacing:.3px;transition:background .2s;}
    .am-save:hover{background:var(--gold-dark);}
    /* Skills checkbox dropdown */
    .skill-select{position:relative;}
    .skill-select-toggle{width:100%;display:flex;align-items:center;justify-content:space-between;gap:10px;border:1.5px solid var(--line);border-radius:10px;padding:9px 12px;font-size:13.5px;background:#fff;color:var(--ink);cursor:pointer;text-align:left;}
    .skill-select-toggle:hover{border-color:var(--gold);}
    .skill-select.open .skill-select-toggle{border-color:var(--gold);}
    .skill-select-label{color:var(--muted);}
    .skill-select.has-value .skill-select-label{color:var(--ink);font-weight:600;}
    .skill-select-caret{color:var(--muted);font-size:12px;transition:transform .2s;}
    .skill-select.open .skill-select-caret{transform:rotate(180deg);}
    .skill-select-menu{display:none;position:absolute;left:0;right:0;top:calc(100% + 6px);z-index:50;background:#fff;border:1.5px solid var(--line);border-radius:12px;box-shadow:0 14px 34px rgba(19,23,107,.18);max-height:220px;overflow-y:auto;padding:8px;}
    .skill-select.open .skill-select-menu{display:block;}
    .skill-option{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;font-size:13.5px;color:var(--ink);cursor:pointer;}
    .skill-option:hover{background:#f4f6fd;}
    .skill-option input{width:16px;height:16px;accent-color:var(--navy);cursor:pointer;flex-shrink:0;}
    @media (max-width:560px){.am-row{grid-template-columns:1fr;}}
</style>
</head>
<body>

<header class="topbar">
    <div class="brand">
        <span class="logo">
            <img src="{{ asset('images/PSU-Logo.png') }}" alt="PSU Logo">
        </span>
        <h1>UPSKILL</h1>
    </div>

    <nav class="nav-pills">
        <a href="{{ route('courses.index') }}" class="{{ request()->routeIs('courses.*') ? 'is-active' : '' }}">Courses</a>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
    </nav>

    <form action="{{ route('search') }}" method="GET" class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input type="text" name="q" placeholder="Search" value="{{ request('q') }}">
    </form>

    <div class="icon-cluster">
        <a href="{{ route('notifications.index') }}" class="icon-circle" title="Notifications">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
        </a>
        <a href="{{ route('profile.show') }}"
           class="icon-circle"
           title="{{ $user->name ?? 'Profile' }}"
           @if($user->avatar_url ?? null)
               style="background-image:url('{{ $user->avatar_url }}');background-size:cover;background-position:center;overflow:hidden;"
           @endif>
            @unless($user->avatar_url ?? null)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
            @endunless
        </a>
        <form action="{{ route('logout') }}" method="POST" style="display:inline-flex;align-items:center;">
            @csrf
            <button type="submit" style="background:#fff1f2;border:1px solid #fecdd3;color:#b91c1c;border-radius:999px;padding:8px 12px;font-weight:700;cursor:pointer;">Logout</button>
        </form>
    </div>
</header>

<div class="layout">

    {{-- Sidebar --}}
    <aside class="sidebar">
        <a href="{{ route('dashboard') }}" class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
            </span>
            Dashboard
        </a>
        <a href="{{ route('courses.browse') }}" class="side-link {{ request()->routeIs('courses.browse') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </span>
            Browse Courses
        </a>
        <a href="{{ route('courses.enrolled') }}" class="side-link {{ request()->routeIs('courses.enrolled') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
            </span>
            Enrolled Courses
        </a>
        <a href="{{ route('badges.index') }}" class="side-link {{ request()->routeIs('badges.*') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3 6 7 1-5 5 1.5 7L12 17l-6.5 4L7 14 2 9l7-1 3-6z"/></svg>
            </span>
            My Badges
        </a>
        <a href="{{ route('certificates.index') }}" class="side-link {{ request()->routeIs('certificates.*') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="5"/><path d="M8 13l-2 8 6-3 6 3-2-8"/></svg>
            </span>
            Certificates
        </a>
        <a href="{{ route('profile.show') }}" class="side-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
            </span>
            Profile
        </a>
        <a href="{{ route('pathways.index') }}" class="side-link {{ request()->routeIs('pathways.*') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="12" r="3"/><path d="M6 9v6"/><path d="M8.5 7.5L15.5 10.5"/><path d="M8.5 16.5L15.5 13.5"/></svg>
            </span>
            My Pathways
        </a>
        <a href="{{ route('analytics.index') }}" class="side-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><rect x="7" y="13" width="3" height="5"/><rect x="12" y="9" width="3" height="9"/><rect x="17" y="6" width="3" height="12"/></svg>
            </span>
            Analytics
        </a>
    </aside>

    {{-- Main content --}}
    <main class="main">

        <div class="page-head">
            <div>
                <h2>Welcome, {{ $user->name ?? 'Student' }}!</h2>
                <p>Track your learning journey and continue where you left off.</p>
                <p style="margin-top: 8px; color: var(--navy); font-weight: 700;">Student ID: {{ $user->student_id ?? '—' }}</p>
            </div>
            <a href="{{ route('courses.browse') }}">
                <button class="btn-outline" type="button">Explore Courses</button>
            </a>
        </div>

        {{-- Stat cards --}}
        <section class="stats">
            <div class="stat-card c-navy">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h12v20l-6-4-6 4V2z"/></svg>
                    <span class="num">{{ $stats['active_courses'] ?? 0 }}</span>
                </div>
                <div class="label">Active Courses</div>
            </div>
            <div class="stat-card c-gold">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10" fill="#fff"/><path d="M8 12.5l2.5 2.5L16 9.5" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="num">{{ $stats['completed'] ?? 0 }}</span>
                </div>
                <div class="label">Completed</div>
            </div>
            <div class="stat-card c-cyan">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="num">{{ $stats['badges_earned'] ?? 0 }}</span>
                </div>
                <div class="label">Badges Earned</div>
            </div>
            <div class="stat-card c-deep">
                <div class="stat-top">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 2l3 6 7 1-5 5 1.5 7L12 17l-6.5 4L7 14 2 9l7-1 3-6z" fill="#fff"/><path d="M9 12l2 2 4-4" stroke="#fff" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="num">{{ $stats['certificates'] ?? 0 }}</span>
                </div>
                <div class="label">Certificates</div>
            </div>
        </section>

        {{-- Courses + side panels --}}
        <div class="content-grid">

            <section class="courses-col">
                <div class="courses-head">
                    <h3>My Courses</h3>
                    <a href="{{ route('courses.browse') }}" class="enroll-more">+ Enroll More</a>
                </div>

                @php
                    $inProgressCourses = $inProgressCourses ?? collect();
                    $completedCourses  = $completedCourses ?? collect();
                @endphp

                @if ($inProgressCourses->isEmpty() && $completedCourses->isEmpty())
                    <div class="empty-state">
                        You haven't enrolled in any courses yet.
                        <br>
                        <a href="{{ route('courses.browse') }}" class="enroll-more">Browse courses to get started</a>
                    </div>
                @else
                    <h4 class="courses-area-title">Not Yet Completed</h4>
                    @forelse ($inProgressCourses as $course)
                    <div class="course-card">
                        <div class="thumb" @if($course->thumbnail_url) style="background-image:url('{{ $course->thumbnail_url }}')" @endif></div>
                        <div class="course-info">
                            <h4>{{ $course->title }}</h4>
                            @if($course->category)
                                <div class="cat">{{ $course->category }}</div>
                            @endif
                            <div class="progress-track">
                                <div class="progress-fill" style="width:{{ $course->progress_percent ?? 0 }}%"></div>
                            </div>
                            <div class="pct">{{ $course->progress_percent ?? 0 }}% Complete</div>
                        </div>
                        <a href="{{ route('courses.show', $course->id) }}">
                            <button class="btn-start" type="button">
                                {{ ($course->progress_percent ?? 0) > 0 ? 'Continue' : 'Start' }}
                            </button>
                        </a>
                    </div>
                    @empty
                        <div class="empty-state empty-state-slim">Nothing in progress right now.</div>
                    @endforelse

                    <h4 class="courses-area-title">Completed Courses</h4>
                    @forelse ($completedCourses as $course)
                    <div class="course-card">
                        <div class="thumb" @if($course->thumbnail_url) style="background-image:url('{{ $course->thumbnail_url }}')" @endif></div>
                        <div class="course-info">
                            <h4>{{ $course->title }}</h4>
                            @if($course->category)
                                <div class="cat">{{ $course->category }}</div>
                            @endif
                            <div class="progress-track">
                                <div class="progress-fill" style="width:{{ $course->progress_percent ?? 0 }}%"></div>
                            </div>
                            <div class="pct">{{ $course->progress_percent ?? 0 }}% Complete</div>
                        </div>
                        <a href="{{ route('courses.show', $course->id) }}">
                            <button class="btn-start btn-completed" type="button">Review</button>
                        </a>
                    </div>
                    @empty
                        <div class="empty-state empty-state-slim">No completed courses yet — finish a course and it will appear here.</div>
                    @endforelse
                @endif
            </section>

            <aside class="side-col">
                <div class="panel">
                    <div class="panel-head">
                        <span>Progress</span>
                        <a href="{{ route('analytics.index') }}" style="color:inherit;">--&gt;</a>
                    </div>
                    <div class="panel-body">
                        @forelse ($progress ?? [] as $item)
                            <div class="prog-item">
                                <div class="prog-item-head">
                                    <span class="prog-item-title">{{ \Illuminate\Support\Str::limit($item->title, 26) }}</span>
                                    <span class="prog-item-pct">{{ $item->progress_percent }}%</span>
                                </div>
                                <div class="prog-bar">
                                    <div class="prog-bar-fill{{ $item->is_completed ? ' prog-bar-done' : '' }}" style="width: {{ $item->progress_percent }}%;"></div>
                                </div>
                                <div class="prog-item-sub">
                                    @if ($item->is_completed)
                                        Completed
                                    @elseif ($item->progress_percent > 0)
                                        In progress · {{ $item->completed_lessons }} lesson{{ $item->completed_lessons === 1 ? '' : 's' }} done
                                    @else
                                        Not started yet
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p>No progress activity yet. Enroll in a course to start tracking your progress.</p>
                        @endforelse
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <span>Badges</span>
                    </div>
                    <div class="panel-body">
                        @forelse ($badges ?? [] as $badge)
                            @php /** @var object{name:string}|string $badge */ @endphp
                            <p>{{ $badge->name ?? $badge }}</p>
                        @empty
                            <p>No badges earned yet.</p>
                        @endforelse
                    </div>
                </div>
            </aside>

        </div>

    </main>
</div>

@if (($show_about_form ?? false) || $errors->any())
<div class="am-overlay" id="about-me-overlay">
    <div class="am-card">
        <div class="am-head">
            <h3>About Me</h3>
            <p>Tell us a bit about yourself — this personalizes your course recommendations.</p>
        </div>
        <div class="am-body">
            @if ($errors->any())
                <div class="am-errors">
                    <strong>Please fix the following:</strong>
                    <ul style="margin:6px 0 0;padding-left:18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('profile.complete') }}">
                @csrf
                <div class="am-row">
                    <div class="am-field">
                        <label for="am-dob">Date of Birth</label>
                        <input type="date" id="am-dob" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="am-field">
                        <label for="am-gender">Gender</label>
                        <select id="am-gender" name="gender" required>
                            <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select…</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Prefer not to say" {{ old('gender') === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                        </select>
                    </div>
                </div>
                <div class="am-field">
                    <label for="am-education">Education</label>
                    <select id="am-education" name="education" required>
                        <option value="" disabled {{ old('education') ? '' : 'selected' }}>Select your highest attainment…</option>
                        @foreach (['Senior High School', 'High School Graduate', 'Vocational / Technical', 'Undergraduate (College)', "Bachelor's Degree", "Master's Degree", 'Doctorate / PhD'] as $level)
                            <option value="{{ $level }}" {{ old('education') === $level ? 'selected' : '' }}>{{ $level }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="am-field">
                    <label for="am-bio">Bio</label>
                    <textarea id="am-bio" name="bio" placeholder="A short introduction about yourself…">{{ old('bio') }}</textarea>
                </div>
                @php
                    $skillOptions = $skill_options ?? [
                        'HTML', 'CSS', 'JavaScript', 'Python', 'Java', 'PHP', 'SQL',
                        'Computer Networking', 'Video Editing', 'Photo Editing', 'Graphic Design',
                        'Data Analysis', 'Excel', 'Digital Marketing', 'Writing', 'Public Speaking',
                        'Communication', 'Leadership', 'Singing', 'Dancing', 'Drawing', 'Cooking',
                        'Hacking', 'Baking',
                    ];
                    $oldHave = (array) old('skills_have', []);
                    $oldWant = (array) old('skills_want', []);
                @endphp
                <div class="am-field">
                    <label>Skills You already Have?</label>
                    <div class="skill-select" data-skill-select>
                        <button type="button" class="skill-select-toggle" onclick="toggleSkillSelect(this)">
                            <span class="skill-select-label" data-placeholder="Choose your skills…">{{ count($oldHave) ? count($oldHave) . ' selected' : 'Choose your skills…' }}</span>
                            <span class="skill-select-caret">▾</span>
                        </button>
                        <div class="skill-select-menu">
                            @foreach ($skillOptions as $skill)
                                <label class="skill-option">
                                    <input type="checkbox" name="skills_have[]" value="{{ $skill }}" {{ in_array($skill, $oldHave) ? 'checked' : '' }}>
                                    <span>{{ $skill }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="am-hint">Tick all that apply. These will also appear on your Profile skills list.</div>
                </div>
                <div class="am-field">
                    <label>Skills You want to learn</label>
                    <div class="skill-select" data-skill-select>
                        <button type="button" class="skill-select-toggle" onclick="toggleSkillSelect(this)">
                            <span class="skill-select-label" data-placeholder="Choose skills to learn…">{{ count($oldWant) ? count($oldWant) . ' selected' : 'Choose skills to learn…' }}</span>
                            <span class="skill-select-caret">▾</span>
                        </button>
                        <div class="skill-select-menu">
                            @foreach ($skillOptions as $skill)
                                <label class="skill-option">
                                    <input type="checkbox" name="skills_want[]" value="{{ $skill }}" {{ in_array($skill, $oldWant) ? 'checked' : '' }}>
                                    <span>{{ $skill }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="am-hint">We use this to recommend the most suitable courses for you.</div>
                </div>
                <div class="am-actions">
                    <button type="submit" class="am-save">Save and Continue</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif


{{-- ── Back to top (appears on long pages) ── --}}
<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
        onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>
<style>
    #back-to-top-btn{position:fixed;right:26px;bottom:26px;z-index:2000;width:48px;height:48px;border-radius:50%;border:none;background:#13176b;color:#fff;display:none;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 10px 22px rgba(19,23,107,.35);transition:transform .15s ease,background .2s ease;}
    #back-to-top-btn:hover{background:#dba617;transform:translateY(-3px);}
    #back-to-top-btn svg{width:22px;height:22px;}
</style>
<script>
    (function () {
        var btn = document.getElementById('back-to-top-btn');
        if (!btn) return;
        function toggleBackToTop() {
            btn.style.display = (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none';
        }
        window.addEventListener('scroll', toggleBackToTop, { passive: true });
        toggleBackToTop();
    })();
</script>

<script>
    // ── Skills checkbox dropdowns (About Me form) ──
    function toggleSkillSelect(btn) {
        var sel = btn.closest('[data-skill-select]');
        if (!sel) return;
        var wasOpen = sel.classList.contains('open');
        document.querySelectorAll('[data-skill-select].open').forEach(function (s) { s.classList.remove('open'); });
        if (!wasOpen) sel.classList.add('open');
    }
    function updateSkillSelectLabel(sel) {
        var n = sel.querySelectorAll('input[type="checkbox"]:checked').length;
        var label = sel.querySelector('.skill-select-label');
        if (label) label.textContent = n ? n + ' selected' : (label.dataset.placeholder || 'Choose…');
        sel.classList.toggle('has-value', n > 0);
    }
    document.querySelectorAll('[data-skill-select]').forEach(function (sel) {
        sel.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
            cb.addEventListener('change', function () { updateSkillSelectLabel(sel); });
        });
        updateSkillSelectLabel(sel);
    });
    document.addEventListener('click', function (e) {
        document.querySelectorAll('[data-skill-select].open').forEach(function (sel) {
            if (!sel.contains(e.target)) sel.classList.remove('open');
        });
    });
</script>
</body>
</html>