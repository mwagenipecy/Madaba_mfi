<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Service Unavailable - {{ config('app.name', 'Laravel') }}</title>
    
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
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
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
            color: #ff9a9e;
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
            background: #ff9a9e;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-block;
        }
        .btn-home:hover {
            background: #ff8a80;
            transform: translateY(-1px);
        }
        .btn-refresh {
            background: transparent;
            color: #ff9a9e;
            padding: 0.75rem 2rem;
            border: 2px solid #ff9a9e;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-block;
            margin-left: 1rem;
        }
        .btn-refresh:hover {
            background: #ff9a9e;
            color: white;
        }
        .maintenance-info {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
            color: #92400e;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <div class="error-code">503</div>
            <h1 class="error-title">Service Unavailable</h1>
            <p class="error-message">
                We're currently performing maintenance on our system. 
                Please check back in a few minutes.
            </p>
            <div class="maintenance-info">
                <strong>Maintenance Notice:</strong> We're working to improve your experience. 
                Estimated completion time: 15-30 minutes.
            </div>
            <div style="margin-top: 2rem;">
                <a href="{{ url('/') }}" class="btn-home">Go Home</a>
                <a href="javascript:location.reload()" class="btn-refresh">Try Again</a>
            </div>
        </div>
    </div>
</body>
</html>
