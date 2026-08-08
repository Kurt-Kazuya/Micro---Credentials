{{--
    resources/views/Faculty_Students.blade.php
    Students enrolled in the current faculty member's courses, with each
    student's per-course progress.
    Expects: $user (UserPresenter::faculty), $students (collection).
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enrolled Students | Upskill</title>
<style>
    :root{--navy:#13176b;--navy-deep:#0c0f4d;--gold:#dba617;--gold-dark:#c4930f;--cyan:#7fe9e3;--ink:#13176b;--muted:#6b7280;--line:#e5e7eb;--green:#15803d;--shadow:0 10px 25px rgba(19,23,107,.08);}
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:var(--ink);margin:0;background:#fff;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}
    .topbar{background:var(--navy);display:flex;align-items:center;justify-content:space-between;padding:14px 28px;gap:20px;}
    .brand{display:flex;align-items:center;gap:14px;color:#fff;white-space:nowrap;}
    .brand .logo{width:46px;height:46px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .brand .logo img{width:100%;height:100%;object-fit:contain;border-radius:50%;padding:3px;}
    .brand h1{font-size:24px;letter-spacing:1px;margin:0;font-weight:800;}
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;background-size:cover;background-position:center;}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}
    .main{padding:32px 36px 60px;max-width:1000px;margin:0 auto;}
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:24px;}
    .page-head h2{font-size:30px;margin:0 0 6px;color:var(--navy);}
    .page-head p{margin:0;color:var(--muted);font-size:15px;}
    .btn-outline{background:#fff;border:2px solid var(--navy);color:var(--navy);font-weight:800;padding:10px 22px;border-radius:12px;font-size:14px;transition:background .15s,color .15s;}
    .btn-outline:hover{background:var(--navy);color:#fff;}
    .student-card{border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);padding:20px 24px;margin-bottom:16px;background:#fff;}
    .student-head{display:flex;align-items:center;gap:16px;margin-bottom:14px;}
    .avatar{width:52px;height:52px;border-radius:50%;background:#eef1fb;display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--navy);font-size:20px;overflow:hidden;background-size:cover;background-position:center;flex-shrink:0;}
    .student-head h3{margin:0;font-size:17px;color:var(--navy);}
    .student-head .sid{font-size:12.5px;color:var(--muted);}
    .course-row{display:flex;align-items:center;gap:14px;padding:10px 0;border-top:1px solid var(--line);}
    .course-row:first-of-type{border-top:none;}
    .course-title{flex:1;font-weight:700;font-size:14px;color:var(--navy);min-width:0;}
    .track{flex:1;max-width:280px;height:7px;border-radius:999px;background:#e8ecf7;overflow:hidden;}
    .fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--navy),#3b41c8);}
    .fill.done{background:linear-gradient(90deg,var(--green),#34d399);}
    .pct{width:46px;text-align:right;font-size:12.5px;font-weight:800;color:var(--navy);}
    .done-pill{background:#e8f7ef;color:var(--green);font-size:11px;font-weight:800;padding:3px 10px;border-radius:999px;}
    .empty-state{color:var(--muted);font-size:14px;padding:20px 4px;text-align:center;}
</style>
</head>
<body>
<header class="topbar">
    <div class="brand">
        <span class="logo"><img src="{{ asset('images/PSU-Logo.png') }}" alt="PSU Logo"></span>
        <h1>UPSKILL</h1>
    </div>
    <div class="icon-cluster">
        <a href="{{ route('faculty.dashboard') }}" class="icon-circle" title="Dashboard">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
        </a>
        <a href="{{ route('faculty.profile') }}" class="icon-circle" title="{{ $user->name ?? 'Profile' }}"
           @if($user->avatar_url ?? null) style="background-image:url('{{ $user->avatar_url }}')" @endif>
            @unless($user->avatar_url ?? null)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
            @endunless
        </a>
    </div>
</header>

<main class="main">
    <div class="page-head">
        <div>
            <h2>Enrolled Students</h2>
            <p>Students taking your courses and their per-course progress.</p>
        </div>
        <a href="{{ route('faculty.courses') }}"><button class="btn-outline" type="button">My Courses</button></a>
    </div>

    @forelse ($students as $student)
        <div class="student-card">
            <div class="student-head">
                <div class="avatar" @if($student->avatar_url) style="background-image:url('{{ $student->avatar_url }}')" @endif>
                    @unless($student->avatar_url){{ strtoupper(substr($student->name ?? 'S', 0, 1)) }}@endunless
                </div>
                <div>
                    <h3>{{ $student->name }}</h3>
                    <div class="sid">{{ $student->student_id }}</div>
                </div>
            </div>
            @foreach ($student->courses as $course)
                <div class="course-row">
                    <span class="course-title">{{ $course->title }}</span>
                    <div class="track"><div class="fill {{ $course->completed ? 'done' : '' }}" style="width:{{ $course->percent }}%"></div></div>
                    <span class="pct">{{ $course->percent }}%</span>
                    @if($course->completed)<span class="done-pill">Done</span>@endif
                </div>
            @endforeach
        </div>
    @empty
        <div class="empty-state">No students are enrolled in your courses yet.</div>
    @endforelse
</main>

</body>
</html>
