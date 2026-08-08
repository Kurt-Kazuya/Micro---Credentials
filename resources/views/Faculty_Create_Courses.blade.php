{{--
    resources/views/Faculty_Create_Courses.blade.php

    Faculty › Create Courses — single self-contained Blade view (no layout).
    ⚠ STANDALONE within the faculty side: links back to the Faculty
      Dashboard (route 'faculty.dashboard') and Faculty My Courses
      (route 'faculty.courses'). Saving POSTs to route
      'faculty.create.store', which stores the course in the SESSION
      (no database yet) and redirects to the Managing Course screen
      for the newly created course.

    Expected data from the route, e.g.:

    return view('Faculty_Create_Courses', [
        'user'       => $user,            // needs ->name (avatar optional)
        'categories' => ['Web Development', ...],
        'programs'   => ['BSIT', ...],
        'terms'      => ['1st Semester 2026 - 2027', ...],
        'levels'     => ['Beginner', 'Intermediate', 'Advanced'],
    ]);
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Courses | Upskill</title>
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
        --green:#22c55e;
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
    .main{padding:32px 36px 60px;max-width:860px;}
    .page-head{margin-bottom:26px;}
    .page-head h2{font-size:26px;margin:0 0 4px;color:var(--navy);}
    .page-head p{margin:0;color:var(--muted);font-size:13px;font-weight:600;}

    /* Form cards */
    .form-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);margin-bottom:28px;overflow:hidden;}
    .form-card-head{padding:16px 24px;border-bottom:1px solid var(--line);}
    .form-card-head h3{margin:0;font-size:18px;color:var(--navy);}
    .form-card-body{padding:22px 24px;}

    .field{margin-bottom:20px;}
    .field:last-child{margin-bottom:0;}
    .field label{display:block;font-size:12px;font-weight:800;color:var(--navy);margin-bottom:8px;}
    .field label .req{color:#dc2626;}
    .field-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;}

    .input, .select, .textarea{
        width:100%;border:1.5px solid #c9ccdb;border-radius:999px;padding:12px 20px;
        font-size:13px;font-weight:600;color:var(--ink);outline:none;font-family:inherit;background:#fff;
    }
    .input::placeholder, .textarea::placeholder{color:#9aa0b4;font-weight:500;}
    .textarea{border-radius:14px;min-height:110px;resize:vertical;}
    .textarea.tall{min-height:130px;}

    .select-wrap{position:relative;}
    .select{appearance:none;-webkit-appearance:none;-moz-appearance:none;padding-right:48px;cursor:pointer;}
    .select-wrap::after{
        content:"";position:absolute;right:16px;top:50%;transform:translateY(-50%);
        width:22px;height:22px;border-radius:50%;background:var(--navy);pointer-events:none;
    }
    .select-wrap::before{
        content:"";position:absolute;right:22px;top:50%;transform:translateY(-60%) rotate(45deg);
        width:6px;height:6px;border-right:2px solid #fff;border-bottom:2px solid #fff;z-index:1;pointer-events:none;
    }

    .file-row{display:flex;align-items:center;border:1.5px solid #c9ccdb;border-radius:8px;overflow:hidden;max-width:300px;}
    .file-row .file-btn{background:#f3f4f6;border:none;border-right:1.5px solid #c9ccdb;padding:9px 14px;font-size:12px;font-weight:700;color:var(--ink);white-space:nowrap;}
    .file-row .file-name{padding:9px 14px;font-size:12px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .file-row input[type=file]{display:none;}

    /* Settings row */
    .settings-row{display:flex;align-items:center;gap:34px;flex-wrap:wrap;}
    .radio-item{display:flex;align-items:center;gap:10px;font-size:12px;font-weight:700;color:var(--ink);cursor:pointer;}
    .radio-item input{display:none;}
    .radio-dot{width:18px;height:18px;border-radius:50%;border:1.5px solid #c9ccdb;background:#fff;flex-shrink:0;position:relative;}
    .radio-item input:checked + .radio-dot{background:var(--green);border-color:var(--green);}
    .settings-label{font-size:12px;font-weight:800;color:var(--navy);}

    /* Actions */
    .form-actions{display:flex;gap:14px;}
    .btn-save{background:var(--navy);border:none;color:#fff;font-weight:700;padding:12px 36px;border-radius:999px;font-size:15px;}
    .btn-cancel{background:#fff;border:1.5px solid #c9ccdb;color:var(--muted);font-weight:700;padding:12px 30px;border-radius:999px;font-size:15px;}

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:row;overflow-x:auto;position:static;margin:14px;border-radius:16px;}
        .field-grid{grid-template-columns:1fr;}
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

    <div class="icon-cluster">
        <a href="{{ route('notifications.index') }}" class="icon-circle" title="Notifications">
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

    {{-- Sidebar — Dashboard & My Courses connected, Create Courses is this page --}}
    <aside class="sidebar">
        <a href="{{ route('faculty.dashboard') }}" class="side-link">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
            </span>
            Dashboard
        </a>
        <a href="{{ route('faculty.courses') }}" class="side-link">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </span>
            My Courses
        </a>
        <a href="#" class="side-link active">
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

        <div class="page-head">
            <h2>Create New Course</h2>
            <p>Fill in the Details below to create your course</p>
        </div>

        {{-- ✅ Real form — Save stores the course (session for now) and
             redirects to the Managing Course screen for the new course --}}
        <form method="POST" action="{{ route('faculty.create.store') }}" enctype="multipart/form-data" id="create-course-form">
            @csrf

        {{-- Validation / save errors — only visible when something went wrong --}}
        @if ($errors->any())
        <div style="background:#fdecea;border:1px solid #f2b8b5;color:#a93226;padding:14px 18px;border-radius:12px;margin-bottom:18px;font-size:14px;font-weight:600;">
            <strong>The course couldn't be saved. Please fix the following:</strong>
            <ul style="margin:8px 0 0 20px;font-weight:500;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ── Basic Information ── --}}
        <section class="form-card">
            <div class="form-card-head"><h3>Basic Information</h3></div>
            <div class="form-card-body">

                <div class="field">
                    <label>Course Title <span class="req">*</span></label>
                    <input class="input" type="text" name="title" placeholder="e.g. Introduction to Web Development" value="{{ old('title') }}" required>
                </div>

                <div class="field field-grid">
                    <div>
                        <label>Category <span class="req">*</span></label>
                        <div class="select-wrap">
                            <select class="select" name="category">
                                <option value="">Select a Category</option>
                                @foreach ($categories ?? [] as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field field-grid">
                    <div>
                        <label>Term <span class="req">*</span></label>
                        <div class="select-wrap">
                            <select class="select" name="term">
                                <option value="">Select an academic term</option>
                                @foreach ($terms ?? [] as $term)
                                    <option value="{{ $term }}">{{ $term }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label>Level <span class="req">*</span></label>
                        <div class="select-wrap">
                            <select class="select" name="level">
                                @foreach ($levels ?? ['Beginner'] as $level)
                                    <option value="{{ $level }}">{{ $level }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label>Description <span class="req">*</span></label>
                    <textarea class="textarea tall" name="description" placeholder="What will students learn in this course?"></textarea>
                </div>

                <div class="field field-grid">
                    <div>
                        <label>Duration (Hours)</label>
                        <input class="input" type="number" name="duration" placeholder="0" min="0">
                    </div>
                    <div>
                        <label>Passing Score</label>
                        <input class="input" type="number" name="passing_score" placeholder="75" min="0" max="100">
                    </div>
                </div>

            </div>
        </section>

        {{-- ── Thumbnail ── --}}
        <section class="form-card">
            <div class="form-card-head"><h3>Thumbnail</h3></div>
            <div class="form-card-body">
                {{-- ⚠ Not connected — file is not uploaded anywhere yet --}}
                <label class="file-row">
                    <span class="file-btn">Choose File</span>
                    <span class="file-name" id="thumbName">No File Chosen</span>
                    <input type="file" name="thumbnail" accept="image/*"
                           onchange="document.getElementById('thumbName').textContent = this.files.length ? this.files[0].name : 'No File Chosen'">
                </label>
            </div>
        </section>

        {{-- ── Learning Details ── --}}
        <section class="form-card">
            <div class="form-card-head"><h3>Learning Details</h3></div>
            <div class="form-card-body">

                <div class="field">
                    <label>Learning Objectives</label>
                    <textarea class="textarea" name="learning_objectives"></textarea>
                </div>

                <div class="field" style="max-width:340px;">
                    <label>Prerequisites (if any)</label>
                    <div class="select-wrap">
                        <select class="select" name="prerequisites">
                            <option value="">Select an academic term</option>
                            @foreach ($terms ?? [] as $term)
                                <option value="{{ $term }}">{{ $term }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label>Skills</label>
                    <textarea class="textarea" name="skills"></textarea>
                </div>

            </div>
        </section>

        {{-- ── Settings ── --}}
        <section class="form-card">
            <div class="form-card-head"><h3>Settings</h3></div>
            <div class="form-card-body">
                <div class="settings-row">
                    <span class="settings-label">Status</span>

                    <label class="radio-item">
                        <input type="radio" name="status" value="draft">
                        <span class="radio-dot"></span>
                        Draft
                    </label>

                    <label class="radio-item">
                        <input type="radio" name="status" value="submit" checked>
                        <span class="radio-dot"></span>
                        Submit for approval
                    </label>
                </div>
            </div>
        </section>

        {{-- ✅ Save submits the form → Managing Course screen for the new
             course · Cancel returns to the dashboard without saving --}}
        <div class="form-actions">
            <button class="btn-save" type="submit">Save</button>
            <a href="{{ route('faculty.dashboard') }}"><button class="btn-cancel" type="button">Cancel</button></a>
        </div>

        </form>

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

<script>
    // ── Draft persistence: keep filled fields when leaving & returning ──
    (function () {
        var form = document.getElementById('create-course-form');
        if (!form) return;
        var KEY = 'upskill_create_course_draft';

        // Restore saved values on load (server-rendered old() values win when
        // the form bounced back from a validation error).
        try {
            var saved = JSON.parse(localStorage.getItem(KEY) || '{}');
            var hasServerValues = {{ $errors->any() ? 'true' : 'false' }};
            if (!hasServerValues) {
                Object.keys(saved).forEach(function (name) {
                    var field = form.querySelector('[name="' + name + '"]');
                    if (!field) return;
                    if (field.type === 'radio') {
                        var r = form.querySelector('[name="' + name + '"][value="' + saved[name] + '"]');
                        if (r) r.checked = true;
                    } else if (field.type !== 'file') {
                        field.value = saved[name];
                    }
                });
            }
        } catch (e) {}

        // Save every change.
        form.addEventListener('input', saveDraft);
        form.addEventListener('change', saveDraft);
        function saveDraft() {
            var data = {};
            form.querySelectorAll('input, select, textarea').forEach(function (f) {
                if (!f.name || f.type === 'file' || f.type === 'password') return;
                if (f.type === 'radio') { if (f.checked) data[f.name] = f.value; return; }
                if (f.type === 'checkbox') { data[f.name] = f.checked ? f.value : ''; return; }
                data[f.name] = f.value;
            });
            try { localStorage.setItem(KEY, JSON.stringify(data)); } catch (e) {}
        }

        // Clear the draft once the course is actually created.
        form.addEventListener('submit', function () {
            try { localStorage.removeItem(KEY); } catch (e) {}
        });
    })();
</script>
</body>
</html>