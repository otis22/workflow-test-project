<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'TaskFlow') }}</title>
        <style>
            :root {
                color-scheme: light;
                font-family: "Segoe UI", sans-serif;
            }

            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                background: linear-gradient(135deg, #f6f8fb 0%, #e8eef8 100%);
                color: #152033;
            }

            main {
                width: min(42rem, calc(100vw - 2rem));
                padding: 3rem;
                border-radius: 1.5rem;
                background: rgba(255, 255, 255, 0.94);
                box-shadow: 0 24px 80px rgba(21, 32, 51, 0.12);
            }

            h1 {
                margin: 0 0 1rem;
                font-size: clamp(2rem, 5vw, 3rem);
            }

            p {
                margin: 0;
                line-height: 1.6;
                color: #40506a;
            }
        </style>
    </head>
    <body>
        <main>
            <h1>TaskFlow</h1>
            <p>Laravel application bootstrap is running in Docker Compose.</p>
        </main>
    </body>
</html>
