<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upskill – User Management</title>
    <style>
        /* ─── Reset ─────────────────────────────────────────────── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        /* ─── Tokens ─────────────────────────────────────────────── */
        :root {
            --navy:        #0d1b6e;
            --navy-light:  #1a2fa0;
            --navy-pale:   #e8eaf6;
            --white:       #ffffff;
            --surface:     #f4f6fb;
            --border:      #e2e5f0;
            --text-main:   #0d1b6e;
            --text-muted:  #6b7280;
            --text-sub:    #9ca3af;
            --shadow-sm:   0 1px 4px rgba(13,27,110,.07);
            --shadow-md:   0 3px 12px rgba(13,27,110,.11);
            --radius-card: 12px;
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
           SIDEBAR — identical design to Admin_Main_Home
           Only the active item changes to "User Management"
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
            padding: 40px 36px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ─── USER SEARCH BAR ─────────────────────────────────── */
        .user-search-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 24px;
            padding: 9px 20px;
            width: 100%;
            max-width: 420px;
            margin-bottom: 28px;
            box-shadow: var(--shadow-sm);
            transition: border-color .2s, box-shadow .2s;
        }

        .user-search-wrap:focus-within {
            border-color: #5c6bc0;
            box-shadow: 0 0 0 3px rgba(92,107,192,.12);
        }

        .user-search-wrap svg { color: var(--text-sub); flex-shrink: 0; }

        .user-search-wrap input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 0.875rem;
            color: var(--text-main);
            background: none;
        }

        .user-search-wrap input::placeholder { color: var(--text-sub); }

        /* ─── TABLE CARD ──────────────────────────────────────── */
        .table-card {
            width: 100%;
            max-width: 780px;
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        /* ─── TABLE ───────────────────────────────────────────── */
        .um-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Header row */
        .um-table thead tr {
            border-bottom: 2px solid var(--border);
        }

        .um-table thead th {
            padding: 14px 18px;
            text-align: left;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--navy);
            white-space: nowrap;
        }

        .um-table thead th:first-child { padding-left: 22px; }
        .um-table thead th:last-child  { padding-right: 22px; }

        /* Body rows */
        .um-table tbody tr {
            border-bottom: 1px solid #f0f2f9;
            transition: background .15s;
        }

        .um-table tbody tr:last-child { border-bottom: none; }

        .um-table tbody tr:hover { background: #f7f9ff; }

        .um-table tbody td {
            padding: 14px 18px;
            font-size: 0.845rem;
            color: var(--navy);
            font-weight: 600;
        }

        .um-table tbody td:first-child { padding-left: 22px; }
        .um-table tbody td:last-child  { padding-right: 22px; }

        /* Status pill colours */
        .status-badge {
            display: inline-block;
            font-size: 0.775rem;
            font-weight: 700;
        }

        .status-active    { color: #15803d; }
        .status-pending   { color: #b45309; }
        .status-published { color: var(--navy); }
        .status-draft     { color: var(--text-muted); }

        .user-tools {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .user-search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            flex: 1;
            max-width: 580px;
        }

        .user-search-form input,
        .user-search-form select {
            border: 1.5px solid var(--border);
            border-radius: 999px;
            padding: 9px 14px;
            font-size: 0.875rem;
            color: var(--text-main);
            background: var(--white);
        }

        .user-search-form button {
            border: none;
            border-radius: 999px;
            padding: 9px 14px;
            background: var(--navy);
            color: var(--white);
            font-weight: 700;
            cursor: pointer;
        }

        .code-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eef3ff;
            color: var(--navy);
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 0.775rem;
            font-weight: 700;
        }

        .empty-state {
            width: 100%;
            max-width: 780px;
            padding: 24px;
            border-radius: var(--radius-card);
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
{{-- Identical to Admin_Main_Home — only "User Management" carries the active pill --}}
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
            {{-- Active pill on User Management --}}
            <a href="{{ route('admin.usermanagement') }}" class="sb-item active">
                <span class="sb-item-text">User Management</span>
            </a>
            <a href="{{ route('admin.courses') }}" class="sb-item">
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

    <div class="user-tools">
        <form class="user-search-form" action="{{ route('admin.usermanagement') }}" method="GET">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search by name, email, or user code">
            <select name="sort">
                <option value="created_at" {{ ($sort ?? 'created_at') === 'created_at' ? 'selected' : '' }}>Newest</option>
                <option value="name" {{ ($sort ?? '') === 'name' ? 'selected' : '' }}>Name</option>
                <option value="role" {{ ($sort ?? '') === 'role' ? 'selected' : '' }}>Role</option>
                <option value="user_code" {{ ($sort ?? '') === 'user_code' ? 'selected' : '' }}>User Code</option>
            </select>
            <input type="hidden" name="direction" value="{{ $direction === 'asc' ? 'desc' : 'asc' }}">
            <button type="submit">Filter</button>
        </form>
    </div>

    @if ($users->isEmpty())
        <div class="empty-state">No users found for the current filter.</div>
    @else
        <div class="table-card">
            <table class="um-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>User Code</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    <tr>
                        <td>{{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->username ?? '—') }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ match ((int) ($user->role_id ?? 3)) {1 => 'Administrator', 2 => 'Faculty', 3 => 'Learner', default => 'Learner'} }}</td>
                        <td><span class="code-pill">{{ $user->user_code ?? '—' }}</span></td>
                        <td>
                            <span class="status-badge {{ $user->is_active ? 'status-active' : 'status-pending' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</main>
</div>{{-- /layout --}}

{{-- ── SCRIPTS ─────────────────────────────────────────────────────────── --}}
<script>
    function toggleSection(id) {
        const section = document.getElementById(id);
        if (!section || section.classList.contains('locked')) return;
        section.classList.toggle('open');
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