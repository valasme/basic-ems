<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<meta name="description" content="{{ $metaDescription ?? 'BasicEMS is a modern employee management system.' }}">
<meta name="keywords" content="EMS, employee, management, HR, system, BasicEMS, staff, payroll, attendance, scheduling">
<meta name="author" content="BasicEMS Team">
<meta name="robots" content="index, follow">
<meta name="theme-color" content="#000000">
<meta name="copyright" content="BasicEMS Team">
<meta name="language" content="en">
<meta name="distribution" content="global">
<meta name="rating" content="general">
<link rel="canonical" href="{{ url()->current() }}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $title ?? config('app.name', 'BasicEMS') }}">
<meta property="og:description" content="{{ $metaDescription ?? 'BasicEMS is a modern employee management system.' }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ asset('og.png') }}">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title ?? config('app.name', 'BasicEMS') }}">
<meta name="twitter:description" content="{{ $metaDescription ?? 'BasicEMS is a modern employee management system.' }}">
<meta name="twitter:image" content="{{ asset('og.png') }}">

<title>{{ $title ?? config('app.name', 'BasicEMS') }}</title>

<link rel="icon" href="/favicon.png" sizes="any">
<link rel="icon" href="/favicon.png" type="image/png">
<link rel="apple-touch-icon" href="/favicon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
