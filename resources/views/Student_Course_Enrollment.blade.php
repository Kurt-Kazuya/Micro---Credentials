{{-- resources/views/Student_Course_Enrollment.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $course->title ?? 'Course' }} | Upskill</title>
<style>
    :root{
        --navy:#13176b;--navy-deep:#0c0f4d;--gold:#dba617;
        --cyan:#7fe9e3;--thumb:#d8e3f8;--ink:#13176b;
        --muted:#6b7280;--line:#e5e7eb;
        --shadow:0 10px 25px rgba(19,23,107,0.08);
        --green:#22c55e;--red:#ef4444;
    }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI",Roboto,sans-serif;color:var(--ink);margin:0;background:#f4f5f9;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}

    /* ── Topbar ─────────────────────────────── */
    .topbar{background:var(--navy);display:flex;align-items:center;justify-content:space-between;padding:14px 28px;gap:20px;position:sticky;top:0;z-index:100;}
    .brand{display:flex;align-items:center;gap:14px;color:#fff;white-space:nowrap;}
    .brand .logo{width:46px;height:46px;border-radius:50%;background:#fff;flex-shrink:0;}
    .brand .logo img{width:100%;height:100%;object-fit:contain;border-radius:50%;padding:3px;}
    .brand h1{font-size:24px;letter-spacing:1px;margin:0;font-weight:800;}
    .nav-pills{display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;align-items:center;}
    .nav-pills a{background:transparent;color:#fff;font-weight:700;padding:10px 18px;border-radius:10px;font-size:15px;transition:color .15s ease, background-color .15s ease;}
    .nav-pills a:hover{color:var(--gold);}
    .nav-pills a.is-active{color:var(--gold);background:rgba(255,255,255,0.08);}
    .search-box{display:flex;align-items:center;gap:10px;background:#fff;border-radius:999px;padding:10px 18px;min-width:220px;color:var(--muted);}
    .search-box input{border:none;outline:none;font-size:15px;width:100%;color:var(--ink);background:transparent;}
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}

    /* ── Layout ─────────────────────────────── */
    .layout{display:grid;grid-template-columns:294px 1fr;height:calc(100vh - 74px);}

    /* ── Left: Course Nav ───────────────────── */
    .course-nav{background:#fff;display:flex;flex-direction:column;overflow:hidden;margin:24px 10px 24px 24px;border-radius:22px;border:1px solid var(--line);}
    .course-nav-header{background:var(--navy);color:#fff;padding:20px 18px 18px;flex-shrink:0;}
    .nav-category{font-size:12px;font-weight:700;color:var(--cyan);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;}
    .nav-title{font-size:15px;font-weight:800;line-height:1.3;margin-bottom:16px;}
    .nav-progress-row{display:flex;justify-content:space-between;font-size:13px;font-weight:600;margin-bottom:6px;}
    .nav-progress-track{width:100%;height:6px;background:rgba(255,255,255,.25);border-radius:999px;overflow:hidden;}
    .nav-progress-fill{height:100%;background:var(--cyan);border-radius:999px;transition:width .5s ease;}

    .modules-scroll{flex:1;overflow-y:auto;}
    .modules-scroll::-webkit-scrollbar{width:4px;}
    .modules-scroll::-webkit-scrollbar-thumb{background:#c9cce0;border-radius:4px;}

    .module-group{border-bottom:1px solid var(--line);}
    .module-toggle{width:100%;background:#f1f2f8;border:none;padding:0;display:flex;align-items:stretch;text-align:left;}
    .module-toggle:hover{background:#e8e9f4;}
    .module-title-area{flex:1;padding:14px 16px;display:flex;flex-direction:column;gap:2px;cursor:pointer;}
    .module-num{font-size:13px;font-weight:800;color:var(--navy);}
    .module-sub{font-size:12px;font-weight:600;color:var(--muted);}
    .module-chevron-btn{background:transparent;border:none;border-left:1px solid var(--line);padding:0 14px;display:flex;align-items:center;cursor:pointer;}
    .chevron{width:18px;height:18px;color:var(--navy);transition:transform .2s;}
    .chevron.open{transform:rotate(180deg);}

    /* Module states */
    .module-title-area.active{background:var(--navy);border-left:4px solid var(--cyan);}
    .module-title-area.active .module-num,.module-title-area.active .module-sub{color:#fff;}
    .module-title-area.completed{border-left:4px solid var(--green);}
    .module-title-area.completed .module-num{color:#166534;}

    .lesson-list{display:none;}
    .lesson-list.open{display:block;}

    /* Lesson items */
    .lesson-item{display:flex;align-items:center;padding:12px 16px 12px 20px;border-bottom:1px solid #f0f0f5;cursor:pointer;transition:background .15s;gap:8px;}
    .lesson-item:hover{background:#f0f1f8;}
    .lesson-item.active{background:#e8e9f4;border-left:3px solid var(--navy);}
    .lesson-item.lesson-correct{border-left:4px solid var(--green)!important;background:#f0fdf4;}
    .lesson-item.lesson-wrong{border-left:4px solid var(--red)!important;background:#fef2f2;}
    .lesson-title-text{font-size:13px;font-weight:600;color:var(--navy);line-height:1.3;flex:1;}
    .lesson-meta{font-size:11px;color:var(--muted);white-space:nowrap;flex-shrink:0;}
    .status-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;display:none;}
    .lesson-correct .status-dot{display:inline-block;background:var(--green);box-shadow:0 0 0 3px rgba(34,197,94,.2);}
    .lesson-wrong   .status-dot{display:inline-block;background:var(--red);box-shadow:0 0 0 3px rgba(239,68,68,.2);}

    /* Quiz row */
    .quiz-row{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:#fef9e7;gap:10px;}
    .quiz-info-title{font-size:13px;font-weight:800;color:var(--navy);margin-bottom:2px;}
    .quiz-info-sub{font-size:11px;color:var(--muted);}
    /* LOCKED quiz button */
    .btn-quiz-sm{font-weight:800;font-size:12px;padding:7px 16px;border-radius:6px;flex-shrink:0;transition:all .2s;}
    .btn-quiz-sm.locked{background:#e5e7eb;color:#9ca3af;border:1.5px solid #e5e7eb;cursor:not-allowed;}
    .btn-quiz-sm.unlocked{background:#fff;color:var(--navy);border:1.5px solid var(--navy);cursor:pointer;}
    .btn-quiz-sm.unlocked:hover{background:var(--navy);color:#fff;}
    .btn-quiz-sm.viewed{background:#e8e9f4;color:var(--navy);border:1.5px solid var(--navy);cursor:pointer;}
    .btn-quiz-sm.viewed:hover{background:#d0d2ea;}

    /* ── Right: Content Area ────────────────── */
    .lesson-content{display:flex;flex-direction:column;overflow-y:auto;padding:28px 36px 40px;gap:20px;background:#f4f5f9;}
    .lesson-content.welcome-mode{padding:24px;gap:0;background:#f4f5f9;}
    .lesson-header-btn{display:flex;align-items:center;justify-content:space-between;width:100%;background:var(--navy);color:#fff;font-weight:800;font-size:18px;padding:18px 28px;border:none;border-radius:14px;text-align:left;}

    /* ── VIEW 1: Welcome ────────────────────── */
    #view-welcome{display:flex;flex-direction:column;gap:0;background:#fff;border-radius:20px;overflow:hidden;align-self:stretch;margin:0;box-shadow:0 4px 20px rgba(19,23,107,0.1);}
    /* Hero banner */
    .welcome-hero{background:linear-gradient(135deg,var(--navy) 0%,#1e2480 60%,#2a3199 100%);padding:48px 48px 52px;position:relative;overflow:hidden;flex-shrink:0;}
    .welcome-hero::before{content:'';position:absolute;top:-40px;right:-40px;width:220px;height:220px;border-radius:50%;background:rgba(127,233,227,.08);}
    .welcome-hero::after{content:'';position:absolute;bottom:-60px;right:60px;width:140px;height:140px;border-radius:50%;background:rgba(127,233,227,.06);}
    .welcome-hero-badge{display:inline-flex;align-items:center;background:rgba(127,233,227,.15);border:1px solid rgba(127,233,227,.35);border-radius:999px;padding:5px 14px;font-size:12px;font-weight:700;color:var(--cyan);letter-spacing:.04em;text-transform:uppercase;margin-bottom:18px;}
    .welcome-hero-title{font-size:30px;font-weight:900;color:#fff;margin:0 0 10px;line-height:1.2;position:relative;z-index:1;}
    .welcome-hero-sub{font-size:14px;color:rgba(255,255,255,.65);margin:0;line-height:1.5;position:relative;z-index:1;}
    /* Stats strip — no icons */
    .welcome-stats-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:0;background:#fff;border-bottom:1px solid var(--line);flex-shrink:0;}
    .wss-item{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;padding:22px 16px;border-right:1px solid var(--line);}
    .wss-item:last-child{border-right:none;}
    .wss-num{font-size:26px;font-weight:900;color:var(--navy);line-height:1;}
    .wss-lbl{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;}
    /* Info card */
    .welcome-info-card{margin:24px 24px 0;background:#fff;border-radius:16px;box-shadow:var(--shadow);padding:22px 24px;border-left:4px solid var(--cyan);}
    .welcome-info-card h4{margin:0 0 8px;color:var(--navy);font-size:15px;font-weight:800;}
    .welcome-info-card p{margin:0;color:var(--muted);font-size:13px;line-height:1.6;}
    /* Continue */
    .welcome-continue-wrap{display:flex;justify-content:center;padding:24px;}
    .btn-welcome-continue{display:inline-flex;align-items:center;padding:15px 52px;background:var(--navy);color:#fff;font-weight:800;font-size:16px;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 14px rgba(19,23,107,.3);transition:all .2s;}
    .btn-welcome-continue:hover{background:var(--navy-deep);}

    /* ── VIEW 2: Lesson content ─────────────── */
    #view-lesson{display:none;flex-direction:column;gap:20px;}
    .lesson-card{background:#fff;border-radius:20px;box-shadow:var(--shadow);overflow:hidden;}
    .lesson-card-topbar{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:1px solid var(--line);}
    .lesson-card-label{font-size:16px;font-weight:800;color:var(--navy);}
    .btn-mark-complete{background:var(--navy);color:#fff;font-weight:700;font-size:14px;padding:10px 22px;border:none;border-radius:8px;transition:background .2s;}
    .btn-mark-complete:hover{background:var(--navy-deep);}
    .btn-mark-complete.done{background:var(--green);}
    /* Video player */
    .video-area{background:#1a1a3e;height:260px;display:flex;align-items:center;justify-content:center;cursor:pointer;}
    /* When a real video is loaded, the frame follows the video's own size */
    .video-area.has-video{height:auto;background:#000;padding:0;}
    .video-area.has-video #lesson-video{width:100%;height:auto;max-height:70vh;display:block;}
    /* Image / PDF / generic-file previews replace the placeholder */
    .video-area.has-image{height:auto;background:#f6f7fc;padding:0;cursor:default;}
    .video-area.has-image img{width:100%;height:auto;max-height:70vh;display:block;object-fit:contain;}
    .video-area.has-pdf{height:70vh;min-height:400px;background:#525659;padding:0;cursor:default;}
    .video-area.has-pdf iframe{width:100%;height:100%;border:0;display:block;}
    .video-area.has-file{background:#f6f7fc;cursor:default;}
    /* Uploaded file attachment card */
    .file-attach{display:flex;align-items:center;gap:10px;padding:10px 12px;margin:14px auto 0;
        max-width:560px;background:#f6f7fc;border:1px solid #e3e6f0;border-radius:10px;}
    .file-attach-icon{width:34px;height:34px;flex:0 0 34px;border-radius:8px;background:var(--navy,#13176b);
        color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;letter-spacing:.5px;}
    .file-attach-info{flex:1;min-width:0;}
    .file-attach-name{font-weight:800;color:var(--navy,#13176b);font-size:13px;
        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .file-attach-sub{font-size:11px;color:#8a8fa8;margin-top:1px;}
    .file-attach-actions{display:flex;gap:6px;flex:0 0 auto;}
    .file-attach-btn{padding:6px 12px;border-radius:8px;font-size:11px;font-weight:800;
        background:var(--navy,#13176b);color:#fff;text-decoration:none;transition:background .2s;}
    .file-attach-btn:hover{background:#0c0f4d;}
    .file-attach-btn.ghost{background:#fff;color:var(--navy,#13176b);border:1px solid #d8dcee;}
    .file-attach-btn.ghost:hover{background:#eef0f9;}
    .play-btn{width:64px;height:64px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;transition:background .2s;}
    .play-btn:hover{background:rgba(255,255,255,.35);}
    .play-btn svg{width:30px;height:30px;fill:#fff;margin-left:5px;}
    /* Text reading area */
    .text-area{display:none;background:var(--thumb);height:260px;flex-direction:column;align-items:center;justify-content:center;gap:14px;padding:24px;}
    .text-area svg{width:54px;height:54px;color:#7a8ec4;opacity:.7;}
    .text-area p{font-size:14px;color:var(--navy);font-weight:600;text-align:center;margin:0;opacity:.85;}
    #view-lesson.text-mode .video-area{display:none;}
    #view-lesson.text-mode .text-area{display:flex;}
    /* Lesson body */
    .lesson-body{padding:22px 26px 26px;}
    .lesson-body-title{font-size:20px;font-weight:800;color:var(--navy);margin:0 0 10px;}
    .lesson-body-desc{font-size:14px;color:var(--muted);margin:0 0 8px;line-height:1.6;}
    .lesson-body-desc strong{color:var(--navy);}
    .lesson-body-duration{font-size:13px;color:var(--muted);margin:0;}
    /* Continue = next lesson only */
    .btn-lesson-continue{display:flex;align-items:center;justify-content:center;width:100%;background:var(--navy);color:#fff;font-weight:800;font-size:18px;padding:18px 28px;border:none;border-radius:14px;cursor:pointer;}
    .btn-lesson-continue:hover{background:var(--navy-deep);}

    /* ── VIEW 3: Quiz (3 questions) ─────────── */
    #view-quiz{display:none;flex-direction:column;gap:20px;}
    .quiz-card{background:#fff;border-radius:20px;box-shadow:var(--shadow);padding:26px 28px 30px;display:flex;flex-direction:column;gap:24px;}
    .quiz-question-block{}
    .quiz-q-label{font-size:15px;font-weight:800;color:var(--navy);margin:0 0 10px;}
    .quiz-q-box{background:#f1f2f8;border-radius:10px;padding:13px 18px;font-size:14px;font-weight:600;color:var(--navy);margin-bottom:12px;}
    .quiz-options{display:flex;flex-direction:column;gap:10px;}
    .quiz-opt{display:flex;align-items:center;gap:12px;background:#f8f9fb;border:1.5px solid var(--line);border-radius:10px;padding:12px 16px;cursor:pointer;transition:all .15s;}
    .quiz-opt:hover{border-color:var(--navy);background:#eef0fa;}
    .quiz-opt.selected{border-color:var(--navy);background:#e8e9f4;}
    .quiz-opt.correct-ans{border-color:var(--green);background:#f0fdf4;}
    .quiz-opt.wrong-ans{border-color:var(--red);background:#fef2f2;}
    .quiz-opt-letter{width:28px;height:28px;border-radius:50%;background:var(--navy);color:#fff;font-weight:800;font-size:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .quiz-opt-text{font-size:14px;font-weight:600;color:var(--navy);}
    .quiz-divider{border:none;border-top:1px solid var(--line);margin:0;}
    .quiz-score-result{display:none;padding:14px 18px;border-radius:10px;font-weight:700;font-size:15px;text-align:center;}
    .quiz-score-result.pass{background:#dcfce7;color:#166534;border:1px solid #bbf7d0;}
    .quiz-score-result.fail{background:#fee2e2;color:#991b1b;border:1px solid #fecaca;}
    .btn-quiz-submit{display:flex;align-items:center;justify-content:center;width:100%;background:var(--navy);color:#fff;font-weight:800;font-size:18px;padding:18px 28px;border:none;border-radius:14px;cursor:pointer;}
    .btn-quiz-submit:hover{background:var(--navy-deep);}
    /* Next-module button after quiz */
    .btn-next-module{display:none;align-items:center;justify-content:center;width:100%;background:var(--green);color:#fff;font-weight:800;font-size:17px;padding:18px 28px;border:none;border-radius:14px;cursor:pointer;}
    .btn-next-module:hover{background:#16a34a;}

    /* ── Locked module styles ──────────────────── */
    .module-locked .module-title-area{opacity:.5;cursor:not-allowed;pointer-events:none;}
    .module-locked .module-chevron-btn{opacity:.5;cursor:not-allowed;pointer-events:none;}
    .module-locked .lesson-item{pointer-events:none;opacity:.4;cursor:not-allowed;}
    .module-locked .btn-quiz-sm{pointer-events:none;opacity:.4;}
    .module-lock-badge{font-size:11px;color:var(--muted);font-weight:700;
        display:flex;align-items:center;gap:4px;margin-top:3px;}
    /* Retake quiz button */
    .btn-retake{display:none;width:100%;padding:12px 20px;border:2px solid var(--navy);
        background:transparent;color:var(--navy);font-weight:800;font-size:15px;
        border-radius:14px;cursor:pointer;margin-top:0;}
    .btn-retake:hover{background:var(--navy);color:#fff;}
    /* Retake button while in 24-hour cooldown */
    .btn-retake.cooldown{border-color:#d1d5db;background:#f3f4f6;color:#9ca3af;cursor:not-allowed;}
    .btn-retake.cooldown:hover{background:#f3f4f6;color:#9ca3af;}
    .btn-retake .cooldown-timer{display:block;font-size:12px;font-weight:700;margin-top:3px;font-variant-numeric:tabular-nums;}

    @media(max-width:980px){
        .layout{grid-template-columns:1fr;height:auto;}
        .course-nav{max-height:340px;margin:14px;border-radius:16px;}
        .lesson-content{padding:20px 18px 40px;}
    }
</style>
</head>
<body>

<header class="topbar">
    <div class="brand">
        <span class="logo"><img src="{{ asset('images/PSU-Logo.png') }}" alt="PSU Logo"></span>
        <h1>UPSKILL</h1>
    </div>
    <nav class="nav-pills">
        <a href="{{ route('courses.browse') }}" class="{{ request()->routeIs('courses.*') ? 'is-active' : '' }}">Courses</a>
        <a href="{{ route('dashboard') }}"      class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
    </nav>
    <form action="{{ route('search') }}" method="GET" class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input type="text" name="q" placeholder="Search" value="{{ request('q') }}">
    </form>
    <div class="icon-cluster">
        <a href="{{ route('notifications.index') }}" class="icon-circle">
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

    {{-- ── LEFT: Course Navigation ─── --}}
    <aside class="course-nav">
        <div class="course-nav-header">
            @if($course->category ?? false)<div class="nav-category">{{ $course->category }}</div>@endif
            <div class="nav-title">{{ $course->title ?? 'Course' }}</div>
            <div class="nav-progress-row">
                <span>Your Progress</span>
                <span id="nav-pct">0%</span>
            </div>
            <div class="nav-progress-track">
                <div class="nav-progress-fill" id="nav-fill" style="width:0%"></div>
            </div>
        </div>

        <div class="modules-scroll">
            @foreach($modules as $mIndex => $module)
            <div class="module-group {{ $mIndex > 0 ? 'module-locked' : '' }}" id="mg-{{ $mIndex }}">

                <div class="module-toggle">
                    <div class="module-title-area" id="mta-{{ $mIndex }}"
                         onclick="showModuleWelcome('{{ addslashes($module->title) }}',{{ $module->lessons->count() }},{{ $modules->count() }},{{ $badge_count ?? 1 }},{{ $mIndex }})">
                        <span class="module-num">Module {{ $mIndex + 1 }}: {{ $module->title }}</span>
                        <span class="module-sub" id="msub-{{ $mIndex }}">{{ $module->lessons->count() }} lessons</span>
                        @if($mIndex > 0)
                        <span class="module-lock-badge" id="mlock-{{ $mIndex }}">
                            Complete previous quiz to unlock
                        </span>
                        @endif
                    </div>
                    <button class="module-chevron-btn" type="button"
                            onclick="toggleModule('mod-{{ $mIndex }}','chev-{{ $mIndex }}')">
                        <svg class="chevron {{ $mIndex===0?'open':'' }}" id="chev-{{ $mIndex }}"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                </div>

                <div class="lesson-list {{ $mIndex===0?'open':'' }}" id="mod-{{ $mIndex }}">
                    @foreach($module->lessons ?? [] as $lIndex => $lesson)
                        @php $isVid = strtolower($lesson->type??'')  === 'video'; @endphp
                        <div class="lesson-item"
                             id="li-{{ $mIndex }}-{{ $lIndex }}"
                             data-module="{{ $mIndex }}"
                             data-lid="{{ $mIndex }}-{{ $lIndex }}"
                             onclick="loadLesson(
                                 '{{ $lesson->type ?? 'Text' }}',
                                 '{{ addslashes($lesson->title) }}',
                                 '{{ addslashes($lesson->description ?? $lesson->title) }}',
                                 '{{ $lesson->duration ?? '15m' }}',
                                 {{ $mIndex }}, {{ $lIndex }},
                                 {{ $lesson->file_url ? "'" . $lesson->file_url . "'" : 'null' }}
                             )">
                            <span class="lesson-title-text">{{ $lesson->title }}</span>
                            <span class="lesson-meta">{{ $lesson->type }} · {{ $lesson->duration }}</span>
                            <span class="status-dot"></span>
                        </div>
                    @endforeach

                    @if($module->quiz ?? false)
                    <div class="quiz-row">
                        <div>
                            <div class="quiz-info-title">{{ $module->quiz->title }}</div>
                            <div class="quiz-info-sub">{{ $module->quiz->questions_count ?? 3 }} questions · Pass {{ $module->quiz->passing_score ?? 75 }}%</div>
                        </div>
                        {{-- Starts LOCKED; JS unlocks after all lessons marked complete --}}
                        <button class="btn-quiz-sm locked" id="qbtn-{{ $mIndex }}"
                                data-module="{{ $mIndex }}"
                                onclick="handleQuizClick({{ $mIndex }})"
                                title="Complete all lessons first">QUIZ</button>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </aside>

    {{-- ── RIGHT: Content ─── --}}
    <main class="lesson-content">

        {{-- VIEW 1: Welcome --}}
        <div id="view-welcome">

            {{-- Hero banner --}}
            <div class="welcome-hero">
                <div class="welcome-hero-badge">Course Module</div>
                <h1 class="welcome-hero-title" id="wh-mod-title">{{ $modules->first()->title ?? 'the Course' }}</h1>
                <p class="welcome-hero-sub">Complete all lessons and pass the quiz to unlock the next module.</p>
            </div>

            {{-- Stats strip (no icons) --}}
            <div class="welcome-stats-strip">
                <div class="wss-item">
                    <span class="wss-num" id="ws-mod">{{ $modules->count() }}</span>
                    <span class="wss-lbl">Modules</span>
                </div>
                <div class="wss-item">
                    <span class="wss-num" id="ws-les">{{ $total_lessons ?? 0 }}</span>
                    <span class="wss-lbl">Lessons</span>
                </div>
                <div class="wss-item">
                    <span class="wss-num" id="ws-bdg">{{ $badge_count ?? 1 }}</span>
                    <span class="wss-lbl">Badge</span>
                </div>
            </div>

            {{-- Info card --}}
            <div class="welcome-info-card">
                <h4>How it works</h4>
                <p>Work through each lesson at your own pace, mark them complete, then take the module quiz. Pass the quiz to unlock the next module and earn your badge.</p>
            </div>

            {{-- Continue button --}}
            <div class="welcome-continue-wrap">
                <button class="btn-welcome-continue" onclick="continueFromWelcome()" type="button">
                    Start Learning
                </button>
            </div>

        </div>

        {{-- VIEW 2: Lesson Content --}}
        <div id="view-lesson">
            <div class="lesson-header-btn">
                <span id="lh-title">Introduction to Laravel</span>
                
            </div>
            <div class="lesson-card">
                <div class="lesson-card-topbar">
                    <span class="lesson-card-label" id="lc-label">Introduction to Laravel</span>
                    <button class="btn-mark-complete" id="btn-mark" onclick="markComplete()" type="button">Mark Complete</button>
                </div>
                {{-- Video --}}
                <div class="video-area" onclick="playVideo()">
                    <video id="lesson-video" controls preload="metadata"
                           style="display:none;width:100%;height:auto;max-height:70vh;background:#000;border-radius:inherit;"
                           onclick="event.stopPropagation()"></video>
                    <img id="lesson-image" alt="" style="display:none;">
                    <iframe id="lesson-pdf" title="Lesson document" style="display:none;"></iframe>
                    <div class="play-btn" id="play-btn">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><polygon points="5,3 19,12 5,21"/></svg>
                    </div>
                </div>
                <div class="file-attach" id="lesson-file-card" style="display:none;">
                    <div class="file-attach-icon" id="lesson-file-ext">FILE</div>
                    <div class="file-attach-info">
                        <div class="file-attach-name" id="lesson-file-name">Attachment</div>
                        <div class="file-attach-sub" id="lesson-file-sub">Attached file</div>
                    </div>
                    <div class="file-attach-actions">
                        <a class="file-attach-btn" id="lesson-file-open" href="#" target="_blank" rel="noopener">Open</a>
                        <a class="file-attach-btn ghost" id="lesson-file-dl" href="#" download>Download</a>
                    </div>
                </div>
                {{-- Text --}}
                <div class="text-area" id="text-area">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    <p id="text-snippet">Read the lesson content below</p>
                </div>
                <div class="lesson-body">
                    <h3 class="lesson-body-title" id="lb-title">What is Introduction to Laravel?</h3>
                    <p class="lesson-body-desc"  id="lb-desc"><strong>Introduction to Laravel</strong> &mdash; lesson content</p>
                    <p class="lesson-body-duration" id="lb-dur">Duration: 15m</p>
                </div>
            </div>
            {{-- Continue = next lesson only (no quiz) --}}
            <button class="btn-lesson-continue" onclick="nextLesson()" type="button">Continue</button>
        </div>

        {{-- VIEW 3: Quiz (3 questions) --}}
        <div id="view-quiz">
            <div class="lesson-header-btn">
                <span id="qh-title">Quiz</span>
                
            </div>
            <div class="quiz-card" id="quiz-questions-container">
                {{-- Populated by JS --}}
            </div>
            <div class="quiz-score-result" id="quiz-score-result"></div>
            <button class="btn-quiz-submit" id="btn-quiz-submit" onclick="submitQuiz()" type="button">Submit</button>
            <button class="btn-next-module" id="btn-next-module" onclick="goToNextModule()" type="button">
                Continue to Next Module &#x2192;
            </button>
            <button class="btn-retake" id="btn-retake" onclick="retakeQuiz()" type="button">
                Retake Quiz
            </button>
        </div>

    </main>
</div>

<script>
/* ═══════════════════════════════════════════════════════
   QUIZ DATA — one per module, 3 questions each
═══════════════════════════════════════════════════════ */
/* Quizzes come straight from the database — exactly what the faculty
   built on the manage screen. Modules without a quiz get an empty set. */
var QUIZ_DATA = {!! $modules->mapWithKeys(fn ($m, $i) => [
    $i => [
        'title'        => $m->quiz->title ?? '',
        'passingScore' => (int) ($m->quiz->passing_score ?? 75),
        'questions'    => $m->quiz->questions ?? [],
    ],
])->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};

/* ═══════════════════════════════════════════════════════
   STATE
═══════════════════════════════════════════════════════ */
var _curLessonEl   = null;
var _curModIdx     = 0;
var _curLesIdx     = 0;
var _totalQuizQ    = 0;          // total quiz questions across all modules
var _moduleScores  = {};         // { modIdx: correctCount } — replaced on retake
var _moduleAnswers = {};         // { modIdx: { qi: letter } } — saved on submit for view-only replay
var _retakeQMap    = [];         // maps rendered question index → original question index on retake

// Count total quiz questions on load
document.addEventListener('DOMContentLoaded', function () {
    Object.values(QUIZ_DATA).forEach(function(m){ _totalQuizQ += m.questions.length; });
    restoreProgress(_savedProgress);
});

/* ── Helpers ──────────────────────────────────────── */
function hideAllViews() {
    document.getElementById('view-welcome').style.display = 'none';
    document.getElementById('view-lesson').style.display  = 'none';
    document.getElementById('view-quiz').style.display    = 'none';
    document.querySelector('.lesson-content').classList.remove('welcome-mode');
}
function toggleModule(listId, chevId) {
    const l = document.getElementById(listId), c = document.getElementById(chevId);
    const o = l.classList.toggle('open'); c.classList.toggle('open', o);
}

/* ── Real-time progress persistence ─────────────────────────────
   Every lesson marked complete and every quiz submit is saved to the
   server, and the saved state is restored on load — so the student's
   percentage is the same on every page, every visit. */
var _savedProgress = {!! json_encode($saved_progress ?? ['completed_lessons' => [], 'module_scores' => new \stdClass()]) !!};
var _serverUnlocks = {!! json_encode($quiz_unlocks ?? new \stdClass()) !!};   // moduleIdx → retake unlock timestamp (ms), from the server
var _pendingQuizSubmit = null;   // last quiz result awaiting server confirmation
var _saveTimer = null;

function saveProgress() {
    if (_saveTimer) clearTimeout(_saveTimer);
    _saveTimer = setTimeout(function () {
        var completed = Array.from(document.querySelectorAll('.lesson-item.lesson-correct'))
            .map(function (el) { return el.dataset.lid; });
        var totalCorrect = Object.values(_moduleScores).reduce(function (s, v) { return s + v; }, 0);
        var pct = _totalQuizQ > 0 ? Math.min(100, Math.round((totalCorrect / _totalQuizQ) * 100)) : 0;

        var payload = {
            percent: pct,
            completed_lessons: completed,
            module_scores: _moduleScores
        };
        if (_pendingQuizSubmit) {
            payload.quiz_results = {};
            payload.quiz_results[_pendingQuizSubmit.modIdx] = { score: _pendingQuizSubmit.correct };
        }

        fetch('{{ route('courses.progress', $course->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (!data) return;
            _pendingQuizSubmit = null;
            // Server-authoritative cooldowns (survive reloads / other devices)
            if (data.quiz_unlocks) {
                Object.keys(data.quiz_unlocks).forEach(function (k) {
                    _serverUnlocks[k] = data.quiz_unlocks[k];
                    try { localStorage.setItem(retakeKey(k), String(data.quiz_unlocks[k])); } catch (e) {}
                    if (String(k) === String(_curModIdx)
                        && document.getElementById('view-quiz').style.display !== 'none') {
                        showRetakeButton(_curModIdx);
                    }
                });
            }
        })
        .catch(function () { /* offline — next change will retry */ });
    }, 600);
}

function restoreProgress(saved) {
    if (!saved) return;

    (saved.completed_lessons || []).forEach(function (lid) {
        var el = document.querySelector('.lesson-item[data-lid="' + lid + '"]');
        if (el) el.classList.add('lesson-correct');
    });

    _moduleScores = saved.module_scores || {};

    // Quiz buttons + module locks follow the saved quiz results
    Object.keys(QUIZ_DATA).forEach(function (k) {
        var i     = parseInt(k, 10);
        var data  = QUIZ_DATA[i];
        var score = _moduleScores[i];
        if (score === undefined) return;
        var pct = Math.round((score / data.questions.length) * 100);
        var doneBtn = document.getElementById('qbtn-' + i);
        if (pct >= (data.passingScore || 75)) {
            if (doneBtn) {
                doneBtn.classList.remove('unlocked', 'locked');
                doneBtn.classList.add('viewed');
                doneBtn.textContent = 'VIEW';
                doneBtn.title = 'View your quiz results';
            }
            unlockModule(i + 1);
        } else if (doneBtn) {
            // Failed attempt — read-only review; retake is gated by the cooldown
            doneBtn.classList.remove('locked');
            doneBtn.classList.add('viewed');
            doneBtn.textContent = 'VIEW';
            doneBtn.title = 'View your quiz results';
        }
    });

    // Lesson-driven states last, so "Completed" labels win
    Object.keys(QUIZ_DATA).forEach(function (k) {
        var i = parseInt(k, 10);
        checkQuizUnlock(i);
        checkModuleComplete(i);
    });

    // Apply server-side retake cooldowns (persist across page changes)
    Object.keys(_serverUnlocks || {}).forEach(function (k) {
        if (retakeMsRemaining(k) > 0) {
            try { localStorage.setItem(retakeKey(k), String(_serverUnlocks[k])); } catch (e) {}
        }
    });

    updateProgress();
}

/* ── Report course completion to the server (awards the badge) ──
   Fires once, the moment the course reaches 100%. The Admin
   "Recent Badges" panel picks the new badge up within 15 seconds. */
var _completionReported = false;
function reportCourseCompletion() {
    if (_completionReported) return;
    _completionReported = true;
    fetch('{{ route('courses.complete', $course->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: '{}'
    }).then(function(r){ return r.json(); })
      .then(function(data){
          if (data && data.badge_awarded) {
              try { console.log('Badge earned: ' + data.badge_awarded); } catch (e) {}
          }
      })
      .catch(function(){ _completionReported = false; });
}

/* ── Progress bar — based on quiz correct answers ONLY ── */
function updateProgress() {
    /* Sum correct answers per module (retakes REPLACE, not add).
       Only correct answers count — wrong answers contribute 0. */
    const totalCorrect = Object.values(_moduleScores).reduce(function(sum, v){ return sum + v; }, 0);
    const raw = _totalQuizQ > 0 ? Math.round((totalCorrect / _totalQuizQ) * 100) : 0;
    const pct = Math.min(100, raw);
    document.getElementById('nav-fill').style.width = pct + '%';
    document.getElementById('nav-pct').textContent  = pct + '%';
    if (pct >= 100) reportCourseCompletion();
}

/* ── Module Welcome ───────────────────────────────── */
function showModuleWelcome(title, lessonCount, modCount, badgeCount, modIdx) {
    document.querySelectorAll('.module-title-area').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.lesson-item').forEach(el => el.classList.remove('active'));
    const ta = document.getElementById('mta-' + modIdx);
    if (ta && !ta.classList.contains('completed')) ta.classList.add('active');
    _curModIdx = modIdx;

    document.getElementById('wh-mod-title').textContent = title;
    document.getElementById('ws-mod').textContent       = modCount;
    document.getElementById('ws-les').textContent       = lessonCount;
    document.getElementById('ws-bdg').textContent       = badgeCount;

    hideAllViews();
    document.getElementById('view-welcome').style.display = 'flex';
    document.querySelector('.lesson-content').classList.add('welcome-mode');
}
function continueFromWelcome() {
    const first = document.querySelector('#mg-' + _curModIdx + ' .lesson-item');
    if (first) first.click();
}

/* ── Load lesson helper ───────────────────────────── */
function _prepLesson(title, desc, duration, modIdx, lesIdx) {
    document.querySelectorAll('.lesson-item').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.module-title-area').forEach(el => el.classList.remove('active'));
    const el = document.getElementById('li-' + modIdx + '-' + lesIdx);
    if (el) el.classList.add('active');
    _curLessonEl = el;
    _curModIdx   = modIdx;
    _curLesIdx   = lesIdx;

    document.getElementById('lh-title').textContent   = title;
    document.getElementById('lc-label').textContent   = title;
    document.getElementById('lb-title').textContent   = 'What is ' + title + '?';
    document.getElementById('lb-desc').innerHTML      = '<strong>' + title + '</strong> \u2014 ' + (desc || title);
    document.getElementById('lb-dur').textContent     = 'Duration: ' + (duration || '15m');
    document.getElementById('text-snippet').textContent = 'Read: ' + (desc || title);

    const btn = document.getElementById('btn-mark');
    // Restore green if already marked
    if (_curLessonEl && _curLessonEl.classList.contains('lesson-correct')) {
        btn.textContent = '\u2713 Completed';
        btn.classList.add('done');
    } else {
        btn.textContent = 'Mark Complete';
        btn.classList.remove('done');
    }
    document.getElementById('play-btn').style.display = 'flex';
}

function loadVideoLesson(title, desc, duration, modIdx, lesIdx) {
    loadLesson('Video', title, desc, duration, modIdx, lesIdx, null);
}
function loadTextLesson(title, desc, duration, modIdx, lesIdx) {
    loadLesson('Text', title, desc, duration, modIdx, lesIdx, null);
}

/* ── Media-aware lesson loader: shows the faculty-uploaded file ──
   Video mp4 → keeps the dark player area and plays inline.
   Image     → the image itself fills the area.
   PDF       → inline document preview.
   Other     → clean icon tile + attachment card below. */
function loadLesson(type, title, desc, duration, modIdx, lesIdx, fileUrl) {
    _prepLesson(title, desc, duration, modIdx, lesIdx);

    type = (type || 'Text').toLowerCase();
    var videoEl   = document.getElementById('lesson-video');
    var imageEl   = document.getElementById('lesson-image');
    var pdfEl     = document.getElementById('lesson-pdf');
    var fileCard  = document.getElementById('lesson-file-card');
    var videoArea = document.querySelector('#view-lesson .video-area');
    var isText    = (type === 'text' && !fileUrl);

    document.getElementById('view-lesson').classList.toggle('text-mode', isText);

    // Reset every preview surface before swapping lessons
    if (videoEl) {
        videoEl.pause();
        videoEl.removeAttribute('src');
        videoEl.load();
        videoEl.style.display = 'none';
    }
    if (imageEl) { imageEl.removeAttribute('src'); imageEl.style.display = 'none'; }
    if (pdfEl)   { pdfEl.removeAttribute('src');   pdfEl.style.display   = 'none'; }
    if (fileCard) fileCard.style.display = 'none';
    if (videoArea) videoArea.classList.remove('has-video', 'has-image', 'has-pdf', 'has-file');

    if (fileUrl && (type === 'video' || type === 'pdf' || type === 'file' || type === 'image')) {
        document.getElementById('play-btn').style.display = 'none';

        if (type === 'video') {
            // Videos keep the dark frame and play inline
            videoEl.src = fileUrl;
            videoEl.style.display = 'block';
            if (videoArea) videoArea.classList.add('has-video');
        } else {
            // Everything else replaces the placeholder with the file itself
            var ext = (fileUrl.split('.').pop() || 'file').toUpperCase().substring(0, 5);

            if (type === 'image' && imageEl) {
                imageEl.src = fileUrl;
                imageEl.alt = title;
                imageEl.style.display = 'block';
                if (videoArea) videoArea.classList.add('has-image');
            } else if (type === 'pdf' && pdfEl) {
                pdfEl.src = fileUrl;
                pdfEl.style.display = 'block';
                if (videoArea) videoArea.classList.add('has-pdf');
            } else if (videoArea) {
                videoArea.classList.add('has-file');
                var pb = document.getElementById('play-btn');
                if (pb) {
                    pb.style.display = 'flex';
                    pb.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5" style="width:30px;height:30px;margin-left:0;">'
                        + '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>'
                        + '<polyline points="14 2 14 8 20 8"/></svg>';
                }
            }

            // Attachment card below (open / download)
            if (fileCard) {
                var officeExts = ['DOC', 'DOCX', 'XLS', 'XLSX', 'PPT', 'PPTX'];
                var openUrl = fileUrl;
                if (officeExts.indexOf(ext) !== -1) {
                    openUrl = 'https://view.officeapps.live.com/op/view.aspx?src=' + encodeURIComponent(fileUrl);
                    document.getElementById('lesson-file-sub').textContent = ext + ' document · opens in the browser via Office Online viewer';
                } else {
                    document.getElementById('lesson-file-sub').textContent = ext + ' file · opens in a new tab';
                }
                document.getElementById('lesson-file-ext').textContent  = ext;
                document.getElementById('lesson-file-name').textContent = title + '.' + ext.toLowerCase();
                document.getElementById('lesson-file-open').href = openUrl;
                document.getElementById('lesson-file-dl').href   = fileUrl;
                fileCard.style.display = 'flex';
            }
        }
    }

    hideAllViews();
    document.getElementById('view-lesson').style.display = 'flex';
}

/* ── Mark Complete → green dot + unlock quiz check ── */
function markComplete() {
    const btn  = document.getElementById('btn-mark');
    const done = btn.classList.toggle('done');
    btn.textContent = done ? '\u2713 Completed' : 'Mark Complete';

    if (_curLessonEl) {
        if (done) {
            _curLessonEl.classList.add('lesson-correct');
            _curLessonEl.classList.remove('lesson-wrong', 'active');
        } else {
            _curLessonEl.classList.remove('lesson-correct');
        }
    }
    checkQuizUnlock(_curModIdx);
    checkModuleComplete(_curModIdx);
    saveProgress();
}

/* ── Unlock QUIZ when all lessons are marked complete ─ */
function checkQuizUnlock(modIdx) {
    const group   = document.getElementById('mg-' + modIdx);
    const lessons = Array.from(group.querySelectorAll('.lesson-item'));
    const allDone = lessons.length > 0 && lessons.every(l => l.classList.contains('lesson-correct'));
    const qbtn    = document.getElementById('qbtn-' + modIdx);
    if (!qbtn) return;
    if (allDone) {
        qbtn.classList.replace('locked', 'unlocked');
        qbtn.title = 'Take the quiz!';
    } else {
        qbtn.classList.replace('unlocked', 'locked');
        qbtn.title = 'Complete all lessons first';
    }
}

/* ── Module completion marker ─────────────────────── */
function checkModuleComplete(modIdx) {
    const group   = document.getElementById('mg-' + modIdx);
    const lessons = Array.from(group.querySelectorAll('.lesson-item'));
    const allDone = lessons.every(l => l.classList.contains('lesson-correct'));
    const ta      = document.getElementById('mta-' + modIdx);
    const sub     = document.getElementById('msub-' + modIdx);
    if (allDone && lessons.length > 0) {
        ta.classList.add('completed');
        ta.classList.remove('active');
        sub.innerHTML = '<span style="color:var(--green);font-weight:800;">\u2713 Completed</span>';
    }
}

/* ── Next lesson (Continue button) ───────────────── */
function nextLesson() {
    const all    = Array.from(document.querySelectorAll('.lesson-item'));
    const idx    = all.indexOf(_curLessonEl);
    const next   = all[idx + 1];

    // If next lesson exists AND is in the same module → go to it
    if (next && parseInt(next.dataset.module) === _curModIdx) {
        next.click();
        return;
    }

    // Otherwise (last lesson of this module) → return to module welcome screen
    const ta       = document.getElementById('mta-' + _curModIdx);
    const modTitle = ta ? ta.querySelector('.module-num').textContent.replace(/^Module \d+:\s*/,'') : 'the Module';
    const group    = document.getElementById('mg-' + _curModIdx);
    const lesCount = group ? group.querySelectorAll('.lesson-item').length : 0;

    showModuleWelcome(
        modTitle,
        lesCount,
        document.querySelectorAll('.module-group').length,
        {{ $badge_count ?? 1 }},
        _curModIdx
    );
}

/* ── QUIZ button click ────────────────────────────── */
function handleQuizClick(modIdx) {
    const qbtn = document.getElementById('qbtn-' + modIdx);
    if (qbtn && qbtn.classList.contains('locked')) {
        alert('Please mark all lessons as complete before taking the quiz.');
        return;
    }
    if (!QUIZ_DATA[modIdx] || !QUIZ_DATA[modIdx].questions || QUIZ_DATA[modIdx].questions.length === 0) {
        alert('No quiz has been added to this module yet.');
        return;
    }
    // Already answered (passed OR failed) → always open read-only review.
    // Retakes are only reachable through the Retake button, which the
    // server-side 24h cooldown guards.
    const viewOnly = (qbtn && qbtn.classList.contains('viewed'))
        || (_moduleScores[modIdx] !== undefined);
    showQuizView(modIdx, viewOnly);
}

/* ── Build quiz view ─────────────────────────────────
   viewOnly  = true  → read-only review of past attempt
   retakeOnly = true → only show questions answered wrong
─────────────────────────────────────────────────── */
function showQuizView(modIdx, viewOnly, retakeOnly) {
    _curModIdx  = modIdx;
    const data  = QUIZ_DATA[modIdx];
    if (!data) return;

    viewOnly   = viewOnly   || false;
    retakeOnly = retakeOnly || false;
    const savedAnswers = _moduleAnswers[modIdx] || {};

    // Decide which questions to render
    var questionsToShow; // array of { origIdx, q }
    if (retakeOnly) {
        questionsToShow = [];
        data.questions.forEach(function(q, qi) {
            const chosen = savedAnswers[qi];
            if (chosen && chosen !== q.correct) {
                questionsToShow.push({ origIdx: qi, q: q });
            }
        });
        // Safety: if somehow all were correct, show all (shouldn't reach retake in that case)
        if (questionsToShow.length === 0) questionsToShow = data.questions.map(function(q, qi){ return { origIdx: qi, q: q }; });
    } else {
        questionsToShow = data.questions.map(function(q, qi){ return { origIdx: qi, q: q }; });
    }

    // Store a map: rendered index → original index (used by submitQuiz to update correct slots)
    _retakeQMap = questionsToShow.map(function(item){ return item.origIdx; });

    const suffix = viewOnly ? ' — Review' : (retakeOnly ? ' — Retake (Wrong Answers)' : '');
    document.getElementById('qh-title').textContent = data.title + suffix;

    const container = document.getElementById('quiz-questions-container');
    container.innerHTML = '';

    questionsToShow.forEach(function(item, ri) {
        const q   = item.q;
        const qi  = item.origIdx;

        if (ri > 0) {
            const hr = document.createElement('hr');
            hr.className = 'quiz-divider';
            container.appendChild(hr);
        }

        const block = document.createElement('div');
        block.className = 'quiz-question-block';
        block.id = 'qblock-' + ri;

        const label = document.createElement('div');
        label.className = 'quiz-q-label';
        label.textContent = q.label;
        block.appendChild(label);

        const qbox = document.createElement('div');
        qbox.className = 'quiz-q-box';
        qbox.textContent = q.question;
        block.appendChild(qbox);

        const optWrap = document.createElement('div');
        optWrap.className = 'quiz-options';
        optWrap.id = 'opts-' + ri;

        q.options.forEach(function(opt) {
            const div = document.createElement('div');
            div.className = 'quiz-opt';
            div.dataset.letter = opt.letter;
            div.innerHTML = '<span class="quiz-opt-letter">' + opt.letter + '</span>'
                          + '<span class="quiz-opt-text">'   + opt.text   + '</span>';

            if (viewOnly) {
                if (opt.letter === q.correct) {
                    div.classList.add('correct-ans');
                } else if (savedAnswers[qi] && savedAnswers[qi] === opt.letter) {
                    div.classList.add('wrong-ans');
                }
                div.style.cursor = 'default';
                div.style.pointerEvents = 'none';
            } else {
                div.onclick = function() {
                    optWrap.querySelectorAll('.quiz-opt').forEach(el => el.classList.remove('selected'));
                    div.classList.add('selected');
                };
            }
            optWrap.appendChild(div);
        });
        block.appendChild(optWrap);
        container.appendChild(block);
    });

    // Buttons
    if (_cooldownTimerId) { clearInterval(_cooldownTimerId); _cooldownTimerId = null; }
    document.getElementById('btn-next-module').style.display = 'none';
    document.getElementById('btn-retake').style.display      = 'none';

    if (viewOnly) {
        document.getElementById('btn-quiz-submit').style.display = 'none';
        const scoreEl = document.getElementById('quiz-score-result');
        const correct = _moduleScores[modIdx] !== undefined ? _moduleScores[modIdx] : '?';
        const total   = data.questions.length;
        const pct     = _moduleScores[modIdx] !== undefined ? Math.round((_moduleScores[modIdx] / total) * 100) : 0;
        const pass    = pct >= (data.passingScore || 75);
        scoreEl.innerHTML = pass
            ? '\u2713 Passed — Score: ' + correct + '/' + total
            : 'Score: ' + correct + '/' + total + ' — Keep practicing!';
        scoreEl.className  = 'quiz-score-result ' + (pass ? 'pass' : 'fail');
        scoreEl.style.display = 'block';

        // Failed attempt being reviewed → show retake button in its cooldown/unlocked state
        if (!pass && _moduleScores[modIdx] !== undefined) {
            showRetakeButton(modIdx);
        }
    } else {
        document.getElementById('quiz-score-result').style.display = 'none';
        document.getElementById('btn-quiz-submit').style.display    = 'flex';
    }

    hideAllViews();
    document.getElementById('view-quiz').style.display = 'flex';
}

/* ── Submit quiz ──────────────────────────────────── */
function submitQuiz() {
    const data = QUIZ_DATA[_curModIdx];
    if (!data) return;

    let answered = true, correctCount = 0;
    var answers = {};   // save selected letters BEFORE highlights overwrite classes

    // _retakeQMap maps rendered index → original question index
    // On a full quiz _retakeQMap = [0,1,2,...]; on retake it's only the wrong indices
    var questionsInView = data.questions.map(function(q, qi){ return qi; });
    if (_retakeQMap.length > 0 && _retakeQMap.length <= data.questions.length) {
        questionsInView = _retakeQMap;
    }

    questionsInView.forEach(function(origQi, ri) {
        const q        = data.questions[origQi];
        const selected = document.querySelector('#opts-' + ri + ' .quiz-opt.selected');
        if (!selected) { answered = false; return; }

        const letter    = selected.dataset.letter;
        answers[origQi] = letter;                       // ← save to original index
        const isCorrect = letter === q.correct;
        if (isCorrect) correctCount++;

        // Highlight options
        document.querySelectorAll('#opts-' + ri + ' .quiz-opt').forEach(function(opt) {
            opt.classList.remove('selected');
            if (opt.dataset.letter === q.correct) opt.classList.add('correct-ans');
            else if (opt.dataset.letter === letter && !isCorrect) opt.classList.add('wrong-ans');
        });
    });

    // Merge retake answers back into full answer map (keep correct ones from previous attempt)
    var prevAnswers = _moduleAnswers[_curModIdx] || {};
    Object.keys(answers).forEach(function(k){ prevAnswers[k] = answers[k]; });
    answers = prevAnswers;

    if (!answered) {
        alert('Please answer all questions before submitting.');
        return;
    }

    _moduleAnswers[_curModIdx] = answers;   // store for view-only replay

    // For retakes: add newly-correct answers to previous correct count (only the retaken subset was scored)
    if (_retakeQMap.length > 0 && _retakeQMap.length < data.questions.length) {
        // Count how many questions from the full set are now correct
        var totalCorrect = 0;
        data.questions.forEach(function(q, qi) {
            if (answers[qi] === q.correct) totalCorrect++;
        });
        correctCount = totalCorrect;
    }
    _moduleScores[_curModIdx] = correctCount;
    _pendingQuizSubmit = { modIdx: _curModIdx, correct: correctCount };
    updateProgress();
    saveProgress();

    // Score result
    const pct      = Math.round((correctCount / data.questions.length) * 100);
    const pass     = pct >= (data.passingScore || 75);
    const scoreEl  = document.getElementById('quiz-score-result');
    scoreEl.innerHTML  = pass
        ? 'You passed! Score: ' + correctCount + '/' + data.questions.length
        : 'Score: ' + correctCount + '/' + data.questions.length + ' \u2014 Keep practicing!';
    scoreEl.className  = 'quiz-score-result ' + (pass ? 'pass' : 'fail');
    scoreEl.style.display = 'block';

    // Hide submit button
    document.getElementById('btn-quiz-submit').style.display = 'none';

    const moduleCount = Object.keys(QUIZ_DATA).length;
    const hasNextMod  = _curModIdx < moduleCount - 1;

    if (pass) {
        // ✅ PASSED → unlock next module
        clearRetakeCooldown(_curModIdx); // no cooldown needed anymore
        // Mark this quiz button as viewed (read-only)
        const doneBtn = document.getElementById('qbtn-' + _curModIdx);
        if (doneBtn) {
            doneBtn.classList.remove('unlocked');
            doneBtn.classList.add('viewed');
            doneBtn.textContent = 'VIEW';
            doneBtn.title = 'View your quiz results';
        }
        if (hasNextMod) {
            unlockModule(_curModIdx + 1);
            const nb = document.getElementById('btn-next-module');
            nb.textContent   = 'Continue to Module ' + (_curModIdx + 2) + ' →';
            nb.style.display = 'flex';
        } else {
            // Last module passed — course finished, award the badge
            reportCourseCompletion();
        }
    } else {
        // ❌ FAILED → read-only review; retake unlocks after the 24h cooldown
        const failBtn = document.getElementById('qbtn-' + _curModIdx);
        if (failBtn) {
            failBtn.classList.remove('unlocked');
            failBtn.classList.add('viewed');
            failBtn.textContent = 'VIEW';
            failBtn.title = 'View your quiz results';
        }
        startRetakeCooldown(_curModIdx);      // clock starts at the moment of failing
        showRetakeButton(_curModIdx);         // shows locked button with live countdown
    }
}

/* ── Unlock a module (remove locked state) ───────── */
function unlockModule(modIdx) {
    const group = document.getElementById('mg-' + modIdx);
    if (!group) return;
    group.classList.remove('module-locked');
    // Hide lock badge
    const lockBadge = document.getElementById('mlock-' + modIdx);
    if (lockBadge) lockBadge.style.display = 'none';
    // Update module sub text
    const sub = document.getElementById('msub-' + modIdx);
    if (sub) sub.innerHTML = '<span style="color:var(--green);font-size:11px;font-weight:700;">✓ Unlocked</span>';
}

/* ── Retake quiz 24-hour cooldown ─────────────────── */
const RETAKE_COOLDOWN_MS = 24 * 60 * 60 * 1000; // 24 hours
const RETAKE_KEY_PREFIX  = 'quizRetakeUnlockAt_{{ $course->id ?? ($course->title ?? "course") }}_';
var _cooldownTimerId = null;

function retakeKey(modIdx) {
    return RETAKE_KEY_PREFIX + modIdx;
}

/* Called when the student FAILS → start (or restart) the 24h clock */
function startRetakeCooldown(modIdx) {
    try {
        localStorage.setItem(retakeKey(modIdx), String(Date.now() + RETAKE_COOLDOWN_MS));
    } catch (e) { /* storage unavailable → button just stays enabled */ }
}

function clearRetakeCooldown(modIdx) {
    try { localStorage.removeItem(retakeKey(modIdx)); } catch (e) {}
}

/* Milliseconds remaining until retake is allowed (0 = allowed now) */
function retakeMsRemaining(modIdx) {
    let unlockAt = 0;
    try { unlockAt = parseInt(localStorage.getItem(retakeKey(modIdx)) || '0', 10); } catch (e) {}
    // The server-recorded cooldown always wins — it survives page changes,
    // new devices and cleared browser storage.
    if (_serverUnlocks && _serverUnlocks[modIdx] !== undefined) {
        unlockAt = Math.max(unlockAt, parseInt(_serverUnlocks[modIdx], 10) || 0);
    }
    return Math.max(0, unlockAt - Date.now());
}

function formatCooldown(ms) {
    const totalSec = Math.ceil(ms / 1000);
    const h = Math.floor(totalSec / 3600);
    const m = Math.floor((totalSec % 3600) / 60);
    const s = totalSec % 60;
    return (h > 0 ? h + 'h ' : '') + m + 'm ' + String(s).padStart(2, '0') + 's';
}

/* Show the retake button in the correct state (locked countdown or clickable),
   and keep the countdown ticking until it unlocks. */
function showRetakeButton(modIdx) {
    const btn = document.getElementById('btn-retake');
    if (_cooldownTimerId) { clearInterval(_cooldownTimerId); _cooldownTimerId = null; }

    function render() {
        const remaining = retakeMsRemaining(modIdx);
        if (remaining > 0) {
            btn.classList.add('cooldown');
            btn.disabled  = true;
            btn.innerHTML = 'Retake Quiz'
                          + '<span class="cooldown-timer">Available in ' + formatCooldown(remaining) + '</span>';
        } else {
            btn.classList.remove('cooldown');
            btn.disabled  = false;
            btn.innerHTML = 'Retake Quiz';
            if (_cooldownTimerId) { clearInterval(_cooldownTimerId); _cooldownTimerId = null; }
        }
    }

    render();
    if (retakeMsRemaining(modIdx) > 0) {
        _cooldownTimerId = setInterval(render, 1000);
    }
    btn.style.display = 'block';
}

/* ── Retake quiz (wrong questions only) ───────────── */
function retakeQuiz() {
    // Hard guard: ignore clicks while the 24h cooldown is still running
    if (retakeMsRemaining(_curModIdx) > 0) {
        showRetakeButton(_curModIdx);   // re-render the countdown
        return;
    }

    // Verify with the server before allowing the retake — the local clock
    // alone can be bypassed, the quiz_attempts table cannot.
    const modIdx = _curModIdx;
    const btn = document.getElementById('btn-retake');
    if (btn) btn.disabled = true;

    fetch('{{ route('courses.progress', $course->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ retake_check: modIdx })
    })
    .then(function (r) { return r.ok ? r.json() : null; })
    .then(function (data) {
        if (btn) btn.disabled = false;
        if (!data) return;

        // Server says it's still locked → restore the countdown and stop
        if (data.quiz_unlocks && data.quiz_unlocks[modIdx] && data.quiz_unlocks[modIdx] > Date.now()) {
            _serverUnlocks[modIdx] = data.quiz_unlocks[modIdx];
            try { localStorage.setItem(retakeKey(modIdx), String(data.quiz_unlocks[modIdx])); } catch (e) {}
            showRetakeButton(modIdx);
            return;
        }

        // Cooldown over → open the retake
        if (_cooldownTimerId) { clearInterval(_cooldownTimerId); _cooldownTimerId = null; }
        document.getElementById('btn-retake').style.display        = 'none';
        document.getElementById('quiz-score-result').style.display = 'none';
        showQuizView(modIdx, false, true);   // retakeOnly=true → only wrong questions shown
    })
    .catch(function () {
        if (btn) btn.disabled = false;
    });
}

/* ── Go to next module ────────────────────────────── */
function goToNextModule() {
    const nextIdx = _curModIdx + 1;
    const list    = document.getElementById('mod-' + nextIdx);
    const chev    = document.getElementById('chev-' + nextIdx);
    if (list) { list.classList.add('open'); if (chev) chev.classList.add('open'); }
    const first   = document.querySelector('#mg-' + nextIdx + ' .lesson-item');
    if (first) first.click();
}

function playVideo() {
    const pb = document.getElementById('play-btn');
    pb.style.display = pb.style.display === 'none' ? 'flex' : 'none';
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