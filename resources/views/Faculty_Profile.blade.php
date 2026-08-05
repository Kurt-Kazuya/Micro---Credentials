{{--
    resources/views/Faculty_Profile.blade.php

    Faculty › My Profile — single self-contained Blade view (no layout file).
    Combines TWO designs into one page, restyled to the Upskill theme:

      ① Profile card + About me + Settings   (reference image 1)
      ② Performance / Activity charts + Courses table (reference image 2)

    ⚠ STANDALONE: the ONLY connections on this page are:
        • Sidebar  Dashboard      → route('faculty.dashboard')
        • Sidebar  My Courses     → route('faculty.courses')
        • Sidebar  Create Courses → route('faculty.create')
        • Topbar   Courses pill   → route('faculty.courses')
        • Topbar   Dashboard pill → route('faculty.dashboard')
      Every other button / link / input is intentionally non-functioning
      (href="#" or plain <button type="button">) until real routes exist.

    Expected data from the route, e.g.:

    return view('Faculty_Profile', [
        'user' => (object) [
            'name', 'role', 'phone', 'email', 'joined', 'location',
            'birth_date', 'gender', 'education', 'bio', 'about',
            'language', 'timezone', 'avatar_url',
        ],
        'languages'      => ['English', ...],
        'timezones'      => ['(GMT + 8:00) Asia/Manila', ...],
        'performance'    => [ ['label' => 'Jan', 'percent' => 62], ... ],   // last 6 months
        'activity'       => [ ['label' => 'Mon', 'hours' => 5], ... ],      // this week
        'profileCourses' => collect of (object) [
            'title', 'category', 'students', 'rating',
            'completion', 'status',
        ],
    ]);
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile | Upskill</title>
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
    .icon-circle{width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;box-shadow:0 6px 16px rgba(19,23,107,0.12);transition:transform .2s ease, box-shadow .2s ease, background-color .2s ease;}
    .icon-circle:hover{transform:translateY(-1px);box-shadow:0 10px 20px rgba(19,23,107,0.16);}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}
    .notification-btn{position:relative;width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg, #ffffff 0%, #eef4ff 100%);border:1px solid rgba(19,23,107,0.08);}
    .notification-btn:hover{background:linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);}
    .notification-btn svg{width:21px;height:21px;}
    .notification-badge{position:absolute;top:5px;right:5px;width:10px;height:10px;border-radius:50%;background:#ef4444;border:2px solid #fff;box-shadow:0 0 0 2px rgba(239,68,68,0.18);} 

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
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:28px;}
    .page-head h2{font-size:30px;margin:0 0 6px;color:var(--navy);}
    .page-head p{margin:0;color:var(--muted);font-size:15px;font-weight:600;}
    .page-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .btn-edit-profile{background:#fff;border:1.5px solid var(--line);color:var(--navy);font-weight:800;padding:12px 28px;border-radius:12px;font-size:15px;box-shadow:var(--shadow);transition:border-color .2s;}
    .btn-edit-profile:hover{border-color:var(--navy);}
    .btn-logout{background:#fff1f2;border:1.5px solid #fecdd3;color:#b91c1c;font-weight:800;padding:12px 28px;border-radius:12px;font-size:15px;box-shadow:var(--shadow);}
    .btn-logout:hover{background:#fee2e2;border-color:#f43f5e;}

    /* Panels (same pattern as dashboard side panels) */
    .panel{border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);overflow:hidden;margin-bottom:28px;background:#fff;}
    .panel-head{background:var(--gold);color:var(--navy);font-weight:800;font-size:20px;padding:14px 22px;display:flex;align-items:center;justify-content:space-between;gap:12px;}
    .panel-head .range{font-size:12px;font-weight:700;background:rgba(255,255,255,0.55);padding:6px 14px;border-radius:999px;}
    .panel-head .head-ico{display:flex;align-items:center;gap:10px;}
    .panel-head .head-ico svg{width:22px;height:22px;}
    .panel-body{padding:22px;}

    /* ── ① Profile identity card ──────────────────────────────────────── */
    .profile-card{border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);padding:26px 30px;display:flex;align-items:center;gap:30px;flex-wrap:wrap;margin-bottom:28px;}
    .avatar-ring{width:120px;height:120px;border-radius:50%;border:3px solid var(--navy);display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#fff;overflow:hidden;background-size:cover;background-position:center;}
    .avatar-ring svg{width:66px;height:66px;color:var(--navy);}
    .identity{flex:1;min-width:260px;}
    .identity h3{margin:0 0 14px;font-size:26px;color:var(--navy);}
    .identity-grid{display:grid;grid-template-columns:repeat(2,minmax(200px,1fr));gap:12px 26px;}
    .id-item{display:flex;align-items:center;gap:10px;font-size:14px;font-weight:700;color:var(--ink);}
    .id-item svg{width:18px;height:18px;color:var(--navy);flex-shrink:0;}
    .role-pill{display:inline-block;background:var(--cyan);color:var(--navy);font-weight:800;font-size:12px;padding:5px 16px;border-radius:999px;margin-left:10px;vertical-align:middle;}

    /* Two-column: About me | Settings */
    .two-col{display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-bottom:0;}

    .about-row{display:grid;grid-template-columns:170px 1fr;gap:8px 14px;align-items:start;padding:9px 0;border-bottom:1px dashed var(--line);}
    .about-row:last-child{border-bottom:none;}
    .about-label{display:flex;align-items:center;gap:10px;font-weight:800;font-size:14px;color:var(--navy);}
    .about-label svg{width:18px;height:18px;flex-shrink:0;}
    .about-value{font-size:14px;font-weight:700;color:var(--ink);}
    .about-value.link-blue{color:#1d4ed8;}
    .about-intro{font-size:12px;color:var(--muted);line-height:1.6;margin:0 0 14px;font-weight:600;}

    .set-row{display:grid;grid-template-columns:130px 1fr auto;gap:10px 14px;align-items:center;padding:9px 0;border-bottom:1px dashed var(--line);}
    .set-row:last-child{border-bottom:none;}
    .set-label{display:flex;align-items:center;gap:10px;font-weight:800;font-size:14px;color:var(--navy);}
    .set-label svg{width:18px;height:18px;flex-shrink:0;}
    .set-value{font-size:14px;font-weight:700;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .btn-change{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:7px 20px;border-radius:999px;font-size:12px;}
    .set-select{grid-column:2 / 4;width:100%;border:1.5px solid #c9ccdb;border-radius:10px;padding:10px 12px;font-size:13px;font-weight:700;color:var(--ink);background:#fff;outline:none;font-family:inherit;}

    /* ── ② Performance + Activity charts ─────────────────────────────── */
    .charts-grid{display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-bottom:28px;}

    .chart{display:flex;align-items:flex-end;gap:14px;height:190px;padding-top:26px;}
    .chart .col{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;gap:8px;min-width:0;}
    .chart .bar-wrap{width:100%;max-width:52px;display:flex;flex-direction:column;justify-content:flex-end;height:100%;position:relative;}
    .chart .bar{width:100%;border-radius:10px 10px 4px 4px;background:var(--navy);transition:opacity .15s ease;}
    .chart .bar:hover{opacity:.85;}
    .chart .bar.gold{background:var(--gold);}
    .chart .bar-value{position:absolute;top:-24px;left:50%;transform:translateX(-50%);font-size:11px;font-weight:800;color:var(--navy);background:var(--cyan);padding:3px 8px;border-radius:999px;white-space:nowrap;}
    .chart .axis-label{font-size:12px;font-weight:700;color:var(--muted);}

    /* ── ② Courses table ──────────────────────────────────────────────── */
    .filter-chip{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:7px 20px;border-radius:999px;font-size:12px;display:inline-flex;align-items:center;gap:8px;}
    .filter-chip svg{width:14px;height:14px;}
    .courses-table{width:100%;border-collapse:collapse;}
    .courses-table th{font-size:12px;font-weight:800;color:var(--muted);text-transform:none;text-align:left;padding:10px 12px;border-bottom:1.5px solid var(--line);white-space:nowrap;}
    .courses-table td{padding:16px 12px;border-bottom:1px solid var(--line);vertical-align:middle;font-size:14px;font-weight:700;color:var(--ink);}
    .courses-table tr:last-child td{border-bottom:none;}
    .tbl-course{display:flex;align-items:center;gap:14px;min-width:230px;}
    .tbl-thumb{width:52px;height:52px;border-radius:12px;background:var(--thumb);flex-shrink:0;background-size:cover;background-position:center;}
    .tbl-course h4{margin:0 0 4px;font-size:15px;color:var(--navy);}
    .tbl-sub{font-size:11px;color:var(--muted);font-weight:700;}
    .tbl-sub .cat{color:var(--gold-dark);}
    .rating{display:inline-flex;align-items:center;gap:6px;white-space:nowrap;}
    .rating svg{width:16px;height:16px;color:var(--gold);flex-shrink:0;}
    .progress-cell{display:flex;align-items:center;gap:10px;min-width:150px;}
    .progress-track{flex:1;height:8px;border-radius:999px;background:#e8eaf6;overflow:hidden;min-width:80px;}
    .progress-fill{height:100%;border-radius:999px;background:var(--navy);}
    .progress-pct{font-size:12px;font-weight:800;color:var(--navy);white-space:nowrap;}
    .status-pill{display:inline-block;font-size:12px;font-weight:700;padding:6px 18px;border-radius:999px;white-space:nowrap;}
    .status-pill.active{background:#86efac;color:#14532d;}
    .status-pill.archived{background:#e5e7eb;color:#374151;}
    .status-pill.draft{background:#fde68a;color:#713f12;}
    .empty-state{border:1px dashed var(--line);border-radius:16px;padding:30px;text-align:center;color:var(--muted);font-size:14px;}

    /* ===== EDIT PROFILE MODAL (design from Student_Profile) ===== */
    .modal-overlay{
        position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;
        display:flex;justify-content:flex-end;
        opacity:0;visibility:hidden;
        transition:opacity .3s ease,visibility .3s ease;
    }
    .modal-overlay.open{opacity:1;visibility:visible;}
    .modal-panel{
        width:520px;max-width:95vw;height:100vh;
        background:#fff;display:flex;flex-direction:column;
        box-shadow:-12px 0 50px rgba(19,23,107,.18);
        transform:translateX(100%);
        transition:transform .38s cubic-bezier(.16,1,.3,1);
        overflow:hidden;
    }
    .modal-overlay.open .modal-panel{transform:translateX(0);}
    .modal-header{
        padding:22px 28px 16px;border-bottom:1px solid var(--line);
        display:flex;align-items:center;justify-content:space-between;flex-shrink:0;
        background:#fff;
    }
    .modal-header-left{display:flex;align-items:center;gap:12px;}
    .modal-header-left svg{width:22px;height:22px;color:var(--navy);}
    .modal-header h3{color:var(--navy);margin:0;font-size:20px;font-weight:800;}
    .modal-header p{margin:2px 0 0;color:var(--muted);font-size:12px;}
    .modal-close{
        width:36px;height:36px;border-radius:50%;
        border:1.5px solid var(--line);background:#fff;
        color:var(--navy);font-size:22px;line-height:1;
        display:flex;align-items:center;justify-content:center;cursor:pointer;
        transition:background .2s,border-color .2s;flex-shrink:0;
    }
    .modal-close:hover{background:var(--line);}
    .modal-tabs{
        display:flex;gap:0;border-bottom:1px solid var(--line);
        padding:0 28px;flex-shrink:0;background:#fff;
    }
    .tab-btn{
        background:none;border:none;
        padding:13px 14px;font-size:13.5px;font-weight:700;
        color:var(--muted);cursor:pointer;
        border-bottom:3px solid transparent;margin-bottom:-1px;
        transition:color .2s,border-color .2s;white-space:nowrap;
    }
    .tab-btn.active{color:var(--navy);border-bottom-color:var(--navy);}
    .tab-btn:hover:not(.active){color:var(--navy);}
    .modal-body{flex:1;overflow-y:auto;padding:24px 28px;scrollbar-width:thin;}
    .tab-content{display:none;}
    .tab-content.active{display:block;}
    .avatar-upload-area{
        display:flex;align-items:center;gap:18px;
        padding:18px;border:2px dashed var(--line);border-radius:14px;
        margin-bottom:24px;background:#fafbff;
    }
    .avatar-preview{
        width:70px;height:70px;border-radius:50%;border:3px solid var(--navy);
        display:flex;align-items:center;justify-content:center;
        background-size:cover;background-position:center;flex-shrink:0;
        overflow:hidden;background-color:#f0f3ff;
    }
    .avatar-preview svg{width:38px;height:38px;color:var(--navy);}
    .avatar-upload-text p{margin:0 0 8px;color:var(--muted);font-size:12.5px;line-height:1.5;}
    .btn-upload-avatar{
        background:var(--navy);color:#fff;border:none;border-radius:8px;
        padding:8px 16px;font-size:12.5px;font-weight:700;cursor:pointer;
        font-family:inherit;display:inline-flex;align-items:center;gap:6px;
        transition:background .2s;
    }
    .btn-upload-avatar:hover{background:var(--navy-deep);}
    .form-group{margin-bottom:18px;}
    .form-group label{display:block;font-weight:700;color:var(--navy);font-size:12.5px;margin-bottom:6px;letter-spacing:.3px;}
    .form-group input,.form-group select,.form-group textarea{
        width:100%;border:1.5px solid var(--line);border-radius:10px;
        padding:10px 13px;font-size:14px;font-family:inherit;
        color:var(--navy);background:#fff;outline:none;
        transition:border-color .2s,box-shadow .2s;
    }
    .form-group input:focus,.form-group select:focus,.form-group textarea:focus{
        border-color:var(--navy);box-shadow:0 0 0 3px rgba(19,23,107,.08);
    }
    .form-group textarea{resize:vertical;min-height:78px;line-height:1.5;}
    .form-group .hint{color:var(--muted);font-size:11.5px;margin:4px 0 0;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .form-divider{border:none;border-top:1px solid var(--line);margin:22px 0;}
    .form-section-label{font-size:11px;font-weight:800;color:var(--muted);letter-spacing:1px;text-transform:uppercase;margin:0 0 14px;}
    .form-errors{
        background:#fef2f2;border:1px solid #fecaca;border-radius:10px;
        padding:12px 15px;margin-bottom:20px;color:#b91c1c;font-size:12.5px;line-height:1.6;
    }
    .form-errors ul{margin:0;padding-left:18px;}
    .pwd-wrap{position:relative;}
    .pwd-wrap input{padding-right:44px;}
    .pwd-toggle{
        position:absolute;right:12px;top:50%;transform:translateY(-50%);
        background:none;border:none;cursor:pointer;color:var(--muted);
        padding:0;display:flex;align-items:center;
    }
    .pwd-toggle svg{width:17px;height:17px;}
    .settings-info-box{
        background:#f0f3ff;border-radius:10px;padding:12px 15px;
        margin-bottom:20px;display:flex;gap:10px;align-items:flex-start;
    }
    .settings-info-box svg{width:16px;height:16px;color:var(--navy);flex-shrink:0;margin-top:1px;}
    .settings-info-box p{margin:0;color:var(--navy);font-size:12.5px;line-height:1.5;}
    .modal-footer{
        padding:16px 28px;border-top:1px solid var(--line);
        display:flex;gap:10px;justify-content:flex-end;flex-shrink:0;background:#fff;
    }
    .btn-cancel-modal{
        background:#fff;border:1.5px solid var(--line);color:var(--navy);
        font-weight:700;padding:10px 22px;border-radius:10px;font-size:14px;cursor:pointer;
        font-family:inherit;transition:border-color .2s;
    }
    .btn-cancel-modal:hover{border-color:var(--navy);}
    .btn-save{
        background:var(--navy);color:#fff;border:none;
        font-weight:700;padding:10px 26px;border-radius:10px;font-size:14px;cursor:pointer;
        font-family:inherit;transition:background .2s;display:flex;align-items:center;gap:7px;
    }
    .btn-save:hover{background:var(--navy-deep);}
    .btn-save svg{width:16px;height:16px;}

    /* ===== SUCCESS TOAST ===== */
    .toast{
        position:fixed;bottom:28px;right:28px;z-index:2000;
        background:var(--navy);color:#fff;
        display:flex;align-items:center;gap:12px;
        padding:14px 20px;border-radius:14px;
        box-shadow:0 8px 30px rgba(19,23,107,.25);
        font-size:14px;font-weight:700;
        transform:translateY(80px);opacity:0;
        transition:transform .4s cubic-bezier(.16,1,.3,1),opacity .4s ease;
        pointer-events:none;
    }
    .toast.show{transform:translateY(0);opacity:1;pointer-events:auto;}
    .toast svg{width:20px;height:20px;flex-shrink:0;color:#4ade80;}
    .toast-close{
        background:none;border:none;color:#fff;opacity:.7;
        font-size:18px;cursor:pointer;padding:0 0 0 8px;line-height:1;
    }

    @media (max-width:1180px){
        .two-col{grid-template-columns:1fr;}
        .charts-grid{grid-template-columns:1fr;}
    }
    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:row;overflow-x:auto;position:static;margin:14px;border-radius:16px;}
        .identity-grid{grid-template-columns:1fr;}
        .courses-table-wrap{overflow-x:auto;}
    }
</style>
</head>
<body>

{{-- ===== SUCCESS TOAST ===== --}}
@if (session('success'))
<div class="toast" id="successToast">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    {{ session('success') }}
    <button class="toast-close" onclick="document.getElementById('successToast').classList.remove('show')">&times;</button>
</div>
@endif

<header class="topbar">
    <div class="brand">
        <span class="logo">
            <img src="{{ asset('images/PSU-Logo.png') }}" alt="PSU Logo">
        </span>
        <h1>UPSKILL</h1>
    </div>

    {{-- ✅ Connected — only to My Courses and Dashboard --}}
    <nav class="nav-pills">
        <a href="{{ route('faculty.courses') }}">Courses</a>
        <a href="{{ route('faculty.dashboard') }}">Dashboard</a>
    </nav>

    {{-- ⚠ Not connected — search does nothing yet --}}
    <div class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input type="text" name="q" placeholder="Search">
    </div>

    <div class="icon-cluster">
        {{-- ⚠ Not connected — no notifications page yet --}}
        <a href="#" class="icon-circle notification-btn" title="Notifications">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
            <span class="notification-badge" aria-hidden="true"></span>
        </a>
        {{-- This page IS the profile — icon just stays here --}}
        <a href="#" class="icon-circle" title="{{ $user->name ?? 'Profile' }}"
           @if($user->avatar_url ?? null)
               style="background-image:url('{{ $user->avatar_url }}');background-size:cover;background-position:center;overflow:hidden;"
           @endif>
            @unless($user->avatar_url ?? null)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
            @endunless
        </a>
    </div>
</header>

<div class="layout">

    {{-- Sidebar — Dashboard, My Courses & Create Courses connected; Profile is this page --}}
    <aside class="sidebar">
        {{-- ✅ Connected — goes to Faculty Dashboard only --}}
        <a href="{{ route('faculty.dashboard') }}" class="side-link">
            <span class="side-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
            </span>
            Dashboard
        </a>
        {{-- ✅ Connected — goes to Faculty › My Courses only --}}
        <a href="{{ route('faculty.courses') }}" class="side-link">
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

        {{-- This page — Profile is active --}}
        <a href="#" class="side-link active">
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
            <div>
                <h2>My Profile</h2>
                <p>Manage your Personal Information and Account Preferences</p>
            </div>
            <div class="page-actions">
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button class="btn-logout" type="submit">Logout</button>
                </form>
                {{-- ✅ Functioning — opens the Edit Profile drawer --}}
                <button class="btn-edit-profile" type="button" onclick="openEditModal('personal')">Edit Profile</button>
            </div>
        </div>

        {{-- ══════════════ ① IDENTITY CARD ══════════════ --}}
        <section class="profile-card">
            <div class="avatar-ring"
                 @if($user->avatar_url ?? null) style="background-image:url('{{ $user->avatar_url }}')" @endif>
                @unless($user->avatar_url ?? null)
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                @endunless
            </div>
            <div class="identity">
                <h3>{{ $user->name ?? 'Faculty Member' }}<span class="role-pill">{{ $user->role ?? 'Faculty' }}</span></h3>
                <div class="identity-grid">
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        {{ $user->phone ?? '—' }}
                    </span>
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg>
                        {{ $user->email ?? '—' }}
                    </span>
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        Joined on {{ $user->joined ?? '—' }}
                    </span>
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $user->location ?? '—' }}
                    </span>
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M6 9h12"/><path d="M6 13h8"/></svg>
                        {{ $user->user_code ?? '—' }}
                    </span>
                </div>
            </div>
        </section>

        {{-- ══════════════ ① ABOUT ME + SETTINGS ══════════════ --}}
        <div class="two-col">

            <section class="panel" style="margin-bottom:28px;">
                <div class="panel-head">
                    <span class="head-ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                        About me
                    </span>
                </div>
                <div class="panel-body">
                    @if($user->about ?? null)
                        <p class="about-intro">{{ $user->about }}</p>
                    @endif
                    <div class="about-row">
                        <span class="about-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            Date of Birth:
                        </span>
                        <span class="about-value">{{ $user->birth_date ?? '—' }}</span>
                    </div>
                    <div class="about-row">
                        <span class="about-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                            Gender:
                        </span>
                        <span class="about-value">{{ $user->gender ?? '—' }}</span>
                    </div>
                    <div class="about-row">
                        <span class="about-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10L12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/></svg>
                            Education:
                        </span>
                        <span class="about-value link-blue">{{ $user->education ?? '—' }}</span>
                    </div>
                    <div class="about-row">
                        <span class="about-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            Bio:
                        </span>
                        <span class="about-value" style="font-size:12px;color:var(--muted);line-height:1.6;">{{ $user->bio ?? '—' }}</span>
                    </div>
                </div>
            </section>

            <section class="panel" style="margin-bottom:28px;">
                <div class="panel-head">
                    <span class="head-ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.09a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.09a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        Settings
                    </span>
                </div>
                <div class="panel-body">
                    <div class="set-row">
                        <span class="set-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg>
                            Email:
                        </span>
                        <span class="set-value">{{ $user->email ?? '—' }}</span>
                        {{-- ✅ Functioning — opens the drawer on Account Settings --}}
                        <button class="btn-change" type="button" onclick="openEditModal('settings')">Change</button>
                    </div>
                    <div class="set-row">
                        <span class="set-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Password:
                        </span>
                        <span class="set-value">••••••••••••••</span>
                        {{-- ✅ Functioning — opens the drawer on Account Settings --}}
                        <button class="btn-change" type="button" onclick="openEditModal('settings')">Change</button>
                    </div>
                    <div class="set-row">
                        <span class="set-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            Language:
                        </span>
                        {{-- ✅ Functioning — opens the drawer where the choice is saved --}}
                        <select class="set-select" onmousedown="event.preventDefault(); openEditModal('settings');">
                            @foreach($languages ?? ['English'] as $lang)
                                <option @selected(($user->language ?? 'English') === $lang)>{{ $lang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="set-row">
                        <span class="set-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            Time Zone:
                        </span>
                        {{-- ✅ Functioning — opens the drawer where the choice is saved --}}
                        <select class="set-select" onmousedown="event.preventDefault(); openEditModal('settings');">
                            @foreach($timezones ?? [] as $tz)
                                <option @selected(($user->timezone ?? '') === $tz)>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

        </div>

        {{-- ══════════════ ② PERFORMANCE + ACTIVITY ══════════════ --}}
        @php
            $performance = collect($performance ?? []);
            $activity    = collect($activity ?? []);
            $perfMax     = max($performance->max('percent') ?? 0, 1);
            $actMax      = max($activity->max('hours') ?? 0, 1);
        @endphp
        <div class="charts-grid">

            <section class="panel" style="margin-bottom:0;">
                <div class="panel-head">
                    <span>Performance</span>
                    <span class="range">Last 6 Months</span>
                </div>
                <div class="panel-body">
                    @if($performance->isNotEmpty())
                        <div class="chart">
                            @foreach($performance as $month)
                                @php $isPeak = ($month['percent'] ?? 0) == $performance->max('percent'); @endphp
                                <div class="col">
                                    <div class="bar-wrap">
                                        <div class="bar {{ $isPeak ? 'gold' : '' }}" style="height: {{ round((($month['percent'] ?? 0) / $perfMax) * 100) }}%;">
                                            @if($isPeak)<span class="bar-value">{{ $month['percent'] }}%</span>@endif
                                        </div>
                                    </div>
                                    <span class="axis-label">{{ $month['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">No performance data yet.</div>
                    @endif
                </div>
            </section>

            <section class="panel" style="margin-bottom:0;">
                <div class="panel-head">
                    <span>Activity</span>
                    <span class="range">This Week</span>
                </div>
                <div class="panel-body">
                    @if($activity->isNotEmpty())
                        <div class="chart">
                            @foreach($activity as $day)
                                @php $isLow = ($day['hours'] ?? 0) == $activity->min('hours'); @endphp
                                <div class="col">
                                    <div class="bar-wrap">
                                        <div class="bar {{ $isLow ? 'gold' : '' }}" style="height: {{ round((($day['hours'] ?? 0) / $actMax) * 100) }}%;">
                                            @if($isLow)<span class="bar-value">{{ $day['hours'] }} hours</span>@endif
                                        </div>
                                    </div>
                                    <span class="axis-label">{{ $day['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">No activity data yet.</div>
                    @endif
                </div>
            </section>

        </div>

        {{-- ══════════════ ② COURSES TABLE ══════════════ --}}
        <section class="panel" style="margin-bottom:0;">
            <div class="panel-head">
                <span>Courses</span>
                {{-- ⚠ Not connected — filter does nothing yet --}}
                <button class="filter-chip" type="button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 3H2l8 9.5V19l4 2v-8.5L22 3z"/></svg>
                    Filter
                </button>
            </div>
            <div class="panel-body courses-table-wrap">
                @if(($profileCourses ?? collect())->isNotEmpty())
                    <table class="courses-table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Rating</th>
                                <th>Completion Rate</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($profileCourses as $course)
                                <tr>
                                    <td>
                                        <div class="tbl-course">
                                            <div class="tbl-thumb" @if($course->thumbnail_url ?? null) style="background-image:url('{{ $course->thumbnail_url }}')" @endif></div>
                                            <div>
                                                <h4>{{ $course->title }}</h4>
                                                <span class="tbl-sub"><span class="cat">{{ $course->category ?? '' }}</span> &nbsp;•&nbsp; {{ ($course->students ?? 0) > 0 ? $course->students . ' Students' : 'No Students enrolled yet' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($course->rating ?? null)
                                            <span class="rating">
                                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                {{ number_format($course->rating, 1) }}
                                            </span>
                                        @else
                                            <span class="tbl-sub">No ratings yet</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="progress-cell">
                                            <div class="progress-track">
                                                <div class="progress-fill" style="width: {{ min(100, max(0, $course->completion ?? 0)) }}%;"></div>
                                            </div>
                                            <span class="progress-pct">{{ $course->completion ?? 0 }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php $st = strtolower($course->status ?? 'draft'); @endphp
                                        <span class="status-pill {{ in_array($st, ['active', 'published']) ? 'active' : ($st === 'archived' ? 'archived' : 'draft') }}">
                                            {{ $course->status ?? 'Draft' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">No course statistics yet.</div>
                @endif
            </div>
        </section>

    </main>
</div>

{{-- ===== EDIT PROFILE MODAL (design from Student_Profile) ===== --}}
<div id="editModal" class="modal-overlay" onclick="handleOverlayClick(event)">
    <div class="modal-panel" role="dialog" aria-modal="true" aria-label="Edit Profile">

        {{-- Header --}}
        <div class="modal-header">
            <div class="modal-header-left">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <div>
                    <h3>Edit Profile</h3>
                    <p>Update your personal information</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeEditModal()" title="Close">&times;</button>
        </div>

        {{-- Tabs --}}
        <div class="modal-tabs">
            <button class="tab-btn active" data-tab="personal" onclick="switchTab('personal')">
                Personal Info
            </button>
            <button class="tab-btn" data-tab="about" onclick="switchTab('about')">
                About Me
            </button>
            <button class="tab-btn" data-tab="settings" onclick="switchTab('settings')">
                Account Settings
            </button>
        </div>

        {{-- ✅ Functioning — saves the whole profile via faculty.profile.update --}}
        <form id="editProfileForm"
              action="{{ route('faculty.profile.update') }}"
              method="POST">
            @csrf
            @method('PATCH')

            <div class="modal-body">

                @if ($errors->any())
                    <div class="form-errors">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- TAB: Personal Info --}}
                <div id="tab-personal" class="tab-content active">

                    {{-- Avatar Upload --}}
                    <div class="avatar-upload-area">
                        <div class="avatar-preview" id="avatarPreview"
                            @if($user->avatar_url ?? null)
                                style="background-image:url('{{ $user->avatar_url }}')"
                            @endif>
                            @unless($user->avatar_url ?? null)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                            @endunless
                        </div>
                        <div class="avatar-upload-text">
                            <p>JPG, PNG or GIF · Up to 10 MB<br>Auto-resized to 400 × 400 px</p>
                            <label class="btn-upload-avatar">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Upload Photo
                                {{-- No name="avatar" — JS resizes & stores in the hidden field below --}}
                                <input type="file" accept="image/*" hidden onchange="previewAvatar(this)">
                            </label>
                            {{-- Client-resized base64 image sent here instead of raw file --}}
                            <input type="hidden" id="avatarBase64" name="avatar_base64" value="">
                        </div>
                    </div>

                    <p class="form-section-label">Basic Information</p>

                    <div class="form-group">
                        <label for="edit_name">Full Name</label>
                        <input type="text" id="edit_name" name="name"
                            value="{{ old('name', $user->name ?? '') }}"
                            placeholder="Enter your full name">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_phone">Phone Number</label>
                            <input type="tel" id="edit_phone" name="phone"
                                value="{{ old('phone', $user->phone ?? '') }}"
                                placeholder="e.g. 09XXXXXXXXX">
                        </div>
                        <div class="form-group">
                            <label for="edit_location">Location</label>
                            <input type="text" id="edit_location" name="location"
                                value="{{ old('location', $user->location ?? '') }}"
                                placeholder="City, Province">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_role">Role / Title</label>
                        <input type="text" id="edit_role" name="role"
                            value="{{ old('role', $user->role ?? '') }}"
                            placeholder="e.g. Faculty, Professor">
                    </div>
                </div>

                {{-- TAB: About Me --}}
                <div id="tab-about" class="tab-content">

                    <div class="form-group">
                        <label for="edit_about">About Me</label>
                        <textarea id="edit_about" name="about" rows="3"
                            placeholder="Share a little about yourself…">{{ old('about', $user->about ?? '') }}</textarea>
                        <p class="hint">Brief description shown on your profile card.</p>
                    </div>

                    <hr class="form-divider">
                    <p class="form-section-label">Personal Details</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_dob">Date of Birth</label>
                            <input type="date" id="edit_dob" name="date_of_birth"
                                value="{{ old('date_of_birth', $user->birth_date_raw ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label for="edit_gender">Gender</label>
                            <select id="edit_gender" name="gender">
                                <option value="">Select…</option>
                                <option value="Male"   @selected(old('gender', $user->gender ?? '') === 'Male')>Male</option>
                                <option value="Female" @selected(old('gender', $user->gender ?? '') === 'Female')>Female</option>
                                <option value="Other"  @selected(old('gender', $user->gender ?? '') === 'Other')>Other</option>
                                <option value="Prefer not to say" @selected(old('gender', $user->gender ?? '') === 'Prefer not to say')>Prefer not to say</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_education">Education</label>
                        <input type="text" id="edit_education" name="education"
                            value="{{ old('education', $user->education ?? '') }}"
                            placeholder="e.g. BS Information Technology">
                    </div>

                    <div class="form-group">
                        <label for="edit_bio">Bio / Skills</label>
                        <textarea id="edit_bio" name="bio" rows="3"
                            placeholder="List your skills, expertise, or career highlights…">{{ old('bio', $user->bio ?? '') }}</textarea>
                    </div>
                </div>

                {{-- TAB: Account Settings --}}
                <div id="tab-settings" class="tab-content">

                    <div class="settings-info-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p>Leave the password fields empty if you don't want to change your password.</p>
                    </div>

                    <p class="form-section-label">Email Address</p>

                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email"
                            value="{{ old('email', $user->email ?? '') }}"
                            placeholder="you@example.com">
                        <p class="hint">This is used to log in and receive notifications.</p>
                    </div>

                    <hr class="form-divider">
                    <p class="form-section-label">Change Password</p>

                    <div class="form-group">
                        <label for="edit_curr_pwd">Current Password</label>
                        <div class="pwd-wrap">
                            <input type="password" id="edit_curr_pwd" name="current_password" placeholder="Enter current password">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('edit_curr_pwd', this)" title="Show/hide">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_new_pwd">New Password</label>
                            <div class="pwd-wrap">
                                <input type="password" id="edit_new_pwd" name="password" placeholder="Min. 8 characters">
                                <button type="button" class="pwd-toggle" onclick="togglePwd('edit_new_pwd', this)" title="Show/hide">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_confirm_pwd">Confirm Password</label>
                            <div class="pwd-wrap">
                                <input type="password" id="edit_confirm_pwd" name="password_confirmation" placeholder="Repeat new password">
                                <button type="button" class="pwd-toggle" onclick="togglePwd('edit_confirm_pwd', this)" title="Show/hide">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="form-divider">
                    <p class="form-section-label">Preferences</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_language">Language</label>
                            <select id="edit_language" name="language">
                                @foreach($languages ?? ['English'] as $lang)
                                    <option value="{{ $lang }}" @selected(old('language', $user->language ?? 'English') === $lang)>{{ $lang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_timezone">Time Zone</label>
                            <select id="edit_timezone" name="timezone">
                                @foreach($timezones ?? [] as $tz)
                                    <option value="{{ $tz }}" @selected(old('timezone', $user->timezone ?? '') === $tz)>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>{{-- /.modal-body --}}

            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn-cancel-modal" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Changes
                </button>
            </div>

        </form>{{-- /form --}}
    </div>{{-- /.modal-panel --}}
</div>{{-- /.modal-overlay --}}

<script>
/* ====== Edit Profile Modal ====== */
function openEditModal(tab) {
    document.getElementById('editModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    if (tab) switchTab(tab);
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
    document.body.style.overflow = '';
}
function handleOverlayClick(e) {
    if (e.target === document.getElementById('editModal')) closeEditModal();
}
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    const btn = document.querySelector(`.tab-btn[data-tab="${tab}"]`);
    const panel = document.getElementById(`tab-${tab}`);
    if (btn) btn.classList.add('active');
    if (panel) panel.classList.add('active');
}

/* Password show/hide */
function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    btn.innerHTML = isText
        ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`
        : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
}

/* Avatar — resize client-side then store in hidden field (bypasses PHP upload limits) */
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            // Resize to max 400 × 400 keeping aspect ratio
            const MAX = 400;
            let w = img.width, h = img.height;
            if (w > h) { if (w > MAX) { h = Math.round(h * MAX / w); w = MAX; } }
            else        { if (h > MAX) { w = Math.round(w * MAX / h); h = MAX; } }

            const canvas = document.createElement('canvas');
            canvas.width  = w;
            canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);

            // Compress to JPEG 85% — typically < 50 KB regardless of original size
            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

            // Live preview in modal
            const preview = document.getElementById('avatarPreview');
            preview.style.backgroundImage = `url('${dataUrl}')`;
            preview.innerHTML = '';

            // Put compressed base64 in hidden field so it's submitted with the form
            document.getElementById('avatarBase64').value = dataUrl;
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

/* Keyboard: Esc closes modal */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeEditModal();
});

/* Re-open the drawer automatically if the save failed validation */
@if ($errors->any())
openEditModal(@json($errors->has('current_password') || $errors->has('password') || $errors->has('email') ? 'settings' : 'personal'));
@endif

/* ====== Success Toast ====== */
(function () {
    const toast = document.getElementById('successToast');
    if (!toast) return;
    // Show on next frame so CSS transition fires
    requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
    // Auto-dismiss after 4 seconds
    setTimeout(() => toast.classList.remove('show'), 4000);
})();
</script>

</body>
</html>