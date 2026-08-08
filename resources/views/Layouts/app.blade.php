<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UpSkill – The Official Platform for PSU Microcredentials">
    <title>@yield('title', 'UpSkill – PSU Microcredentials')</title>

    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">


    <style>
        /* ── Reset & Base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:       #0a1f6e;
            --navy-dark:  #071550;
            --gold:       #e8a800;
            --gold-light: #f5c518;
            --gold-bg:    #f5c200;
            --white:      #ffffff;
            --text-muted: #5a6a8a;
            --card-bg:    #ffffff;
            --radius-lg:  18px;
            --radius-md:  12px;
            --radius-sm:  8px;
            --font-display: 'Poppins', sans-serif;
            --font-body:    'Inter', sans-serif;
            --shadow-card:  0 4px 24px rgba(10,31,110,0.10);
            --transition:   0.22s ease;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--font-body);
            background: var(--white);
            color: var(--navy);
            min-height: 100vh;
        }

        img { max-width: 100%; display: block; }
        a  { text-decoration: none; color: inherit; }

        .btn {
            display: inline-block;
            padding: 0.65rem 1.8rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            border: none;
            transition: transform var(--transition), box-shadow var(--transition), background var(--transition);
        }
        .btn:hover  { transform: translateY(-2px); }
        .btn:active { transform: translateY(0); }

        .btn-gold {
            background: var(--gold);
            color: var(--navy-dark);
        }
        .btn-gold:hover { background: var(--gold-light); box-shadow: 0 6px 18px rgba(232,168,0,0.4); }

        .btn-navy {
            background: var(--navy);
            color: var(--white);
        }
        .btn-navy:hover { background: var(--navy-dark); box-shadow: 0 6px 18px rgba(10,31,110,0.35); }

        .btn-outline-navy {
            background: transparent;
            color: var(--navy);
            border: 2px solid var(--navy);
        }
        .btn-outline-navy:hover { background: var(--navy); color: var(--white); }

        .container {
            width: 92%;
            max-width: 1160px;
            margin: 0 auto;
        }

        .section { padding: 5rem 0; }

        .section-title {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 0.35rem;
        }

        .tag {
            display: inline-block;
            padding: 0.25rem 0.85rem;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.03em;
        }
        .tag-teal   { background: #00bcd4; color: var(--white); }
        .tag-gold   { background: var(--gold); color: var(--navy-dark); }
    </style>

    @stack('styles')
</head>
<body>

    {{-- Navigation --}}
    @include('components.navbar')

    {{-- Page Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('components.footer')

    <script>
        // No page-fade transitions — navigation is instant. This also fixes
        // the "stuck on a white screen" issue when using the browser Back
        // button: a bfcache-restored page is always shown again.
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                document.body.style.opacity = '1';
                document.body.classList.remove('page-transition-leave');
            }
        });
    </script>


    {{-- ── Back to top (appears on long pages) ── --}}
    <button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
            onclick="window.scrollTo({top:0,behavior:'smooth'});">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="22" height="22"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
    </button>
    <style>
        #back-to-top-btn{position:fixed;right:26px;bottom:26px;z-index:2000;width:48px;height:48px;border-radius:50%;border:none;background:#13176b;color:#fff;display:none;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 10px 22px rgba(19,23,107,.35);transition:transform .15s ease,background .2s ease;}
        #back-to-top-btn:hover{background:#dba617;transform:translateY(-3px);}
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

    @stack('scripts')
</body>
</html>