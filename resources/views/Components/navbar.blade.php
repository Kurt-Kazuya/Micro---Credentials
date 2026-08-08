<nav class="navbar" id="navbar">
    <div class="container navbar__inner">

        {{-- Brand (no icon) --}}
        <a href="{{ url('/') }}" class="navbar__brand">
            <span class="navbar__brand-name">UPSKILL</span>
        </a>

        {{-- Desktop Nav Links --}}
        <ul class="navbar__links" id="navLinks">
            <li><a href="{{ url('/') }}"                  class="navbar__link {{ request()->is('/') ? 'active' : '' }}">Home</a></li>
            <li><a href="{{ request()->is('/') ? '#latest-courses' : url('/#latest-courses') }}" class="navbar__link {{ request()->is('explore') ? 'active' : '' }}">Explore</a></li>
            <li><a href="{{ url('/announcements') }}"     class="navbar__link {{ request()->is('announcements') ? 'active' : '' }}">Announcement</a></li>
            <li><a href="{{ url('/microcredentials') }}"  class="navbar__link {{ request()->is('microcredentials') ? 'active' : '' }}">Microcredentials</a></li>
            <li><a href="{{ url('/students') }}"          class="navbar__link {{ request()->is('students') ? 'active' : '' }}">Students</a></li>
        </ul>

        {{-- Actions --}}
        <div class="navbar__actions" id="navActions">
            <a href="{{ url('/login') }}"    class="navbar__action-link">Login</a>
            <a href="{{ url('/register') }}" class="btn btn-gold navbar__enroll-btn">Enroll Now</a>
        </div>

        {{-- Hamburger (mobile) --}}
        <button class="navbar__hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

    </div>
</nav>

<style>
/* ── Navbar ── */
.navbar {
    background: var(--navy);
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 16px rgba(10,31,110,0.18);
}

.navbar__inner {
    display: flex;
    align-items: center;
    gap: 1.2rem;
    padding: 0.85rem 0;
}

/* Brand */
.navbar__brand { flex-shrink: 0; }
.navbar__brand-name {
    font-family: var(--font-display);
    font-weight: 900;
    font-size: 1.25rem;
    color: var(--white);
    letter-spacing: 0.04em;
}

/* Links */
.navbar__links {
    display: flex;
    list-style: none;
    gap: 0.15rem;
    margin-left: auto;
}
.navbar__link {
    color: rgba(255,255,255,0.82);
    font-size: 0.9rem;
    font-weight: 500;
    padding: 0.42rem 0.8rem;
    border-radius: var(--radius-sm);
    transition: color var(--transition), background var(--transition);
    white-space: nowrap;
}
.navbar__link:hover,
.navbar__link.active {
    color: var(--gold);
    background: rgba(255,255,255,0.07);
}

/* Actions */
.navbar__actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-shrink: 0;
}
.navbar__action-link {
    color: rgba(255,255,255,0.85);
    font-size: 0.9rem;
    font-weight: 500;
    transition: color var(--transition);
    white-space: nowrap;
}
.navbar__action-link:hover { color: var(--gold); }
.navbar__enroll-btn { font-size: 0.88rem; padding: 0.5rem 1.4rem; }

/* Hamburger */
.navbar__hamburger {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    margin-left: auto;
}
.navbar__hamburger span {
    display: block;
    width: 24px;
    height: 2px;
    background: var(--white);
    border-radius: 2px;
    transition: transform 0.25s, opacity 0.25s;
}
.navbar__hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.navbar__hamburger.open span:nth-child(2) { opacity: 0; }
.navbar__hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* ── Responsive ── */
@media (max-width: 960px) {
    .navbar__link { font-size: 0.82rem; padding: 0.4rem 0.6rem; }
}

@media (max-width: 768px) {
    .navbar__links       { display: none; }
    .navbar__actions     { display: none; }
    .navbar__hamburger   { display: flex; }

    .navbar__links.open {
        display: flex;
        flex-direction: column;
        position: absolute;
        top: 100%;
        left: 0; right: 0;
        background: var(--navy-dark);
        padding: 1rem 1.5rem 1.5rem;
        gap: 0.25rem;
        box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    }
    .navbar__actions.open {
        display: flex;
        position: absolute;
        top: calc(100% + 230px);
        left: 0; right: 0;
        background: var(--navy-dark);
        padding: 1rem 1.5rem;
        justify-content: center;
        gap: 1.5rem;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }
}
</style>

<script>
    const hamburger = document.getElementById('hamburger');
    const navLinks  = document.getElementById('navLinks');
    const navActions = document.getElementById('navActions');

    hamburger.addEventListener('click', () => {
        const isOpen = navLinks.classList.toggle('open');
        navActions.classList.toggle('open', isOpen);
        hamburger.classList.toggle('open', isOpen);
        hamburger.setAttribute('aria-expanded', isOpen);
    });
</script>