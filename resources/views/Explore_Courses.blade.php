<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>All Courses | Upskill</title>
<style>
    :root{--navy:#13176b;--navy-deep:#0c0f4d;--gold:#f5a623;--teal:#2ec4b6;--bg:#f4f6fb;--ink:#2b2f4a;--muted:#8a8fa8;}
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Arial,sans-serif;}
    body{background:var(--bg);color:var(--ink);}
    .topbar{background:var(--navy);padding:14px 24px;display:flex;align-items:center;justify-content:space-between;}
    .topbar a{color:#fff;text-decoration:none;font-weight:700;font-size:14px;}
    .topbar .brand{color:var(--gold);font-size:20px;font-weight:800;}
    .wrap{max-width:1180px;margin:0 auto;padding:34px 24px 60px;}
    .page-title{font-size:26px;font-weight:800;color:var(--navy);margin-bottom:6px;}
    .page-sub{color:var(--muted);font-size:14px;margin-bottom:26px;}
    .courses-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:22px;}
    .course-card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 18px rgba(19,23,107,.06);
        display:flex;flex-direction:column;transition:transform .2s,box-shadow .2s;}
    .course-card:hover{transform:translateY(-3px);box-shadow:0 10px 26px rgba(19,23,107,.12);}
    .course-card__thumb{display:block;height:170px;background:#e3e6f0;}
    .course-card__thumb img{width:100%;height:100%;object-fit:cover;}
    .course-card__thumb-placeholder{width:100%;height:100%;background:linear-gradient(135deg,#e3e6f0,#cfd4ea);}
    .course-card__body{padding:18px;display:flex;flex-direction:column;gap:10px;flex:1;}
    .course-card__tags{display:flex;gap:8px;}
    .tag{padding:4px 10px;border-radius:999px;font-size:11px;font-weight:800;}
    .tag-teal{background:rgba(46,196,182,.14);color:#0f7a70;}
    .tag-gold{background:rgba(245,166,35,.16);color:#9c6503;}
    .course-card__title{font-size:16px;line-height:1.35;}
    .course-card__title a{color:var(--navy);text-decoration:none;}
    .course-card__desc{font-size:13px;color:var(--muted);line-height:1.5;flex:1;}
    .course-card__meta{display:flex;align-items:center;gap:12px;font-size:12px;color:var(--muted);font-weight:600;}
    .course-card__rating{color:var(--gold);letter-spacing:1px;}
    .empty{background:#fff;border-radius:16px;padding:60px 24px;text-align:center;color:var(--muted);font-weight:600;}
</style>
</head>
<body>
<div class="topbar">
    <span class="brand">UPSKILL</span>
    <a href="{{ url('/') }}">&larr; Back to Home</a>
</div>

<div class="wrap">
    <h1 class="page-title">All Courses</h1>
    <p class="page-sub">Every published micro-credential from PSU faculty &mdash; {{ count($courses) }} course{{ count($courses) === 1 ? '' : 's' }}</p>

    @if(count($courses))
        <div class="courses-grid">
            @foreach($courses as $course)
                @include('components.course-card', ['course' => $course])
            @endforeach
        </div>
    @else
        <div class="empty">No published courses yet. Check back soon!</div>
    @endif
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
