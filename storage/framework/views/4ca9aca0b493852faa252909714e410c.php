<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UpSkill – The Official Platform for PSU Microcredentials">
    <title><?php echo $__env->yieldContent('title', 'UpSkill – PSU Microcredentials'); ?></title>

    
    <link rel="icon" type="image/png" href="<?php echo e(asset('Images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('Images/PSU-Logo.png')); ?>">

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
            opacity: 0;
            transition: opacity 240ms ease, transform 240ms ease;
            transform: translateY(10px);
        }

        body.page-transition-ready {
            opacity: 1;
            transform: translateY(0);
        }

        body.page-transition-leave {
            opacity: 0;
            transform: translateY(10px);
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

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>

    
    <?php echo $__env->make('components.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    
    <?php echo $__env->make('components.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.body.classList.add('page-transition-ready');

            document.querySelectorAll('a[href]').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    var href = link.getAttribute('href');
                    if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) {
                        return;
                    }
                    if (link.target === '_blank' || link.hasAttribute('download') || link.dataset.noTransition !== undefined) {
                        return;
                    }
                    try {
                        var url = new URL(href, location.href);
                    } catch (e) {
                        return;
                    }
                    if (location.origin !== url.origin) {
                        return;
                    }
                    event.preventDefault();
                    document.body.classList.add('page-transition-leave');
                    setTimeout(function () {
                        window.location.href = href;
                    }, 180);
                });
            });
        });
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\Users\Admin\Herd\Micro-credentials\resources\views/layouts/app.blade.php ENDPATH**/ ?>