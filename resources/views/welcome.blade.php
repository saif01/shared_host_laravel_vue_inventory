<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('common.demo-icon')
    @php
        $appBase = rtrim(request()->getSchemeAndHttpHost() . request()->getBaseUrl(), '/');
    @endphp
    <meta name="api-base-url" content="{{ $appBase }}/api/v1">
    <title>Business</title>
    @vite('resources/sass/app.scss')
</head>
<body>
    <!-- HTML-based page loader (shows before Vue loads) -->
    <div id="html-page-loader" class="html-page-loader">
        <div class="html-page-loader-content">
            <div class="html-page-loader-spinner"></div>
            <p>Loading...</p>
        </div>
    </div>

    <div id="app"></div>

<!-- <p>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</p> -->

 @vite('resources/js/app.js')
</body>
</html>
