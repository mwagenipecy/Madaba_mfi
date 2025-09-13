<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
    <style>
        .brand { color:#176836; }
        .brand-bg { background:#176836; }
        
        /* Sidebar enhancements */
        .sidebar-item {
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: #16a34a;
            transform: scaleY(0);
            transition: transform 0.2s ease-in-out;
        }
        
        .sidebar-item.active::before {
            transform: scaleY(1);
        }
        
        /* Smooth hover effects */
        .sidebar-item:hover {
            transform: translateX(2px);
        }
        
        /* Custom scrollbar for sidebar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 2px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }
        
        /* Main content area adjustment for fixed sidebar */
        .main-content {
            margin-left: 16rem; /* 256px = w-64 */
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Sidebar Component -->
    <x-sidebar />
    
    <!-- Main Content Area -->
    <div class="main-content min-h-screen flex flex-col">
        <!-- Navbar Component -->
        <x-navbar :header="$header ?? 'Dashboard'" />
        
        <!-- Page Content -->
        <main class="flex-1 p-4"> 
            {{ $slot }}
        </main>
    </div>
    
    @livewireScripts
</body>
</html>