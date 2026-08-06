{{--
    resources/views/Student_Course_Description.blade.php

    Expected data from the controller, e.g.:

    return view('Student_Course_Description', [
        'user'              => $user,               // Auth::user() – needs ->name
        'course'            => $course,             // single Course model
        //   ->id, ->title, ->description, ->category, ->level, ->is_featured
        //   ->instructor (string name), ->duration, ->lessons_count, ->enrolled_count
        //   ->thumbnail_url, ->passing_score, ->skills (array of strings)
        //   ->objectives    (array of strings)
        'instructor_detail' => $instructorDetail,   // ->name, ->department, ->bio, ->avatar_url
        'modules'           => $modules,            // collection – each exposes:
        //   ->title, ->description, ->type ('Test'|'Video'), ->duration
        'quiz'              => $quiz ?? null,       // optional bottom quiz
        //   ->title, ->questions_count, ->passing_score
        'is_enrolled'       => $isEnrolled,         // bool
        'progress_percent'  => $progressPercent,    // int 0-100
    ]);
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $course->title ?? 'Course' }} | Upskill</title>
<style>
    :root{
        --navy:#13176b;
        --navy-deep:#0c0f4d;
        --gold:#dba617;
        --gold-dark:#c4930f;
        --gold-bg:#f5c518;
        --cyan:#7fe9e3;
        --tag-cat:#38d6cf;
        --tag-level-bg:#f3e2a6;
        --tag-feat-bg:#c8e6c9;
        --tag-feat:#2e7d32;
        --thumb:#d8e3f8;
        --ink:#13176b;
        --muted:#6b7280;
        --line:#e5e7eb;
        --shadow:0 10px 25px rgba(19,23,107,0.08);
        --card-shadow:0 4px 18px rgba(19,23,107,0.10);
    }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:var(--ink);margin:0;background:#f8f9fb;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}

    /* ── Topbar ─────────────────────────────────────────── */
    .topbar{background:var(--navy);display:flex;align-items:center;justify-content:space-between;padding:14px 28px;gap:20px;}
    .brand{display:flex;align-items:center;gap:14px;color:#fff;white-space:nowrap;}
    .brand .logo{width:46px;height:46px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
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

    /* ── Page layout ─────────────────────────────────────── */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}

    /* ── Sidebar ─────────────────────────────────────────── */
    .sidebar{background:var(--navy);padding:26px 16px;display:flex;flex-direction:column;gap:6px;margin:24px 10px 24px 24px;border-radius:22px;box-shadow:0 16px 34px rgba(19,23,107,0.28);height:fit-content;position:sticky;top:20px;}
    .side-link{display:flex;align-items:center;gap:14px;padding:14px;border-radius:14px;font-weight:700;font-size:16px;color:#fff;transition:color .15s ease;}
    /* hover: text + icon turn gold (non-active links) */
    .side-link:not(.active):hover{color:var(--gold);}
    .side-link:not(.active):hover .side-icon-box svg{color:var(--gold);}
    .side-icon-box{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .side-link.active{background:var(--cyan);color:var(--navy);}
    .side-link:not(.active) .side-icon-box svg{color:#fff;width:26px;height:26px;transition:color .15s ease;}
    .side-link.active .side-icon-box svg{color:var(--navy);width:26px;height:26px;}

    /* ── Main wrapper ────────────────────────────────────── */
    .main{padding:0 0 60px;}

    /* ── Back link ───────────────────────────────────────── */
    .breadcrumb{padding:22px 36px 0;display:flex;align-items:center;gap:8px;font-size:14px;font-weight:700;color:var(--navy);}
    .breadcrumb svg{width:16px;height:16px;}

    /* ── Hero banner (gold) ───────────────────────────────── */
    .hero{background:var(--gold);padding:32px 36px 36px;display:grid;grid-template-columns:1fr 340px;gap:36px;align-items:start;}
    .hero-left{}
    .hero-tags{display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;}
    .tag{padding:7px 18px;border-radius:999px;font-weight:700;font-size:13px;}
    .tag-cat{background:var(--tag-cat);color:#fff;}
    .tag-level{background:var(--tag-level-bg);color:var(--navy);}
    .tag-feat{background:#fff;color:var(--navy);}
    .hero-title{font-size:30px;font-weight:800;color:var(--navy);margin:0 0 10px;line-height:1.25;}
    .hero-desc{font-size:14px;color:var(--navy-deep);margin:0 0 22px;line-height:1.5;opacity:.85;}
    .skills-label{font-size:13px;font-weight:800;color:var(--navy);text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;}
    .skill-tags{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px;}
    .skill-tag{background:var(--navy);color:#fff;padding:8px 20px;border-radius:8px;font-weight:700;font-size:14px;}
    .hero-meta{display:flex;align-items:center;gap:22px;font-size:14px;font-weight:600;color:var(--navy);flex-wrap:wrap;}
    .hero-meta span{display:flex;align-items:center;gap:6px;}
    .hero-meta svg{width:16px;height:16px;}

    /* ── Enroll card (right) ─────────────────────────────── */
    .enroll-card{background:#fff;border-radius:20px;box-shadow:var(--card-shadow);overflow:hidden;position:sticky;top:20px;}
    .enroll-thumb{height:200px;background:var(--thumb);background-size:cover;background-position:center;}
    .enroll-body{padding:22px 24px;}
    .progress-label{display:flex;justify-content:space-between;align-items:center;font-size:14px;font-weight:700;color:var(--navy);margin-bottom:8px;}
    .progress-track{width:100%;height:8px;background:#e3e6f0;border-radius:999px;overflow:hidden;margin-bottom:20px;}
    .progress-fill{height:100%;background:var(--navy);border-radius:999px;transition:width .3s;}
    .enroll-form{display:block;width:100%;margin:0 0 18px;}
    .btn-enroll{display:block;width:100%;background:var(--navy);color:#fff;font-weight:800;font-size:16px;padding:15px;border:none;border-radius:12px;text-align:center;cursor:pointer;letter-spacing:.3px;}
    .btn-enroll:hover{background:var(--navy-deep);}
    .btn-continue{display:block;width:100%;background:var(--gold);color:var(--navy);font-weight:800;font-size:16px;padding:15px;border:none;border-radius:12px;text-align:center;margin-bottom:18px;cursor:pointer;}
    .enroll-perks{display:flex;flex-direction:column;gap:10px;}
    .perk{display:flex;align-items:center;gap:10px;font-size:14px;font-weight:700;color:var(--navy);}
    .perk svg{width:18px;height:18px;color:var(--gold-dark);flex-shrink:0;}

    /* ── Body section ────────────────────────────────────── */
    .body-section{padding:32px 36px 0;}
    .panels-row{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:32px;}

    /* ── Info panels ─────────────────────────────────────── */
    .info-panel{background:#fff;border-radius:18px;box-shadow:var(--shadow);overflow:hidden;}
    .info-panel-head{background:var(--gold);padding:14px 22px;font-size:18px;font-weight:800;color:var(--navy);}
    .info-panel-body{padding:20px 24px;}
    .objectives-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px;}
    .objectives-list li{display:flex;align-items:flex-start;gap:10px;font-size:14px;color:var(--ink);line-height:1.4;}
    .objectives-list li svg{width:18px;height:18px;color:var(--navy);flex-shrink:0;margin-top:1px;}
    .instructor-wrap{display:flex;align-items:center;gap:14px;margin-bottom:14px;}
    .instructor-avatar{width:52px;height:52px;border-radius:50%;background:var(--thumb);flex-shrink:0;background-size:cover;background-position:center;}
    .instructor-name{font-weight:800;font-size:16px;color:var(--navy);margin:0 0 2px;}
    .instructor-dept{font-size:13px;color:var(--muted);}
    .instructor-bio{font-size:14px;color:var(--muted);line-height:1.5;margin:0;}

    /* ── Modules section ─────────────────────────────────── */
    .modules-section{background:#fff;border-radius:18px;box-shadow:var(--shadow);overflow:hidden;margin-bottom:32px;}
    .modules-head{padding:18px 24px;border-bottom:1px solid var(--line);}
    .modules-head h3{margin:0;font-size:20px;color:var(--navy);}
    .module-item{display:flex;align-items:center;gap:16px;padding:18px 24px;border-bottom:1px solid var(--line);transition:background .15s;}
    .module-item:last-child{border-bottom:none;}
    .module-item:hover{background:#f8f9fb;}
    .module-check{width:36px;height:36px;border-radius:8px;background:var(--thumb);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .module-check svg{width:20px;height:20px;color:var(--navy);}
    .module-info{flex:1;min-width:0;}
    .module-title{font-weight:700;font-size:15px;color:var(--navy);margin:0 0 3px;}
    .module-sub{font-size:13px;color:var(--muted);}
    .module-meta{display:flex;align-items:center;gap:14px;flex-shrink:0;}
    .module-type{font-size:13px;font-weight:600;color:var(--muted);}
    .module-duration{font-size:13px;font-weight:600;color:var(--muted);}
    .module-chevron{width:20px;height:20px;color:var(--muted);}

    /* ── Quiz section ────────────────────────────────────── */
    .quiz-section{background:#fff;border-radius:18px;box-shadow:var(--shadow);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;}
    .quiz-info{}
    .quiz-title{font-weight:800;font-size:16px;color:var(--navy);margin:0 0 4px;}
    .quiz-sub{font-size:13px;color:var(--muted);}
    .btn-quiz{background:var(--navy);color:#fff;font-weight:700;font-size:14px;padding:10px 24px;border:none;border-radius:10px;}
    .btn-quiz:hover{background:var(--navy-deep);}

    /* ── Responsive ──────────────────────────────────────── */
    @media (max-width:1100px){
        .hero{grid-template-columns:1fr;gap:24px;}
        .enroll-card{position:static;}
        .panels-row{grid-template-columns:1fr;}
    }
    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:row;overflow-x:auto;position:static;margin:14px;border-radius:16px;}
    }
    @media (max-width:640px){
        .hero{padding:24px 18px;}
        .body-section{padding:20px 18px 0;}
        .breadcrumb{padding:16px 18px 0;}
    }
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════
     TOPBAR
═══════════════════════════════════════════════════ --}}
<header class="topbar">
    <div class="brand">
        <span class="logo">
            <img src="{{ asset('images/PSU-Logo.png') }}" alt="PSU Logo">
        </span>
        <h1>UPSKILL</h1>
    </div>

    <nav class="nav-pills">
        <a href="{{ route('courses.browse') }}" class="{{ request()->routeIs('courses.*') ? 'is-active' : '' }}">Courses</a>
        <a href="{{ route('dashboard') }}"      class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
    </nav>

    <form action="{{ route('search') }}" method="GET" class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
            <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
        </svg>
        <input type="text" name="q" placeholder="Search" value="{{ request('q') }}">
    </form>

    <div class="icon-cluster">
        <a href="{{ route('notifications.index') }}" class="icon-circle" title="Notifications">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.7 21a2 2 0 0 1-3.4 0"/>
            </svg>
        </a>
        <a href="{{ route('profile.show') }}"
           class="icon-circle"
           title="{{ $user->name ?? 'Profile' }}"
           @if($user->avatar_url ?? null)
               style="background-image:url('{{ $user->avatar_url }}');background-size:cover;background-position:center;overflow:hidden;"
           @endif>
            @unless($user->avatar_url ?? null)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/>
                </svg>
            @endunless
        </a>
    </div>
</header>

{{-- ═══════════════════════════════════════════════════
     GRID: SIDEBAR + MAIN
═══════════════════════════════════════════════════ --}}
<div class="layout">

    {{-- ── Sidebar ──────────────────────────────────── --}}
    <aside class="sidebar">
        <a href="{{ route('dashboard') }}" class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="9" rx="1"/>
                    <rect x="14" y="3" width="7" height="5" rx="1"/>
                    <rect x="14" y="12" width="7" height="9" rx="1"/>
                    <rect x="3" y="16" width="7" height="5" rx="1"/>
                </svg>
            </span>
            Dashboard
        </a>
        <a href="{{ route('courses.browse') }}" class="side-link {{ request()->routeIs('courses.*') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2l3 6 7 1-5 5 1.5 7L12 17l-6.5 4L7 14 2 9l7-1 3-6z"/>
                </svg>
            </span>
            My Badges
        </a>
        <a href="{{ route('certificates.index') }}" class="side-link {{ request()->routeIs('certificates.*') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="8" r="5"/>
                    <path d="M8 13l-2 8 6-3 6 3-2-8"/>
                </svg>
            </span>
            Certificates
        </a>
        <a href="{{ route('profile.show') }}" class="side-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/>
                </svg>
            </span>
            Profile
        </a>
        <a href="{{ route('pathways.index') }}" class="side-link {{ request()->routeIs('pathways.*') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="6" cy="6" r="3"/>
                    <circle cx="6" cy="18" r="3"/>
                    <circle cx="18" cy="12" r="3"/>
                    <path d="M6 9v6"/>
                    <path d="M8.5 7.5L15.5 10.5"/>
                    <path d="M8.5 16.5L15.5 13.5"/>
                </svg>
            </span>
            My Pathways
        </a>
        <a href="{{ route('analytics.index') }}" class="side-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3v18h18"/>
                    <rect x="7"  y="13" width="3" height="5"/>
                    <rect x="12" y="9"  width="3" height="9"/>
                    <rect x="17" y="6"  width="3" height="12"/>
                </svg>
            </span>
            Analytics
        </a>
    </aside>

    {{-- ── Main ─────────────────────────────────────── --}}
    <main class="main">

        {{-- Back to Browse --}}
        <div class="breadcrumb">
            <a href="{{ route('courses.browse') }}" style="display:flex;align-items:center;gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
                Back to Browse
            </a>
        </div>

        {{-- ────────────────────────────────────────────
             HERO BANNER
        ──────────────────────────────────────────── --}}
        <section class="hero">

            {{-- Left: title, description, skills, meta --}}
            <div class="hero-left">
                {{-- Tags row --}}
                <div class="hero-tags">
                    @if($course->category)
                        <span class="tag tag-cat">{{ $course->category }}</span>
                    @endif
                    @if($course->level)
                        <span class="tag tag-level">{{ $course->level }}</span>
                    @endif
                    @if($course->is_featured ?? false)
                        <span class="tag tag-feat">Featured</span>
                    @endif
                </div>

                {{-- Title + description --}}
                <h1 class="hero-title">{{ $course->title }}</h1>
                @if($course->description)
                    <p class="hero-desc">{{ $course->description }}</p>
                @endif

                {{-- Skills You'll Gain --}}
                @if(!empty($course->skills))
                    <div class="skills-label">Skills You'll Gain</div>
                    <div class="skill-tags">
                        @foreach($course->skills as $skill)
                            <span class="skill-tag">{{ $skill }}</span>
                        @endforeach
                    </div>
                @endif

                {{-- Meta row --}}
                <div class="hero-meta">
                    @if($course->instructor)
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/>
                            </svg>
                            {{ $course->instructor }}
                        </span>
                    @endif
                    @if($course->duration)
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v5l3 3"/>
                            </svg>
                            {{ $course->duration }}
                        </span>
                    @endif
                    @if($course->lessons_count)
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                <path d="M8 21h8M12 17v4"/>
                            </svg>
                            {{ $course->lessons_count }} {{ Str::plural('Module', $course->lessons_count) }}
                        </span>
                    @endif
                    @if($course->enrolled_count ?? false)
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            {{ $course->enrolled_count }} Enrolled
                        </span>
                    @endif
                </div>
            </div>

            {{-- Right: Enroll card --}}
            <div class="enroll-card">
                <div class="enroll-thumb"
                     @if($course->thumbnail_url)
                         style="background-image:url('{{ $course->thumbnail_url }}')"
                     @endif>
                </div>
                <div class="enroll-body">
                    <div class="progress-label">
                        <span>Your Progress</span>
                        <span>{{ $progress_percent ?? 0 }}%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width:{{ $progress_percent ?? 0 }}%"></div>
                    </div>

                    @if($is_enrolled ?? false)
                        <a href="{{ route('courses.learn', $course->id) }}" class="btn-continue">
                            {{ ($progress_percent ?? 0) > 0 ? 'Continue Learning' : 'Start Course' }}
                        </a>
                    @else
                        <form action="{{ route('courses.enroll', $course->id) }}" method="POST" class="enroll-form">
                            @csrf
                            <button type="submit" class="btn-enroll">Enroll Now</button>
                        </form>
                    @endif

                    <div class="enroll-perks">
                        <div class="perk">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="5"/>
                                <path d="M8 13l-2 8 6-3 6 3-2-8"/>
                            </svg>
                            Earn Digital Certificates!
                        </div>
                        <div class="perk">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2l3 6 7 1-5 5 1.5 7L12 17l-6.5 4L7 14 2 9l7-1 3-6z"/>
                            </svg>
                            Digital Badges
                        </div>
                        @if($course->passing_score ?? false)
                            <div class="perk">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 12l2 2 4-4"/>
                                    <path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2z"/>
                                </svg>
                                Passing Score: {{ $course->passing_score }}%
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </section>

        {{-- ────────────────────────────────────────────
             BODY PANELS + MODULES
        ──────────────────────────────────────────── --}}
        <div class="body-section">

            {{-- Objectives + Instructor --}}
            <div class="panels-row">

                {{-- Course Objectives --}}
                <div class="info-panel">
                    <div class="info-panel-head">Course Objectives</div>
                    <div class="info-panel-body">
                        @if(!empty($course->objectives))
                            <ul class="objectives-list">
                                @foreach($course->objectives as $objective)
                                    <li>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        {{ $objective }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p style="color:var(--muted);font-size:14px;margin:0;">No objectives listed yet.</p>
                        @endif
                    </div>
                </div>

                {{-- About the Instructor --}}
                <div class="info-panel">
                    <div class="info-panel-head">About the Instructor</div>
                    <div class="info-panel-body">
                        @if($instructor_detail ?? false)
                            <div class="instructor-wrap">
                                <div class="instructor-avatar"
                                     @if($instructor_detail->avatar_url)
                                         style="background-image:url('{{ $instructor_detail->avatar_url }}')"
                                     @endif>
                                </div>
                                <div>
                                    <p class="instructor-name">{{ $instructor_detail->name }}</p>
                                    <p class="instructor-dept">{{ $instructor_detail->department ?? '' }}</p>
                                </div>
                            </div>
                            @if($instructor_detail->bio ?? false)
                                <p class="instructor-bio">{{ $instructor_detail->bio }}</p>
                            @endif
                        @elseif($course->instructor)
                            <div class="instructor-wrap">
                                <div class="instructor-avatar"></div>
                                <div>
                                    <p class="instructor-name">{{ $course->instructor }}</p>
                                    <p class="instructor-dept">Instructor</p>
                                </div>
                            </div>
                        @else
                            <p style="color:var(--muted);font-size:14px;margin:0;">No instructor information available.</p>
                        @endif
                    </div>
                </div>

            </div>

            {{-- Modules / Lessons list --}}
            @if(($modules ?? collect())->count())
                <div class="modules-section">
                    <div class="modules-head">
                        <h3>Course Content</h3>
                    </div>

                    @foreach($modules as $module)
                        <div class="module-item">
                            <div class="module-check">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="3"/>
                                </svg>
                            </div>
                            <div class="module-info">
                                <p class="module-title">{{ $module->title }}</p>
                                @if($module->description ?? false)
                                    <p class="module-sub">{{ $module->description }}</p>
                                @endif
                            </div>
                            <div class="module-meta">
                                @if($module->type ?? false)
                                    <span class="module-type">{{ $module->type }}</span>
                                @endif
                                @if($module->duration ?? false)
                                    <span class="module-duration">{{ $module->duration }}</span>
                                @endif
                                <svg class="module-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 9l6 6 6-6"/>
                                </svg>
                            </div>
                        </div>
                    @endforeach

                    {{-- Inline quiz (bottom of modules list) --}}
                    @if($quiz ?? false)
                        <div style="padding:16px 24px;background:#f8f9fb;border-top:1px solid var(--line);">
                            <div class="quiz-section" style="box-shadow:none;padding:0;background:transparent;">
                                <div class="quiz-info">
                                    <p class="quiz-title">{{ $quiz->title }}</p>
                                    <p class="quiz-sub">
                                        {{ $quiz->questions_count }} {{ Str::plural('Question', $quiz->questions_count) }}
                                        @if($quiz->passing_score ?? false)
                                            · Pass {{ $quiz->passing_score }}%
                                        @endif
                                    </p>
                                </div>
                                @if($is_enrolled ?? false)
                                    <a href="{{ route('quiz.show', $quiz->id) }}">
                                        <button class="btn-quiz" type="button">Quiz</button>
                                    </a>
                                @else
                                    <button class="btn-quiz" type="button" disabled
                                            style="opacity:.5;cursor:not-allowed;">Quiz</button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endif

        </div>{{-- /.body-section --}}

    </main>
</div>


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
</body>
</html>