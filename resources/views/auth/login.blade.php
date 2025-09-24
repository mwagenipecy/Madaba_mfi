<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Financing - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .custom-green {
            background: linear-gradient(135deg, #176836 0%, #0f4a25 100%);
        }
        .light-green-bg {
            background-color: #C5E4D4;
        }
        .curved-border {
            position: relative;
            overflow: hidden;
        }
        .curved-border::after {
            content: '';
            position: absolute;
            top: 52%;
            right: -38%;
            width: 56%;
            height: 140%;
            background: white;
            border-radius: 50%;
            transform: translateY(-50%) rotate(-13deg);
            z-index: 0;
            pointer-events: none;
        }
        .right-curve {
            position: relative;
            overflow: hidden;
        }
        .right-curve::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -28%;
            width: 78%;
            height: 78%;
            background: radial-gradient(circle at 60% 40%, rgba(23,104,54,0.25) 0%, rgba(23,104,54,0.18) 35%, rgba(23,104,54,0.1) 60%, rgba(23,104,54,0.0) 100%);
            border-radius: 50%;
            filter: none;
            z-index: 0;
            pointer-events: none;
        }
        .right-curve > * { position: relative; z-index: 1; }
        @media (max-width: 768px) {
            .curved-border::after {
                display: none;
            }
            .right-curve::before {
                display: none;
            }
        }
        @media (min-width: 1024px) {
            .right-curve::before {
                top: -22%;
                right: -20%;
                width: 68%;
                height: 68%;
            }
        }
    </style>
</head>
<body class="h-screen overflow-hidden">
    <div class="flex h-full">
        <!-- Left Side - Welcome Back Section -->
        <div class="custom-green w-full md:w-1/2 flex flex-col justify-center items-center text-white p-8 relative curved-border">
            <div class="relative z-10 text-center max-w-sm">
                <!-- Logo -->
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full mb-4 overflow-hidden">
                        <img src="{{ asset('logo/wibook.png') }}" alt="Logo" class="w-16 h-16 object-contain" />
                    </div>
                    <h2 class="text-2xl font-bold text-white">Financing</h2>
                </div>

                <!-- Welcome Text -->
                <h1 class="text-4xl font-semibold mb-4">Welcome Back!</h1>
                <p class="text-sm opacity-90 mb-8">Access your financial management dashboard<br>and continue your work</p>

                <!-- Features List -->
                <div class="text-left space-y-2 text-sm opacity-90">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Loan Management
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Financial Analytics
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Real-time Reporting
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="hidden md:flex w-1/2 flex-col justify-center items-center bg-white p-8 right-curve">
            <div class="w-full max-w-md">
                <!-- Welcome Header -->
                <h2 class="text-4xl font-semibold text-green-800 mb-2 text-center" style="color: #176836;">Welcome</h2>
                <p class="text-gray-600 text-center mb-8">Sign in to your account to continue</p>

                <!-- Login Form -->
                <form class="space-y-4" method="POST" action="{{ route('login') }}">
                    @csrf
                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input 
                            type="email" 
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            placeholder="Enter your email address"
                            autocomplete="username"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-300"
                            required
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                name="password"
                                id="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-300"
                                required
                            >
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg id="eye-open" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="eye-closed" class="h-5 w-5 text-gray-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="text-center">
                        <a href="{{ route('password.request') }}" class="text-sm text-gray-600 hover:text-green-700 transition duration-300">Forgot your password?</a>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                id="remember" 
                                name="remember" 
                                type="checkbox" 
                                class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                            >
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                Remember me
                            </label>
                        </div>
                    </div>

                    <!-- Login Button -->
                    <div class="pt-4">
                        <button 
                            type="submit" 
                            class="w-full py-3 px-4 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-300"
                        >
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Sign In
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mobile View - Login Form (shown on small screens) -->
        <div class="flex md:hidden w-full flex-col justify-center items-center bg-white p-8">
            <div class="w-full max-w-md">
                <!-- Logo for mobile -->
                <div class="mb-6 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-green-100 rounded-full mb-2 overflow-hidden">
                        <img src="{{ asset('logo/wibook.png') }}" alt="Financing logo" class="w-12 h-12 object-contain" />
                    </div>
                    <h3 class="text-lg font-medium text-green-800" style="color: #176836;">Financing</h3>
                </div>

                <!-- Welcome Header -->
                <h2 class="text-3xl font-semibold text-green-800 mb-2 text-center" style="color: #176836;">Welcome Back!</h2>
                <p class="text-gray-600 text-center mb-6 text-sm">Sign in to your account to continue</p>

                <!-- Login Form -->
                <form class="space-y-3" method="POST" action="{{ route('login') }}">
                    @csrf
                    <!-- Email Input -->
                    <div>
                        <label for="email_mobile" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input 
                            type="email" 
                            name="email"
                            id="email_mobile"
                            value="{{ old('email') }}"
                            placeholder="Enter your email address"
                            autocomplete="username"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-300 text-sm"
                            required
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password_mobile" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                name="password"
                                id="password_mobile"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-300 text-sm"
                                required
                            >
                            <button type="button" onclick="togglePasswordMobile()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg id="eye-open-mobile" class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="eye-closed-mobile" class="h-4 w-4 text-gray-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="text-center">
                        <a href="{{ route('password.request') }}" class="text-xs text-gray-600 hover:text-green-700 transition duration-300">Forgot your password?</a>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center">
                        <input 
                            id="remember_mobile" 
                            name="remember" 
                            type="checkbox" 
                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                        >
                        <label for="remember_mobile" class="ml-2 block text-xs text-gray-700">
                            Remember me
                        </label>
                    </div>

                    <!-- Login Button -->
                    <div class="pt-3">
                        <button 
                            type="submit" 
                            class="w-full py-3 px-4 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-300 text-sm"
                        >
                            <span class="flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Sign In
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality for desktop
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }

        // Password toggle functionality for mobile
        function togglePasswordMobile() {
            const passwordInput = document.getElementById('password_mobile');
            const eyeOpen = document.getElementById('eye-open-mobile');
            const eyeClosed = document.getElementById('eye-closed-mobile');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }

        // Auto-focus on email input
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email') || document.getElementById('email_mobile');
            if (emailInput) {
                emailInput.focus();
            }
        });

        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const email = form.querySelector('input[type="email"]').value;
                const password = form.querySelector('input[type="password"]').value;
                
                if (!email || !password) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                    return false;
                }
                
                if (!email.includes('@')) {
                    e.preventDefault();
                    alert('Please enter a valid email address.');
                    return false;
                }
            });
        });
    </script>
</body>
</html>