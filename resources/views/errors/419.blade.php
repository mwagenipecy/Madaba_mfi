<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Page Expired - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }
        .error-content {
            text-align: center;
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 500px;
            width: 90%;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #a8edea;
            margin-bottom: 1rem;
            line-height: 1;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn-home {
            background: #a8edea;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-block;
        }
        .btn-home:hover {
            background: #81e6d9;
            transform: translateY(-1px);
        }
        .btn-refresh {
            background: transparent;
            color: #a8edea;
            padding: 0.75rem 2rem;
            border: 2px solid #a8edea;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-block;
            margin-left: 1rem;
        }
        .btn-refresh:hover {
            background: #a8edea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <div class="error-code">419</div>
            <h1 class="error-title">Page Expired</h1>
            <p class="error-message">
                Your session has expired due to inactivity. 
                Please refresh the page and try again.
            </p>
            <div>
                <a href="{{ url('/') }}" class="btn-home">Go Home</a>
                <a href="javascript:location.reload()" class="btn-refresh">Refresh Page</a>
            </div>
        </div>
    </div>
</body>
</html>
