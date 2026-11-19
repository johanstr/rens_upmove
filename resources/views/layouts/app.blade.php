<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover">

    <meta
        name="theme-color"
        content="#f7f8fa"
        id="theme-color">

    <title>@yield('title', 'UpMove')</title>

    <!-- ========================================= -->
    <!-- GLOBALE INSTELLINGEN -->
    <!-- Dark mode + werk/school instellingen. -->
    <!-- Bewust geen module: moet vóór het tekenen draaien. -->
    <!-- ========================================= -->

    <script src="{{ asset('js/app-settings.js') }}"></script>


    <!-- ========================================= -->
    <!-- ICONEN -->
    <!-- ========================================= -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css">


    <!-- ========================================= -->
    <!-- FONT (Montserrat, lokaal gebundeld door Vite) -->
    <!-- ========================================= -->

    @fonts


    <!-- ========================================= -->
    <!-- TAILWIND + PAGINASCRIPTS -->
    <!-- ========================================= -->

    @vite(array_merge(['resources/css/app.css'], $scripts ?? []))
</head>
<body @isset($bodyClass) class="{{ $bodyClass }}" @endisset>
    @yield('body')
</body>
</html>
