{{--
    resources/views/Student_MyPathways.blade.php

    Expected data from the controller, e.g.:

    return view('Student_MyPathways', [
        'user' => $user,
        'pathway' => [
            'steps' => [
                ['label' => 'Goal 1', 'title' => 'Web Development', 'color' => '#2DD4CF', 'status' => 'completed'],
                ['label' => 'Goal 2', 'title' => 'Laravel',         'color' => '#D8C84A', 'status' => 'completed'],
                ['label' => 'Goal 3', 'title' => 'SQL',             'color' => '#E5483D', 'status' => 'current'],
                ['label' => 'Goal 4', 'title' => 'Locked',          'color' => '#9CA3AF', 'status' => 'locked'],
            ],
            'destination' => 'Full Stack Web Developer',
            'destination_color' => '#5FD93D',
            'connector_to_destination' => '#2563EB',
        ],
        'recommendations' => [
            ['title' => 'Take Blade Courses', 'completion' => 0],
            ['title' => 'Take SQL Courses',   'completion' => 0],
            ['title' => 'Networking Course',  'completion' => 0],
            ['title' => 'HTML & CSS',         'completion' => 0],
        ],
        'desiredPathway' => [
            'title' => 'Data Analyst',
            'current_competencies' => ['Networking Course', 'HTML & CSS'],
            'missing_competencies' => ['Python', 'Statistics'],
        ],
        'readinessPercent' => 60,
        'readinessLabel' => 'Data Analytics',
    ]);

    The status of each step ('completed' | 'current' | 'locked') is informational only here —
    color is what actually drives the look, so the controller has full control of styling per step.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Pathways | Upskill</title>
<style>
    :root{
        --navy:#13176b;
        --navy-deep:#0c0f4d;
        --gold:#dba617;
        --gold-dark:#c4930f;
        --cyan:#7fe9e3;
        --pill-bg:#f3e2a6;
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
    .side-icon-box svg{width:26px;height:26px;color:#fff;transition:color .15s ease;}
    .side-link.active .side-icon-box svg{color:var(--navy);}

    /* Main */
    .main{padding:32px 36px 60px;}
    .page-head h2{font-size:30px;margin:0 0 28px;color:var(--navy);}

    /* Pathway roadmap */
    .pathway-card{border:1px solid var(--line);border-radius:20px;box-shadow:var(--shadow);padding:34px 36px 30px;margin-bottom:28px;overflow-x:auto;}
    .pathway-track{display:flex;align-items:flex-start;min-width:760px;}
    .pathway-col{display:flex;flex-direction:column;align-items:center;gap:10px;flex-shrink:0;width:108px;}
    .goal-label{font-weight:700;color:var(--navy);font-size:13px;height:16px;line-height:16px;}
    .pathway-box{width:100px;height:80px;border-radius:10px;display:flex;align-items:center;justify-content:center;text-align:center;font-weight:800;color:#fff;font-size:13px;padding:6px;line-height:1.3;}
    .pathway-box.is-destination{width:130px;height:96px;font-size:14px;}
    .pathway-line{flex:1;height:4px;min-width:24px;margin-top:66px;}
    .pathway-col.is-destination-col{width:140px;}

    /* Bottom panels */
    .path-panels{display:grid;grid-template-columns:1fr 1fr 300px;gap:24px;align-items:stretch;}
    .path-panel{border:1px solid var(--line);border-radius:20px;box-shadow:var(--shadow);padding:22px 24px;}
    .path-pill{display:inline-block;background:var(--pill-bg);color:var(--navy);font-weight:700;padding:8px 22px;border-radius:999px;font-size:13px;margin-bottom:16px;}
    .path-pill-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap;}
    .path-pill-row .desired-title{color:#2563eb;font-weight:800;font-size:16px;}

    .rec-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-top:1px solid var(--line);font-size:14px;}
    .rec-row:first-of-type{border-top:none;}
    .rec-row .title{color:var(--navy);font-weight:700;}
    .rec-row .completion{color:var(--muted);}

    .comp-columns{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
    .comp-col h5{margin:0 0 10px;color:var(--muted);font-size:13px;font-weight:700;text-align:center;}
    .comp-col ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px;align-items:center;}
    .comp-col li{color:var(--navy);font-weight:700;font-size:14px;text-align:center;}

    .path-oval{border:1px solid var(--line);border-radius:50%/38%;box-shadow:var(--shadow);padding:40px 26px;display:flex;align-items:center;justify-content:center;text-align:center;height:100%;}
    .path-oval p{margin:0;color:var(--navy);font-weight:700;font-style:italic;font-size:14px;line-height:1.5;}

    .empty-state{color:var(--muted);font-size:14px;}

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:row;overflow-x:auto;position:static;margin:14px;border-radius:16px;}
        .path-panels{grid-template-columns:1fr;}
        .path-oval{border-radius:24px;}
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

    <nav class="nav-pills">
        <a href="{{ route('courses.browse') }}" class="{{ request()->routeIs('courses.*') ? 'is-active' : '' }}">Courses</a>
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
            <h2>My Pathways</h2>
        </div>

        {{-- Pathway roadmap --}}
        <div class="pathway-card">
            <div class="pathway-track">
                @foreach (($pathway['steps'] ?? []) as $step)
                    <div class="pathway-col">
                        <span class="goal-label">{{ $step['label'] }}</span>
                        <div class="pathway-box" style="background:{{ $step['color'] }}">{{ $step['title'] }}</div>
                    </div>
                    <div class="pathway-line" style="background:{{ $loop->last ? ($pathway['connector_to_destination'] ?? $step['color']) : $step['color'] }}"></div>
                @endforeach
                <div class="pathway-col is-destination-col">
                    <span class="goal-label"></span>
                    <div class="pathway-box is-destination" style="background:{{ $pathway['destination_color'] ?? '#5FD93D' }}">{{ $pathway['destination'] ?? 'Career Goal' }}</div>
                </div>
            </div>
        </div>

        {{-- Recommendations / Desired Pathway / Readiness --}}
        <div class="path-panels">

            <div class="path-panel">
                <span class="path-pill">Recommendations</span>
                @forelse (($recommendations ?? []) as $rec)
                    <div class="rec-row">
                        <span class="title">{{ $rec['title'] }}</span>
                        <span class="completion">{{ $rec['completion'] }}% Completion</span>
                    </div>
                @empty
                    <p class="empty-state">No recommendations right now.</p>
                @endforelse
            </div>

            <div class="path-panel">
                <div class="path-pill-row">
                    <span class="path-pill">Desired Pathway</span>
                    <span class="desired-title">{{ $desiredPathway['title'] ?? '' }}</span>
                </div>
                <div class="comp-columns">
                    <div class="comp-col">
                        <h5>Current Competencies</h5>
                        <ul>
                            @forelse (($desiredPathway['current_competencies'] ?? []) as $item)
                                <li>{{ $item }}</li>
                            @empty
                                <li class="empty-state">None yet</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="comp-col">
                        <h5>Missing Competencies</h5>
                        <ul>
                            @forelse (($desiredPathway['missing_competencies'] ?? []) as $item)
                                <li>{{ $item }}</li>
                            @empty
                                <li class="empty-state">None</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <div class="path-oval">
                <p>You Currently meet {{ $readinessPercent ?? 0 }}% of the required Competencies for {{ $readinessLabel ?? 'this Pathway' }}.</p>
            </div>

        </div>

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