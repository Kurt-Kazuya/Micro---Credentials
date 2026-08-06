{{--
    resources/views/Faculty_My_Courses.blade.php

    Faculty › My Courses — ONE self-contained Blade view with TWO modes:

      • $mode = 'list'    → the My Courses list (default)
                            URL: /Faculty-mycourses          (faculty.courses)
      • $mode = 'manage'  → the "Managing Course" screen shown after
                            clicking a course's Manage button
                            URL: /Faculty-mycourses/manage   (faculty.courses.manage)

    ⚠ STANDALONE: the only connections are between these two modes and back
      to the Faculty Dashboard (route 'faculty.dashboard'). Every other
      button / link / input is intentionally non-functioning (href="#" or
      plain <button type="button">) until the real routes are wired up.

    LIST mode data:
        'user', 'courses' (each: ->title, ->description, ->status,
        ->students_count, ->modules_count, ->lessons_count, ->thumbnail_url)

    MANAGE mode data:
        'user',
        'course'   (->title, ->status, ->level, ->students_count, ->modules_count),
        'modules'  (each: ->title, ->subtitle,
                    ->lessons (->title, ->meta, ->thumbnail_url),
                    ->quiz (->title, ->questions_count, ->passing_score)),
        'students' (each: ->name, ->student_id, ->status, ->avatar_url),
        'quizAverages' (each: ->title, ->percent)
--}}
@php $mode = $mode ?? 'list'; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $mode === 'quiz' ? 'Add Quiz' : ($mode === 'manage' ? 'Manage Course' : 'My Courses') }} | Upskill</title>
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
        --green:#16a34a;
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
    .side-link svg{width:26px;height:26px;flex-shrink:0;}
    .side-link.active{background:var(--cyan);color:var(--navy);}
    .side-icon-box{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(255,255,255,0.14);}
    .side-icon-box svg{color:#fff;width:22px;height:22px;transition:color .15s ease;}
    .side-link.active .side-icon-box{background:var(--navy);}
    .side-link.plain .side-icon-box{background:transparent;}
    .side-link.plain .side-icon-box svg{color:#fff;width:26px;height:26px;}
    /* hover: text + icon turn gold (non-active links) */
    .side-link:not(.active):hover{color:var(--gold);}
    .side-link:not(.active):hover .side-icon-box svg{color:var(--gold);}
    .side-divider{border:none;border-top:1px solid rgba(255,255,255,0.25);margin:18px 6px;}

    /* Main */
    .main{padding:32px 36px 60px;}

    /* ── LIST mode ─────────────────────────────────────────────────────── */
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:28px;}
    .page-head h2{font-size:26px;margin:0 0 4px;color:var(--navy);}
    .page-head p{margin:0;color:var(--muted);font-size:13px;font-weight:600;}
    .btn-outline{background:#fff;border:1.5px solid #c9ccdb;color:#9aa0b4;font-weight:600;padding:12px 28px;border-radius:999px;font-size:15px;}

    .course-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:22px 24px;display:flex;align-items:center;gap:22px;margin-bottom:24px;}
    .thumb{width:88px;height:88px;border-radius:12px;background:var(--thumb);flex-shrink:0;background-size:cover;background-position:center;}
    .course-info{flex:1;min-width:0;}
    .course-info h3{margin:0 0 8px;font-size:21px;color:var(--navy);}
    .course-info .desc{font-size:12px;color:var(--navy);opacity:.85;line-height:1.5;max-width:520px;margin-bottom:14px;}
    .course-meta{display:flex;align-items:center;gap:26px;flex-wrap:wrap;}
    .status-chip{display:inline-block;font-size:12px;font-weight:700;padding:8px 24px;border-radius:999px;border:1.5px solid #c9ccdb;background:#fff;color:var(--ink);}
    .status-chip.draft{background:#fde68a;border-color:#fde68a;color:#713f12;}
    .meta-item{text-align:center;font-size:11px;color:var(--muted);line-height:1.3;}
    .meta-item .meta-num{display:block;font-weight:800;font-size:14px;color:var(--ink);}
    .card-actions{display:flex;flex-direction:column;gap:12px;flex-shrink:0;}
    .btn-action{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:10px 30px;border-radius:999px;font-size:15px;min-width:150px;display:block;width:100%;text-align:center;}
    .empty-state{border:1px dashed var(--line);border-radius:16px;padding:30px;text-align:center;color:var(--muted);font-size:14px;}

    /* ── MANAGE mode ───────────────────────────────────────────────────── */
    .course-banner{background:var(--navy);border-radius:18px;box-shadow:var(--shadow);padding:22px 28px;display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;color:#fff;margin-bottom:32px;}
    .banner-info .kicker{font-size:13px;font-weight:700;opacity:.85;margin-bottom:6px;}
    .banner-info h2{margin:0 0 10px;font-size:26px;color:#fff;}
    .banner-meta{display:flex;gap:18px;flex-wrap:wrap;font-size:13px;font-weight:700;opacity:.9;}
    .banner-actions{display:flex;flex-direction:column;gap:10px;align-items:stretch;}
    .banner-actions .row{display:flex;gap:10px;}
    .btn-pill{background:#fff;border:none;color:var(--navy);font-weight:700;padding:10px 26px;border-radius:999px;font-size:14px;flex:1;}

    .section-title{font-size:24px;margin:0 0 16px;color:var(--navy);}
    .section-head{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:16px;}
    .section-head .section-title{margin:0;}
    .pill-muted{display:inline-block;font-size:12px;font-weight:700;padding:10px 24px;border-radius:999px;border:1.5px solid #c9ccdb;background:#fff;color:#9aa0b4;}
    .content-grid{display:grid;grid-template-columns:1fr 380px;gap:28px;align-items:start;}
    .content-grid.single{grid-template-columns:1fr;}
    .empty-modules{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:110px 30px;text-align:center;}
    .empty-modules h4{margin:0 0 12px;font-size:17px;color:#111;}
    .empty-modules p{margin:0;font-size:14px;color:#9aa0b4;font-weight:600;}

    .add-module-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:18px;display:flex;flex-direction:column;gap:14px;margin-bottom:24px;}
    .add-module-inputs{display:flex;gap:14px;flex-wrap:wrap;}
    .input-outline{flex:1;min-width:170px;border:1.5px solid #c9ccdb;border-radius:999px;padding:12px 20px;font-size:14px;font-weight:600;color:var(--ink);outline:none;font-family:inherit;}
    .input-outline::placeholder{color:#9aa0b4;}
    .btn-navy{background:var(--navy);border:none;color:#fff;font-weight:700;padding:12px 26px;border-radius:999px;font-size:14px;align-self:flex-end;}

    .module-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:14px;margin-bottom:26px;display:flex;flex-direction:column;gap:12px;}
    .lesson-row{border:1px solid var(--line);border-radius:14px;padding:12px 14px;display:flex;align-items:center;gap:14px;}
    .lesson-thumb{width:52px;height:52px;border-radius:10px;background:var(--thumb);flex-shrink:0;background-size:cover;background-position:center;}
    .lesson-info{flex:1;min-width:0;}
    .lesson-info h4{margin:0 0 3px;font-size:14px;color:var(--navy);}
    .lesson-info .sub{font-size:11px;color:var(--muted);}
    .btn-x{width:36px;height:36px;border-radius:50%;border:1.5px solid var(--navy);background:#fff;color:var(--navy);font-weight:800;font-size:15px;flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .module-head .lesson-info h4{font-size:15px;}
    .btn-add-lesson{background:#fff;border:1.5px solid #c9ccdb;color:var(--navy);font-weight:700;padding:9px 20px;border-radius:999px;font-size:13px;flex-shrink:0;}
    .lessons-empty{min-height:72px;}
    .quiz-none{font-size:13px;color:#9aa0b4;font-weight:600;}

    /* Inline "Add Lesson" form (shown inside a module card) */
    .lesson-form{display:flex;flex-direction:column;gap:14px;padding:14px 4px 6px;}
    .lesson-form .row2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .textarea-outline{width:100%;border:1.5px solid #c9ccdb;border-radius:14px;padding:14px 18px;min-height:95px;font-family:inherit;font-size:13px;font-weight:600;color:var(--ink);outline:none;resize:vertical;}
    .textarea-outline::placeholder{color:#9aa0b4;font-weight:500;}
    .sel-wrap{position:relative;}
    .sel-wrap select{appearance:none;-webkit-appearance:none;-moz-appearance:none;width:100%;border:1.5px solid #c9ccdb;border-radius:999px;padding:12px 44px 12px 20px;font-size:13px;font-weight:600;color:var(--ink);outline:none;font-family:inherit;background:#fff;cursor:pointer;}
    .sel-wrap::after{content:"";position:absolute;right:20px;top:50%;width:9px;height:9px;border-right:2.5px solid var(--ink);border-bottom:2.5px solid var(--ink);transform:translateY(-70%) rotate(45deg);pointer-events:none;}
    .lesson-form .actions{display:flex;gap:14px;align-items:center;flex-wrap:wrap;}
    .lesson-form .actions .input-outline{flex:0 1 200px;min-width:150px;}
    .file-row{display:flex;align-items:center;border:1.5px solid #c9ccdb;border-radius:10px;overflow:hidden;max-width:340px;background:#fff;}
    .file-row .file-btn{background:#f3f4f6;border:none;border-right:1.5px solid #c9ccdb;padding:10px 16px;font-size:12px;font-weight:700;color:var(--ink);white-space:nowrap;cursor:pointer;}
    .file-row .file-name{padding:10px 14px;font-size:12px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .file-row input[type=file]{display:none;}
    .view-file{display:inline-block;font-size:11px;font-weight:700;color:var(--navy);border:1.5px solid var(--navy);border-radius:999px;padding:5px 14px;flex-shrink:0;}
    .btn-save-lesson{background:var(--navy);border:none;color:#fff;font-weight:700;padding:12px 30px;border-radius:999px;font-size:14px;}
    .btn-cancel-outline{background:#fff;border:1.5px solid #c9ccdb;color:var(--ink);font-weight:700;padding:12px 28px;border-radius:999px;font-size:14px;display:inline-block;}
    @media (max-width:980px){ .lesson-form .row2{grid-template-columns:1fr;} }
    /* ── QUIZ BUILDER mode ─────────────────────────────────────────────── */
    .back-link{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--navy);margin-bottom:8px;}
    .quiz-head{font-size:24px;margin:0 0 20px;color:var(--navy);}
    .quiz-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:22px;margin-bottom:26px;}
    .q-label{display:block;font-size:11px;font-weight:800;color:var(--navy);margin-bottom:7px;}
    .q-label .req{color:#dc2626;}
    .grid3{display:grid;grid-template-columns:2fr 1fr 1fr;gap:16px;margin-bottom:18px;}
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;}
    .q-head{display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:18px;}
    .q-head h3{margin:0;font-size:21px;color:var(--navy);}
    .q-head .type-label{font-size:12px;font-weight:800;color:var(--navy);margin-left:auto;}
    .q-head .sel-wrap{min-width:190px;}
    .q-head .points{width:110px;}
    .question-input{max-width:420px;}
    .choice-row{display:flex;align-items:center;gap:16px;margin-bottom:14px;}
    .choice-letter{font-weight:800;font-size:16px;color:var(--navy);width:26px;flex-shrink:0;}
    .choice-row .input-outline{flex:1;max-width:420px;}
    .mark-correct{display:flex;align-items:center;gap:10px;font-size:13px;font-weight:800;color:var(--ink);cursor:pointer;white-space:nowrap;}
    .mark-correct input{display:none;}
    .radio-dot{width:20px;height:20px;border-radius:50%;border:1.5px solid #c9ccdb;background:#fff;flex-shrink:0;}
    .mark-correct input:checked + .radio-dot{background:#22c55e;border-color:#22c55e;}
    .btn-add-choice{width:100%;background:#fff;border:1.5px solid #c9ccdb;border-radius:999px;padding:12px;font-weight:700;color:var(--ink);font-size:13px;margin-top:6px;}
    .tf-label{flex:1;max-width:420px;font-weight:700;font-size:14px;color:var(--ink);border:1.5px solid #c9ccdb;border-radius:999px;padding:12px 20px;background:#f9fafb;}
    .save-quiz-row{display:flex;justify-content:flex-end;}
    @media (max-width:980px){ .grid3{grid-template-columns:1fr;} .grid2{grid-template-columns:1fr;} }

    .quiz-row{border:1px solid var(--line);border-radius:14px;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;}
    .quiz-info h4{margin:0 0 3px;font-size:14px;color:var(--navy);}
    .quiz-info .sub{font-size:11px;color:var(--muted);}
    .btn-outline-navy{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:9px 20px;border-radius:10px;font-size:13px;flex-shrink:0;}

    .student-card{border:1px solid var(--line);border-radius:14px;box-shadow:var(--shadow);padding:14px 16px;display:flex;align-items:center;gap:14px;margin-bottom:14px;}
    .student-thumb{width:48px;height:48px;border-radius:10px;background:var(--thumb);flex-shrink:0;background-size:cover;background-position:center;}
    .student-info{flex:1;min-width:0;}
    .student-info h4{margin:0 0 3px;font-size:14px;color:var(--navy);}
    .student-info .sub{font-size:11px;color:var(--muted);}
    .status-pill{display:inline-block;font-size:12px;font-weight:700;padding:8px 22px;border-radius:999px;border:1.5px solid var(--navy);background:#fff;color:var(--navy);flex-shrink:0;}

    .panel{border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);overflow:hidden;margin-top:28px;}
    .panel-head{background:var(--gold);color:var(--navy);font-weight:800;font-size:20px;padding:14px 22px;}
    .panel-body{padding:18px 22px;}
    .avg-row{display:flex;align-items:center;gap:14px;margin-bottom:14px;}
    .avg-row:last-child{margin-bottom:0;}
    .avg-row .avg-title{font-weight:800;font-size:14px;color:var(--navy);white-space:nowrap;}
    .avg-track{flex:1;height:7px;border-radius:999px;background:#e3e6f0;overflow:hidden;}
    .avg-fill{height:100%;background:var(--green);border-radius:999px;}
    .avg-pct{font-weight:800;font-size:14px;color:var(--navy);white-space:nowrap;}

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:row;overflow-x:auto;position:static;margin:14px;border-radius:16px;}
        .content-grid{grid-template-columns:1fr;}
        .banner-actions{width:100%;}
        .course-card{flex-direction:column;align-items:flex-start;}
        .card-actions{flex-direction:row;width:100%;}
        .btn-action{flex:1;min-width:0;}
    }
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

    {{-- "Courses" → My Courses list · "Dashboard" → Faculty Dashboard --}}
    <nav class="nav-pills">
        <a href="{{ route('faculty.courses') }}" class="is-active">Courses</a>
        <a href="{{ route('faculty.dashboard') }}">Dashboard</a>
    </nav>

    {{-- ⚠ Not connected — search does nothing yet --}}
    <div class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input type="text" name="q" placeholder="Search">
    </div>

    <div class="icon-cluster">
        <a href="#" class="icon-circle" title="Notifications">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
        </a>
        <a href="{{ route('faculty.profile') }}" class="icon-circle" title="{{ $user->name ?? 'Profile' }}"
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

    {{-- Sidebar — Dashboard & My Courses connected, everything else placeholder --}}
    <aside class="sidebar">
        <a href="{{ route('faculty.dashboard') }}" class="side-link">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
            </span>
            Dashboard
        </a>
        <a href="{{ route('faculty.courses') }}" class="side-link active">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </span>
            My Courses
        </a>
        {{-- ✅ Connected — goes to Faculty › Create Courses only --}}
        <a href="{{ route('faculty.create') }}" class="side-link">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
            </span>
            Create Courses
        </a>

        <hr class="side-divider">

        {{-- ✅ Connected — goes to Faculty › Profile only --}}
        <a href="{{ route('faculty.profile') }}" class="side-link plain">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
            </span>
            Profile
        </a>
        {{-- ✅ Connected — goes to Faculty › Analytics only --}}
        <a href="{{ route('faculty.analytics') }}" class="side-link plain">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><rect x="7" y="13" width="3" height="5"/><rect x="12" y="9" width="3" height="9"/><rect x="17" y="6" width="3" height="12"/></svg>
            </span>
            Analytics
        </a>
    </aside>

    {{-- Main content --}}
    <main class="main">

    @if ($mode === 'manage')

        {{-- ══════════════ MANAGE MODE — "Managing Course" screen ══════════════ --}}

        <section class="course-banner">
            <div class="banner-info">
                <div class="kicker">Managing Course</div>
                <h2>{{ $course->title ?? 'Course Title' }}</h2>
                <div class="banner-meta">
                    <span>{{ $course->students_count ?? 0 }} Students</span>
                    <span>{{ $course->modules_count ?? 0 }} {{ ($course->modules_count ?? 0) == 1 ? 'Module' : 'Modules' }}</span>
                    <span>{{ $course->level ?? '' }}</span>
                </div>
            </div>
            <div class="banner-actions">
                <div class="row">
                    {{-- ⚠ Not connected — no actions yet --}}
                    <button class="btn-pill" type="button">{{ $course->status ?? 'Draft' }}</button>
                    <button class="btn-pill" type="button">Edit</button>
                </div>
                <a href="{{ route('faculty.analytics', ['course_id' => $course->id ?? null]) }}"><button class="btn-pill" type="button">Analytics</button></a>
            </div>
        </section>

        @php $hasStudents = $students->isNotEmpty() || $quizAverages->isNotEmpty(); @endphp

        @unless($hasStudents)
            <div class="section-head">
                <h3 class="section-title">Course Modules</h3>
                <span class="pill-muted">No Students enrolled yet</span>
            </div>
        @endunless

        <div class="content-grid {{ $hasStudents ? '' : 'single' }}">

            {{-- ── Left column: Course Modules ── --}}
            <section>
                @if($hasStudents)
                    <h3 class="section-title">Course Modules</h3>
                @endif

                {{-- ✅ Real form — stores the module (session for now) and
                     reloads this Managing Course screen with the new module --}}
                <form class="add-module-card" method="POST" action="{{ route('faculty.module.store', $course->id) }}">
                    @csrf
                    <div class="add-module-inputs">
                        <input class="input-outline" type="text" name="module_title" placeholder="New Module Title *" required>
                        <input class="input-outline" type="text" name="module_description" placeholder="Description (Optional)">
                    </div>
                    <button class="btn-navy" type="submit">+ Add Modules</button>
                </form>

                @forelse ($modules as $module)
                    <div class="module-card">
                        <div class="lesson-row module-head">
                            <div class="lesson-thumb"></div>
                            <div class="lesson-info">
                                <h4>{{ $module->title }}</h4>
                                @if($module->subtitle ?? null)
                                    <div class="sub">{{ $module->subtitle }}</div>
                                @endif
                            </div>
                            {{-- ✅ Opens the inline Add Lesson form for THIS module --}}
                            <button class="btn-add-lesson" type="button" onclick="toggleLessonForm({{ $module->idx }}, true)">+ Add Lesson</button>
                            {{-- ✅ Deletes THIS module and everything in it (with confirmation) --}}
                            <form method="POST" style="margin:0;display:flex;"
                                  action="{{ route('faculty.module.delete', [$course->id, $module->key]) }}"
                                  onsubmit="return confirm('Delete this module? Its lessons and quiz will be removed too.');">
                                @csrf
                                <button class="btn-x" type="submit" title="Remove module">✕</button>
                            </form>
                        </div>

                        @php $showLessonForm = ($addLessonIndex ?? null) === $module->idx
    || (int) session('open_lesson_form') === (int) $module->idx; @endphp

                        {{-- ✅ Inline Add Lesson form — opened by the "+ Add Lesson"
                             button above · Save stores the lesson (session for now) ·
                             Cancel closes the form without saving --}}
                        <form class="lesson-form" id="lesson-form-{{ $module->idx }}"
                              style="display:{{ $showLessonForm ? 'flex' : 'none' }};"
                              method="POST" enctype="multipart/form-data"
                              action="{{ route('faculty.lesson.store', [$course->id, $module->idx]) }}">
                            @csrf
                            <div class="row2">
                                <input class="input-outline" type="text" name="lesson_title" placeholder="Lesson title *" value="{{ old('lesson_title') }}" required>
                                {{-- ✅ Upload the lesson file — PDF, video, image or text file.
                                     The lesson type is detected automatically from the file. --}}
                                <label class="file-row" style="max-width:100%;">
                                    <span class="file-btn">Choose File</span>
                                    <span class="file-name" id="lesson-file-name-{{ $module->idx }}">No File Chosen</span>
                                    <input type="file" name="lesson_file" id="lesson-file-{{ $module->idx }}"
                                           accept=".pdf,.txt,video/*,image/*,audio/*,.zip,.doc,.docx,.ppt,.pptx,.xls,.xlsx"
                                           onchange="lessonFileChanged(this, {{ $module->idx }})">
                                </label>
                            </div>

                            {{-- Optional written content (lessons without a file become Text lessons) --}}
                            <textarea class="textarea-outline" name="lesson_content" placeholder="Lesson Content ( Optional )"></textarea>

                            @if ($errors->has('lesson_file') && (int) session('open_lesson_form') === (int) $module->idx)
                            <div style="background:#fdecea;border:1px solid #f2b8b5;color:#a93226;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:600;">
                                {{ $errors->first('lesson_file') }}
                            </div>
                            @endif

                            <div class="actions">
                                <input class="input-outline" type="number" name="duration" placeholder="Duration ( Min )" min="0">
                                <button class="btn-save-lesson" type="submit">Save Lesson</button>
                                <button class="btn-cancel-outline" type="button" onclick="toggleLessonForm({{ $module->idx }}, false)">Cancel</button>
                            </div>
                        </form>

                        @forelse ($module->lessons ?? [] as $lesson)
                            <div class="lesson-row">
                                <div class="lesson-thumb" @if($lesson->thumbnail_url ?? null) style="background-image:url('{{ $lesson->thumbnail_url }}')" @endif></div>
                                <div class="lesson-info">
                                    <h4>
                                        @if($lesson->file_url ?? null)
                                            <a href="{{ asset($lesson->file_url) }}" target="_blank" style="text-decoration:underline;">{{ $lesson->title }}</a>
                                        @else
                                            {{ $lesson->title }}
                                        @endif
                                    </h4>
                                    @if($lesson->meta ?? null)
                                        <div class="sub">{{ $lesson->meta }}</div>
                                    @endif
                                </div>
                                @if($lesson->file_url ?? null)
                                    {{-- ✅ Opens the uploaded file in a new tab --}}
                                    <a class="view-file" href="{{ asset($lesson->file_url) }}" target="_blank">View File</a>
                                @endif
                                {{-- ✅ Deletes THIS lesson (with confirmation) --}}
                                <form method="POST" style="margin:0;display:flex;"
                                      action="{{ route('faculty.lesson.delete', [$course->id, $module->idx, $lesson->key ?? 'seed-'.$loop->index]) }}"
                                      onsubmit="return confirm('Delete this lesson?');">
                                    @csrf
                                    <button class="btn-x" type="submit" title="Remove lesson">✕</button>
                                </form>
                            </div>
                        @empty
                            {{-- No lessons yet — empty area like the mockup --}}
                            <div class="lessons-empty" id="lessons-empty-{{ $module->idx }}"
                                 style="display:{{ $showLessonForm ? 'none' : 'block' }};"></div>
                        @endforelse

                        {{-- Quiz footer row --}}
                        <div class="quiz-row" id="quiz-row-{{ $module->idx }}">
                            @if($module->quiz ?? null)
                                <div class="quiz-info">
                                    <h4>{{ $module->quiz->title }}</h4>
                                    <div class="sub">{{ $module->quiz->questions_count }} Questions · Pass {{ $module->quiz->passing_score }}%</div>
                                </div>
                                {{-- ✅ Opens the quiz builder prefilled for editing --}}
                                <a class="btn-outline-navy" style="display:inline-block;"
                                   href="{{ route('faculty.quiz.create', [$course->id, $module->idx]) }}">Edit Quiz</a>
                            @else
                                <span class="quiz-none">No quiz yet.</span>
                                {{-- ✅ Opens the Add Quiz builder for THIS module --}}
                                <a class="btn-outline-navy" style="display:inline-block;"
                                   href="{{ route('faculty.quiz.create', [$course->id, $module->idx]) }}">Add Quiz</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-modules">
                        <h4>No Modules yet</h4>
                        <p>Add your first Module above</p>
                    </div>
                @endforelse
            </section>

            {{-- ── Right column: Enrolled Students + Quiz Average (hidden when empty) ── --}}
            @if($hasStudents)
            <aside>
                <h3 class="section-title">Enrolled Students</h3>

                @forelse ($students as $student)
                    <div class="student-card">
                        <div class="student-thumb" @if($student->avatar_url ?? null) style="background-image:url('{{ $student->avatar_url }}')" @endif></div>
                        <div class="student-info">
                            <h4>{{ $student->name }}</h4>
                            <div class="sub">{{ $student->student_id }}</div>
                        </div>
                        <span class="status-pill">{{ $student->status }}</span>
                    </div>
                @empty
                    <div class="empty-state">No students enrolled yet.</div>
                @endforelse

                <div class="panel">
                    <div class="panel-head">Quiz Average</div>
                    <div class="panel-body">
                        @forelse ($quizAverages as $avg)
                            <div class="avg-row">
                                <span class="avg-title">{{ $avg->title }}</span>
                                <div class="avg-track">
                                    <div class="avg-fill" style="width:{{ $avg->percent }}%"></div>
                                </div>
                                <span class="avg-pct">{{ $avg->percent }}%</span>
                            </div>
                        @empty
                            <div class="empty-state" style="border:none;padding:0;text-align:left;">No quiz results yet.</div>
                        @endforelse
                    </div>
                </div>
            </aside>
            @endif

        </div>

    @elseif ($mode === 'quiz')

        {{-- ══════════════ QUIZ BUILDER MODE — "Add Quiz : <module>" ══════════════ --}}

        {{-- ✅ Back to the Managing Course screen --}}
        <a class="back-link" href="{{ route('faculty.courses.manage', $course->id ?? 1) }}">‹ Back to Modules</a>
        <h2 class="quiz-head">{{ ($quiz ?? null) ? 'Edit' : 'Add' }} Quiz : {{ $moduleTitle }}</h2>

        {{-- ✅ Real form — Save Quiz stores everything (session for now) and
             returns to the Managing Course screen with the quiz in the module --}}
        <form method="POST" action="{{ route('faculty.quiz.store', [$course->id, $moduleIdx]) }}">
            @csrf

            {{-- Quiz settings --}}
            <div class="quiz-card">
                <div class="grid3">
                    <div>
                        <label class="q-label">Quiz Title <span class="req">*</span></label>
                        <input class="input-outline" style="width:100%;" type="text" name="quiz_title" placeholder="MySQL Fundamentals Quiz" value="{{ $quiz->title ?? '' }}" required>
                    </div>
                    <div>
                        <label class="q-label">No. of Items <span class="req">*</span></label>
                        <input class="input-outline" style="width:100%;" type="number" name="items" placeholder="1" min="1" value="{{ $quiz->items ?? '' }}">
                    </div>
                    <div>
                        <label class="q-label">Passing Score (%)</label>
                        <input class="input-outline" style="width:100%;" type="number" name="passing_score" placeholder="100" min="0" max="100" value="{{ $quiz->passing_score ?? '' }}">
                    </div>
                </div>
                <div class="grid2">
                    <div>
                        <label class="q-label">Attempts allowed</label>
                        <input class="input-outline" style="width:100%;" type="text" name="attempts" placeholder="1 Attempt" value="{{ $quiz->attempts ?? '' }}">
                    </div>
                    <div>
                        <label class="q-label">Time Limit (min)</label>
                        <input class="input-outline" style="width:100%;" type="number" name="time_limit" placeholder="30" min="0" value="{{ $quiz->time_limit ?? '' }}">
                    </div>
                </div>
                <div>
                    <label class="q-label">Instructions ( Optional )</label>
                    <textarea class="textarea-outline" name="instructions" placeholder="Read each question carefully before answering. You may only submit once.">{{ $quiz->instructions ?? '' }}</textarea>
                </div>
            </div>

            {{-- Questions — for a NEW quiz the containers are generated after
                 clicking "Create Quiz"; the count follows "No. of Items" --}}
            @php
                $questions = $quiz->questions ?? [];
                $isNewQuiz = empty($questions);
            @endphp

            @if ($isNewQuiz)
            <div class="quiz-card" id="create-quiz-intro" style="text-align:center;">
                <p style="margin:0 0 16px;color:var(--muted);font-size:14px;">
                    Set the quiz details above — especially <strong>No. of Items</strong> — then click
                    <strong>Create Quiz</strong>. One question container will be created per item.
                </p>
                <button class="btn-save-lesson" type="button" onclick="createQuizQuestions()">Create Quiz</button>
            </div>
            @endif

            <div id="questions-area" style="display:{{ $isNewQuiz ? 'none' : 'block' }};">
            <div id="questions">
                @foreach ($questions as $q => $question)
                    <div class="quiz-card question-card">
                        <div class="q-head">
                            <h3>Question {{ $q + 1 }}</h3>
                            <span class="type-label">Type</span>
                            <div class="sel-wrap">
                                @php $qType = $question['type'] ?? 'Multiple Choice'; @endphp
                                <select name="questions[{{ $q }}][type]" onchange="questionTypeChanged(this)">
                                    <option value="Multiple Choice" {{ $qType === 'Multiple Choice' ? 'selected' : '' }}>Multiple Choice</option>
                                    <option value="True or False" {{ $qType === 'True or False' ? 'selected' : '' }}>True or False</option>
                                    <option value="Identification" {{ $qType === 'Identification' ? 'selected' : '' }}>Identification</option>
                                </select>
                            </div>
                            <input class="input-outline points" type="number" name="questions[{{ $q }}][points]" placeholder="Points" min="0" value="{{ $question['points'] ?? '' }}">
                            {{-- ✅ Removes THIS question card --}}
                            <button class="btn-x" type="button" title="Remove question" onclick="removeQuestion(this)">✕</button>
                        </div>

                        <div style="margin-bottom:18px;">
                            <input class="input-outline question-input" style="width:100%;" type="text" name="questions[{{ $q }}][text]" placeholder="What does SQL stand for?" value="{{ $question['text'] ?? '' }}">
                        </div>

                        @php
                            $savedChoices = $question['choices'] ?? [];
                            $choiceCount  = max(3, count($savedChoices));
                            $correct      = $question['correct'] ?? null;
                        @endphp

                        {{-- ✅ The section below changes with the question Type --}}

                        {{-- Multiple Choice: A/B/C choices + Add a Choice --}}
                        <div class="q-mc" style="display:{{ $qType === 'Multiple Choice' ? 'block' : 'none' }};">
                            <div class="choices" id="choices-{{ $q }}">
                                @for ($i = 0; $i < $choiceCount; $i++)
                                    @php $letter = chr(65 + $i); @endphp
                                    <div class="choice-row">
                                        <span class="choice-letter">{{ $letter }}.</span>
                                        <input class="input-outline" type="text" name="questions[{{ $q }}][choices][]" placeholder="Choice {{ $letter }}" value="{{ $qType === 'Multiple Choice' ? ($savedChoices[$i] ?? '') : '' }}">
                                        <label class="mark-correct">
                                            Mark as Correct
                                            <input type="radio" name="questions[{{ $q }}][correct]" value="{{ $i }}" {{ $qType === 'Multiple Choice' && $correct !== null && (int) $correct === $i ? 'checked' : '' }}>
                                            <span class="radio-dot"></span>
                                        </label>
                                    </div>
                                @endfor
                            </div>
                            {{-- ✅ Adds another choice row (D, E, F ...) --}}
                            <button class="btn-add-choice" type="button" onclick="addChoice({{ $q }})">+ Add a Choice</button>
                        </div>

                        {{-- True or False: fixed True / False rows --}}
                        <div class="q-tf" style="display:{{ $qType === 'True or False' ? 'block' : 'none' }};">
                            @foreach (['True', 'False'] as $ti => $tfLabel)
                                <div class="choice-row">
                                    <span class="tf-label">{{ $tfLabel }}</span>
                                    <label class="mark-correct">
                                        Mark as Correct
                                        <input type="radio" name="questions[{{ $q }}][tf_correct]" value="{{ $ti }}" {{ $qType === 'True or False' && $correct !== null && (int) $correct === $ti ? 'checked' : '' }}>
                                        <span class="radio-dot"></span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        {{-- Identification: a single correct-answer field --}}
                        <div class="q-id" style="display:{{ $qType === 'Identification' ? 'block' : 'none' }};">
                            <label class="q-label">Correct Answer</label>
                            <input class="input-outline" style="width:100%;max-width:420px;" type="text" name="questions[{{ $q }}][answer]" placeholder="Type the correct answer" value="{{ $question['answer'] ?? '' }}">
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ✅ Adds another fresh Question card --}}
            <button class="btn-add-choice" type="button" style="margin-bottom:26px;" onclick="addQuestion()">+ Add Question</button>
            </div>{{-- /questions-area --}}

            <div class="save-quiz-row" id="save-quiz-row" style="display:{{ $isNewQuiz ? 'none' : 'flex' }};">
                <button class="btn-save-lesson" type="submit">Save Quiz</button>
            </div>
        </form>

    @else

        {{-- ══════════════ LIST MODE — My Courses ══════════════ --}}

        <div class="page-head">
            <div>
                <h2>My Courses</h2>
                <p>{{ $courses->count() }} Total {{ $courses->count() == 1 ? 'course' : 'courses' }} created</p>
            </div>
            {{-- ✅ Connected — goes to Faculty › Create Courses only --}}
            <a href="{{ route('faculty.create') }}"><button class="btn-outline" type="button">Create Courses</button></a>
        </div>

        @forelse ($courses as $course)
            <div class="course-card">
                <div class="thumb" @if($course->thumbnail_url ?? null) style="background-image:url('{{ $course->thumbnail_url }}')" @endif></div>
                <div class="course-info">
                    <h3>{{ $course->title }}</h3>
                    @if($course->description ?? null)
                        <div class="desc">{{ $course->description }}</div>
                    @endif
                    <div class="course-meta">
                        <span class="status-chip {{ strtolower($course->status ?? 'draft') === 'published' ? 'published' : 'draft' }}">
                            {{ $course->status ?? 'Draft' }}
                        </span>
                        <span class="meta-item">
                            <span class="meta-num">{{ $course->students_count ?? 0 }}</span>
                            Students
                        </span>
                        <span class="meta-item">
                            <span class="meta-num">{{ $course->modules_count ?? 0 }}</span>
                            Modules
                        </span>
                        <span class="meta-item">
                            <span class="meta-num">{{ $course->lessons_count ?? 0 }}</span>
                            Lessons
                        </span>
                    </div>
                </div>
                <div class="card-actions">
                    {{-- ✅ Manage → Managing Course screen for THIS course (same blade, manage mode) --}}
                    <a href="{{ route('faculty.courses.manage', $course->id ?? 1) }}" class="btn-action">Manage</a>
                    {{-- ⚠ Not connected — no action yet --}}
                    <button class="btn-action" type="button">Analytics</button>
                </div>
            </div>
        @empty
            <div class="empty-state">
                You haven't created any courses yet.
            </div>
        @endforelse

    @endif

    </main>
</div>

<script>
    function toggleLessonForm(i, show) {
        var form  = document.getElementById('lesson-form-' + i);
        var empty = document.getElementById('lessons-empty-' + i);
        if (form)  form.style.display  = show ? 'flex' : 'none';
        if (empty) empty.style.display = show ? 'none' : 'block';
        if (show && form) {
            var first = form.querySelector('input[name="lesson_title"]');
            if (first) first.focus();
        }
    }

    // ── Add Lesson form: file name display ──
    function lessonFileChanged(input, idx) {
        var span = document.getElementById('lesson-file-name-' + idx);
        if (span) span.textContent = (input.files && input.files.length) ? input.files[0].name : 'No File Chosen';
    }

    function addChoice(q) {
        var box = document.getElementById('choices-' + q);
        if (!box) return;
        var i = box.children.length;
        if (i >= 26) return;
        var letter = String.fromCharCode(65 + i);   // D, E, F ...
        var row = document.createElement('div');
        row.className = 'choice-row';
        row.innerHTML =
            '<span class="choice-letter">' + letter + '.</span>' +
            '<input class="input-outline" type="text" name="questions[' + q + '][choices][]" placeholder="Choice ' + letter + '">' +
            '<label class="mark-correct">Mark as Correct' +
            '<input type="radio" name="questions[' + q + '][correct]" value="' + i + '">' +
            '<span class="radio-dot"></span></label>';
        box.appendChild(row);
    }

    // Next unused question index (indices never repeat, even after removals)
    var nextQuestionIndex = document.querySelectorAll('#questions .question-card').length;

    // ── "Create Quiz" — builds exactly N question containers, where N is the
    //    value of the "No. of Items" field (5 items → 5 question cards) ──
    function createQuizQuestions() {
        var itemsInput = document.querySelector('input[name="items"]');
        var n = parseInt(itemsInput && itemsInput.value, 10);
        if (!n || n < 1) {
            alert('Please set the "No. of Items" first (at least 1) before creating the quiz.');
            if (itemsInput) itemsInput.focus();
            return;
        }
        if (n > 100) n = 100;

        var wrap = document.getElementById('questions');
        if (wrap) wrap.innerHTML = '';
        nextQuestionIndex = 0;
        for (var i = 0; i < n; i++) addQuestion();

        var intro = document.getElementById('create-quiz-intro');
        if (intro) intro.style.display = 'none';
        var area = document.getElementById('questions-area');
        if (area) area.style.display = 'block';
        var saveRow = document.getElementById('save-quiz-row');
        if (saveRow) saveRow.style.display = 'flex';
    }

    // ✅ Swaps the question card's display to match the chosen Type
    function questionTypeChanged(sel) {
        var card = sel.closest('.question-card');
        if (!card) return;
        var t  = sel.value;
        var mc = card.querySelector('.q-mc');
        var tf = card.querySelector('.q-tf');
        var id = card.querySelector('.q-id');
        if (mc) mc.style.display = t === 'Multiple Choice' ? 'block' : 'none';
        if (tf) tf.style.display = t === 'True or False'   ? 'block' : 'none';
        if (id) id.style.display = t === 'Identification'  ? 'block' : 'none';
    }

    function addQuestion() {
        var wrap = document.getElementById('questions');
        if (!wrap) return;
        var q = nextQuestionIndex++;
        var card = document.createElement('div');
        card.className = 'quiz-card question-card';
        var tfHtml = '';
        ['True', 'False'].forEach(function (label, i) {
            tfHtml +=
                '<div class="choice-row">' +
                '<span class="tf-label">' + label + '</span>' +
                '<label class="mark-correct">Mark as Correct' +
                '<input type="radio" name="questions[' + q + '][tf_correct]" value="' + i + '">' +
                '<span class="radio-dot"></span></label>' +
                '</div>';
        });
        var choicesHtml = '';
        ['A', 'B', 'C'].forEach(function (letter, i) {
            choicesHtml +=
                '<div class="choice-row">' +
                '<span class="choice-letter">' + letter + '.</span>' +
                '<input class="input-outline" type="text" name="questions[' + q + '][choices][]" placeholder="Choice ' + letter + '">' +
                '<label class="mark-correct">Mark as Correct' +
                '<input type="radio" name="questions[' + q + '][correct]" value="' + i + '">' +
                '<span class="radio-dot"></span></label>' +
                '</div>';
        });
        card.innerHTML =
            '<div class="q-head">' +
                '<h3>Question ?</h3>' +
                '<span class="type-label">Type</span>' +
                '<div class="sel-wrap">' +
                    '<select name="questions[' + q + '][type]" onchange="questionTypeChanged(this)">' +
                        '<option value="Multiple Choice">Multiple Choice</option>' +
                        '<option value="True or False">True or False</option>' +
                        '<option value="Identification">Identification</option>' +
                    '</select>' +
                '</div>' +
                '<input class="input-outline points" type="number" name="questions[' + q + '][points]" placeholder="Points" min="0">' +
                '<button class="btn-x" type="button" title="Remove question" onclick="removeQuestion(this)">\u2715</button>' +
            '</div>' +
            '<div style="margin-bottom:18px;">' +
                '<input class="input-outline question-input" style="width:100%;" type="text" name="questions[' + q + '][text]" placeholder="Enter your question">' +
            '</div>' +
            '<div class="q-mc">' +
                '<div class="choices" id="choices-' + q + '">' + choicesHtml + '</div>' +
                '<button class="btn-add-choice" type="button" onclick="addChoice(' + q + ')">+ Add a Choice</button>' +
            '</div>' +
            '<div class="q-tf" style="display:none;">' + tfHtml + '</div>' +
            '<div class="q-id" style="display:none;">' +
                '<label class="q-label">Correct Answer</label>' +
                '<input class="input-outline" style="width:100%;max-width:420px;" type="text" name="questions[' + q + '][answer]" placeholder="Type the correct answer">' +
            '</div>';
        wrap.appendChild(card);
        renumberQuestions();
        var first = card.querySelector('input[type="text"]');
        if (first) first.focus();
    }

    function removeQuestion(btn) {
        var card = btn.closest('.question-card');
        if (card && confirm('Remove this question?')) {
            card.remove();
            renumberQuestions();
        }
    }

    function renumberQuestions() {
        document.querySelectorAll('#questions .question-card .q-head h3').forEach(function (h, i) {
            h.textContent = 'Question ' + (i + 1);
        });
    }
</script>

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