<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'TaskFlow' }}</title>
        <style>
            :root {
                color-scheme: light;
                --page-background: #f4efe6;
                --panel-background: #fffdf9;
                --panel-border: #d7c9b4;
                --text-primary: #182028;
                --text-muted: #5d6470;
                --accent: #14532d;
                --accent-contrast: #f8fafc;
                --danger: #b42318;
            }

            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                font-family: "Iowan Old Style", "Palatino Linotype", serif;
                background:
                    radial-gradient(circle at top, rgba(20, 83, 45, 0.08), transparent 35%),
                    linear-gradient(180deg, #f8f4ec 0%, var(--page-background) 100%);
                color: var(--text-primary);
            }
            .shell {
                width: min(100%, 960px);
                margin: 0 auto;
                padding: 3rem 1.5rem 4rem;
            }
            .panel {
                background: var(--panel-background);
                border: 1px solid var(--panel-border);
                border-radius: 24px;
                box-shadow: 0 20px 45px rgba(24, 32, 40, 0.08);
                padding: 2rem;
            }
            .eyebrow, .muted { color: var(--text-muted); }
            .eyebrow { letter-spacing: 0.08em; text-transform: uppercase; font-size: 0.8rem; }
            h1 { margin: 0.5rem 0 1rem; font-size: clamp(2rem, 5vw, 3.5rem); line-height: 1; }
            p { line-height: 1.6; }
            .stack { display: grid; gap: 1rem; }
            .field { display: grid; gap: 0.4rem; }
            label { font-size: 0.95rem; font-weight: 700; }
            input {
                width: 100%;
                padding: 0.85rem 1rem;
                border: 1px solid var(--panel-border);
                border-radius: 14px;
                font: inherit;
                background: #fff;
            }
            button, .button {
                display: inline-flex;
                justify-content: center;
                align-items: center;
                padding: 0.85rem 1.2rem;
                border: 0;
                border-radius: 999px;
                background: var(--accent);
                color: var(--accent-contrast);
                font: inherit;
                text-decoration: none;
                cursor: pointer;
            }
            .errors {
                margin: 0;
                padding: 1rem 1rem 1rem 1.25rem;
                border-radius: 16px;
                background: rgba(180, 35, 24, 0.08);
                color: var(--danger);
            }
            .hero {
                display: grid;
                gap: 1.5rem;
                align-items: start;
            }
            .app-shell {
                display: grid;
                gap: 1.5rem;
                align-items: start;
            }
            .sidebar {
                position: relative;
                overflow: hidden;
            }
            .sidebar::after {
                content: "";
                position: absolute;
                inset: auto -4rem -4rem auto;
                width: 10rem;
                height: 10rem;
                border-radius: 999px;
                background: rgba(20, 83, 45, 0.08);
            }
            .content-area {
                min-width: 0;
            }
            .nav-links {
                gap: 0.75rem;
            }
            .nav-link {
                display: inline-flex;
                align-items: center;
                min-height: 3rem;
                padding: 0.75rem 1rem;
                border-radius: 16px;
                text-decoration: none;
                color: var(--text-primary);
                background: rgba(20, 83, 45, 0.04);
                border: 1px solid transparent;
                font-weight: 700;
            }
            .nav-link.is-active {
                background: rgba(20, 83, 45, 0.12);
                border-color: rgba(20, 83, 45, 0.18);
            }
            .nav-link--muted {
                color: var(--text-muted);
                background: rgba(93, 100, 112, 0.06);
            }
            .profile-card {
                background: rgba(255, 255, 255, 0.72);
                backdrop-filter: blur(8px);
            }
            .profile-email {
                margin-bottom: 0;
            }
            .app-title {
                font-size: clamp(1.8rem, 4vw, 2.7rem);
            }
            .dashboard-grid {
                display: grid;
                gap: 1.5rem;
            }
            .section-title {
                margin: 0;
                font-size: 1.4rem;
            }

            @media (min-width: 768px) {
                .hero {
                    grid-template-columns: 1.1fr 0.9fr;
                }
                .app-shell {
                    grid-template-columns: minmax(260px, 300px) minmax(0, 1fr);
                }
                .sidebar {
                    position: sticky;
                    top: 1.5rem;
                }
                .dashboard-grid {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
            }
        </style>
    </head>
    <body>
        <main class="shell">
            @yield('content')
        </main>
    </body>
</html>
