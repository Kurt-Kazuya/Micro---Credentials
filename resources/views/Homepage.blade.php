@extends('layouts.app')

@section('title', 'UpSkill – The Official Platform for PSU Microcredentials')

@push('styles')
<style>
/* ════════════════════════════════════════
   HERO
════════════════════════════════════════ */
.hero {
    background: linear-gradient(170deg, var(--navy) 0%, var(--navy-dark) 55%, #0e2b8e 100%);
    padding: 5rem 0 4rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 70% 60% at 50% 0%, rgba(232,168,0,0.12) 0%, transparent 70%);
    pointer-events: none;
}
.hero__eyebrow {
    display: inline-block;
    background: rgba(232,168,0,0.15);
    color: var(--gold);
    border: 1px solid rgba(232,168,0,0.3);
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 0.35rem 1.1rem;
    margin-bottom: 1.4rem;
}
.hero__title {
    font-family: var(--font-display);
    font-weight: 900;
    font-size: clamp(3rem, 8vw, 5.5rem);
    color: var(--gold);
    letter-spacing: -0.01em;
    line-height: 1;
    margin-bottom: 0.6rem;
}
.hero__subtitle {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    color: rgba(255,255,255,0.85);
    font-weight: 500;
    margin-bottom: 2.5rem;
}
.hero__cta { font-size: 1rem; padding: 0.8rem 2.4rem; }

/* Stats row */
.hero__stats {
    display: flex;
    justify-content: center;
    gap: clamp(1.5rem, 5vw, 4rem);
    margin-top: 3rem;
    flex-wrap: wrap;
}
.hero__stat { text-align: center; }
.hero__stat-num {
    font-family: var(--font-display);
    font-size: 2rem;
    font-weight: 800;
    color: var(--white);
    line-height: 1;
}
.hero__stat-label {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.55);
    margin-top: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* ════════════════════════════════════════
   FEATURED COURSES
════════════════════════════════════════ */
.featured {
    background: #f4f6fb;
    padding: 5rem 0;
}
.featured__header {
    text-align: center;
    margin-bottom: 2.8rem;
}
.featured__header .section-title { color: var(--navy); }

.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.6rem;
}

/* Course Card */
.course-card {
    background: var(--card-bg);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    overflow: hidden;
    transition: transform var(--transition), box-shadow var(--transition);
    display: flex;
    flex-direction: column;
}
.course-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 36px rgba(10,31,110,0.15);
}
.course-card__thumb {
    display: block;
    width: 100%;
    height: 180px;
    background: var(--gold-bg);
    overflow: hidden;
}
.course-card__thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.course-card:hover .course-card__thumb img { transform: scale(1.04); }
.course-card__thumb-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--gold-bg) 0%, #d4940a 100%);
}
.course-card__body { padding: 1.25rem 1.3rem 1.4rem; flex: 1; display: flex; flex-direction: column; }
.course-card__tags { display: flex; gap: 0.45rem; flex-wrap: wrap; margin-bottom: 0.75rem; }
.course-card__title {
    font-family: var(--font-display);
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 0.55rem;
    line-height: 1.35;
}
.course-card__title a { color: inherit; transition: color var(--transition); }
.course-card__title a:hover { color: var(--gold); }
.course-card__desc {
    font-size: 0.83rem;
    color: var(--text-muted);
    line-height: 1.55;
    flex: 1;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.course-card__meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.78rem;
    color: var(--text-muted);
    flex-wrap: wrap;
    border-top: 1px solid #eef0f6;
    padding-top: 0.85rem;
}

.course-card__rating { color: var(--gold); margin-left: auto; font-size: 0.82rem; }

.featured__footer { text-align: center; margin-top: 2.8rem; }

/* ════════════════════════════════════════
   HOW IT WORKS
════════════════════════════════════════ */
.how-it-works { background: var(--navy); padding: 5.5rem 0; }
.how-it-works .section-title { color: var(--white); text-align: center; margin-bottom: 3rem; }

.steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
}
.step-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: var(--radius-lg);
    padding: 2.4rem 2rem;
    text-align: center;
    transition: background var(--transition), transform var(--transition);
}
.step-card:hover {
    background: rgba(255,255,255,0.09);
    transform: translateY(-4px);
}
.step-card__num {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-weight: 800;
    font-size: 1.6rem;
    color: var(--white);
    margin: 0 auto 1.4rem;
}
.step-card__title {
    font-family: var(--font-display);
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--white);
    margin-bottom: 0.65rem;
}
.step-card__desc {
    font-size: 0.86rem;
    color: rgba(255,255,255,0.6);
    line-height: 1.6;
}

/* ════════════════════════════════════════
   LATEST COURSES (gold bg)
════════════════════════════════════════ */
.latest {
    background: var(--gold-bg);
    padding: 5rem 0;
}
.latest__header { margin-bottom: 2.5rem; }
.latest__header .section-title { color: var(--navy-dark); }
.latest__header .section-sub {
    color: rgba(10,31,110,0.65);
    font-size: 0.9rem;
    margin-top: 0.25rem;
}

/* ════════════════════════════════════════
   CTA BANNER
════════════════════════════════════════ */
.cta-banner {
    background: linear-gradient(135deg, var(--gold-bg) 0%, #d4940a 100%);
    padding: 5rem 0;
    text-align: center;
}
.cta-banner__title {
    font-family: var(--font-display);
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    font-weight: 800;
    color: var(--navy-dark);
    margin-bottom: 0.5rem;
}
.cta-banner__sub {
    color: rgba(10,31,110,0.7);
    font-size: 1rem;
    margin-bottom: 2.2rem;
}

/* ════════════════════════════════════════
   ANNOUNCEMENTS
════════════════════════════════════════ */
.announcements {
    background: var(--white, #fff);
    padding: 5rem 0;
}
.announcements__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2.5rem;
}
.announcements__header .section-title { color: var(--navy); margin-bottom: 0; }
.announcements__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(232,168,0,0.12);
    color: var(--gold, #e8a800);
    border: 1px solid rgba(232,168,0,0.35);
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 0.3rem 0.9rem;
}
.announcements__badge::before {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--gold, #e8a800);
    animation: pulse-dot 1.6s ease-in-out infinite;
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.4; transform: scale(0.7); }
}

.announcements-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Individual announcement card */
.ann-card {
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
    background: #f9fafc;
    border: 1px solid #eaedf5;
    border-left: 4px solid var(--navy, #0a1f6e);
    border-radius: 0 var(--radius-lg, 12px) var(--radius-lg, 12px) 0;
    padding: 1.2rem 1.4rem;
    transition: box-shadow var(--transition, 0.2s), transform var(--transition, 0.2s);

    /* scroll-reveal: hidden by default */
    opacity: 0;
    transform: translateY(18px);
}
.ann-card.is-visible {
    opacity: 1;
    transform: translateY(0);
    transition: opacity 0.45s ease, transform 0.45s ease, box-shadow 0.2s, border-color 0.2s;
}
.ann-card:hover {
    box-shadow: 0 6px 24px rgba(10,31,110,0.1);
    transform: translateY(-2px);
}
.ann-card--urgent  { border-left-color: #d63b3b; }
.ann-card--event   { border-left-color: var(--gold, #e8a800); }
.ann-card--general { border-left-color: #2d9c6e; }

.ann-card__body { flex: 1; min-width: 0; }
.ann-card__meta {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
    margin-bottom: 0.35rem;
}
.ann-card__type {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    padding: 0.15rem 0.55rem;
    border-radius: 50px;
    background: rgba(10,31,110,0.08);
    color: var(--navy, #0a1f6e);
}
.ann-card--urgent  .ann-card__type { background: rgba(214,59,59,0.1);  color: #b02e2e; }
.ann-card--event   .ann-card__type { background: rgba(232,168,0,0.15); color: #9a6c00; }
.ann-card--general .ann-card__type { background: rgba(45,156,110,0.1); color: #1e7a53; }

.ann-card__date {
    font-size: 0.74rem;
    color: var(--text-muted, #8a90a0);
    margin-left: auto;
}
.ann-card__title {
    font-family: var(--font-display);
    font-size: 0.98rem;
    font-weight: 700;
    color: var(--navy, #0a1f6e);
    margin-bottom: 0.3rem;
}
.ann-card__desc {
    font-size: 0.83rem;
    color: var(--text-muted, #8a90a0);
    line-height: 1.55;
}
</style>
@endpush

@section('content')

{{-- ── HERO ── --}}
<section class="hero">
    <div class="container">
        <span class="hero__eyebrow">PSU Official Learning Platform</span>
        <h1 class="hero__title">UPSKILL</h1>
        <p class="hero__subtitle">The Official Platform for PSU Microcredentials</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="{{ url('/register') }}" class="btn btn-gold hero__cta">Get Started</a>
            <a href="#announcements" class="btn hero__cta" id="ann-btn"
               style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.35);backdrop-filter:blur(4px);"
               onclick="smoothScrollTo('announcements',event)">
               Announcements
            </a>
        </div>

        <div class="hero__stats">
            <div class="hero__stat">
                <div class="hero__stat-num" data-count="{{ $stats['courses'] ?? 0 }}">0</div>
                <div class="hero__stat-label">Courses</div>
            </div>
            <div class="hero__stat">
                <div class="hero__stat-num" data-count="{{ $stats['learners'] ?? 0 }}">0</div>
                <div class="hero__stat-label">Learners</div>
            </div>
            <div class="hero__stat">
                <div class="hero__stat-num" data-count="{{ $stats['certificates'] ?? 0 }}">0</div>
                <div class="hero__stat-label">Certificates Issued</div>
            </div>
            <div class="hero__stat">
                <div class="hero__stat-num" data-count="{{ $stats['badges'] ?? 0 }}">0</div>
                <div class="hero__stat-label">Badges Earned</div>
            </div>
        </div>
    </div>
</section>

{{-- ── FEATURED COURSES ── --}}
<section class="featured" id="featured">
    <div class="container">
        <div class="featured__header">
            <h2 class="section-title">Featured Courses</h2>
        </div>

        <div class="courses-grid">
            @php $featuredCourses = $featuredCourses ?? []; @endphp

            @foreach($featuredCourses as $course)
                @include('components.course-card', ['course' => $course])
            @endforeach
        </div>

        <div class="featured__footer">
            <a href="{{ url('/explore') }}" class="btn btn-gold">View all Courses</a>
        </div>
    </div>
</section>

{{-- ── HOW IT WORKS ── --}}
<section class="how-it-works">
    <div class="container">
        <h2 class="section-title">How UpSkill Works</h2>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-card__num">1</div>
                <h3 class="step-card__title">Enroll in a Course</h3>
                <p class="step-card__desc">Browse our catalog of competency-based IT courses and enroll for free.</p>
            </div>
            <div class="step-card">
                <div class="step-card__num">2</div>
                <h3 class="step-card__title">Complete Modules</h3>
                <p class="step-card__desc">Work through lessons, videos, readings and quizzes at your own pace.</p>
            </div>
            <div class="step-card">
                <div class="step-card__num">3</div>
                <h3 class="step-card__title">Earn Credentials</h3>
                <p class="step-card__desc">Stack digital badges for each module and earn your course certificate.</p>
            </div>
        </div>
    </div>
</section>

{{-- ── ANNOUNCEMENTS ── --}}
<section class="announcements" id="announcements">
    <div class="container">
        <div class="announcements__header">
            <h2 class="section-title">Announcements</h2>
        </div>

        @php $announcements = $announcements ?? []; @endphp

        <div class="announcements-list" id="announcements-list">
            @forelse($announcements as $ann)
                <div class="ann-card ann-card--{{ $ann['type'] }}">
                    <div class="ann-card__body">
                        <div class="ann-card__meta">
                            <span class="ann-card__type">{{ $ann['label'] }}</span>
                            <span class="ann-card__date">{{ $ann['date'] }}</span>
                        </div>
                        <div class="ann-card__title">{{ $ann['title'] }}</div>
                        <p class="ann-card__desc">{{ $ann['desc'] }}</p>
                    </div>
                </div>
            @empty
                <p style="color:var(--muted);padding:18px 4px;">No announcements yet — check back soon for new courses and site updates.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- ── LATEST COURSES ── --}}
<section class="latest" id="latest-courses">
    <div class="container">
        <div class="latest__header">
            <h2 class="section-title">Latest Courses</h2>
            <p class="section-sub">Freshly published by our PSU faculty</p>
        </div>

        <div class="courses-grid">
            @php $latestCourses = $latestCourses ?? []; @endphp

            @foreach($latestCourses as $course)
                @include('components.course-card', ['course' => $course])
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA BANNER ── --}}
<section class="cta-banner">
    <div class="container">
        <h2 class="cta-banner__title">Ready to UpSkill?</h2>
        <p class="cta-banner__sub">Join thousands of PSU students already building their PSU credentials</p>
        <a href="{{ url('/register') }}" class="btn btn-navy">Create Free Account</a>
    </div>
</section>

@push('scripts')
<script>
/* Smooth scroll to announcements */
function smoothScrollTo(id, e) {
    if (e) e.preventDefault();
    const target = document.getElementById(id);
    if (!target) return;
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/* Intercept the navbar "Announcement" link and smooth-scroll instead of jumping */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('a[href="#announcements"], a[href*="announcements"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            const target = document.getElementById('announcements');
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    document.querySelectorAll('a[href="#featured"], a[href*="microcredentials"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            const target = document.querySelector('.featured');
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});

/* Animated hero counters — count up to the real totals */
(function () {
    const nums = document.querySelectorAll('.hero__stat-num[data-count]');
    if (!nums.length) return;
    const animate = (el) => {
        const target = parseInt(el.dataset.count, 10) || 0;
        const dur = 1200;
        const start = performance.now();
        function tick(now) {
            const p = Math.min(1, (now - start) / dur);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(target * eased) + (p === 1 && target > 0 ? '+' : '');
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    };
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => { if (e.isIntersecting) { animate(e.target); io.unobserve(e.target); } });
    }, { threshold: 0.4 });
    nums.forEach((n) => io.observe(n));
})();

/* Scroll-reveal for announcement cards */
(function () {
    const cards = document.querySelectorAll('.ann-card');
    if (!cards.length) return;

    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                // stagger each card slightly
                setTimeout(() => {
                    entry.target.classList.add('is-visible');
                }, i * 90);
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    cards.forEach(card => io.observe(card));
})();
</script>
@endpush

@endsection