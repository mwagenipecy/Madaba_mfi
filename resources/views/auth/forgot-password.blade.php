<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Financing - Forgot Password</title>
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
                <h1 class="text-4xl font-semibold mb-4">Reset Password</h1>
                <p class="text-sm opacity-90 mb-8">Don't worry, it happens to the best of us<br>Enter your email to get a reset link</p>

                <!-- Features List -->
                <div class="text-left space-y-2 text-sm opacity-90">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Secure Password Reset
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Email Verification
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Quick Recovery
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Forgot Password Form -->
        <div class="hidden md:flex w-1/2 flex-col justify-center items-center bg-white p-8 right-curve">
            <div class="w-full max-w-md">
                <!-- Welcome Header -->
                <h2 class="text-4xl font-semibold text-green-800 mb-2 text-center" style="color: #176836;">Forgot Password?</h2>
                <p class="text-gray-600 text-center mb-8">Enter your email address and we'll send you a reset link</p>

                <!-- Status Message -->
                @session('status')
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm text-green-800">{{ $value }}</span>
                        </div>
                    </div>
                @endsession

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-red-800 mb-1">Please correct the following errors:</h4>
                                <ul class="text-sm text-red-700 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Forgot Password Form -->
                <form class="space-y-4" method="POST" action="{{ route('password.email') }}">
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
                            autocomplete="email"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-300"
                            required
                        >
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button 
                            type="submit" 
                            class="w-full py-3 px-4 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-300"
                        >
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Send Reset Link
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Back to Login Link -->
                <div class="text-center mt-6">
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-green-700 transition duration-300 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Login
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile View - Forgot Password Form (shown on small screens) -->
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
                <h2 class="text-3xl font-semibold text-green-800 mb-2 text-center" style="color: #176836;">Forgot Password?</h2>
                <p class="text-gray-600 text-center mb-6 text-sm">Enter your email address and we'll send you a reset link</p>

                <!-- Status Message -->
                @session('status')
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-xs text-green-800">{{ $value }}</span>
                        </div>
                    </div>
                @endsession

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-4 h-4 text-red-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-xs font-medium text-red-800 mb-1">Please correct the following errors:</h4>
                                <ul class="text-xs text-red-700 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Forgot Password Form -->
                <form class="space-y-3" method="POST" action="{{ route('password.email') }}">
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
                            autocomplete="email"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-300 text-sm"
                            required
                        >
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <button 
                            type="submit" 
                            class="w-full py-3 px-4 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-300 text-sm"
                        >
                            <span class="flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Send Reset Link
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Back to Login Link -->
                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="text-xs text-gray-600 hover:text-green-700 transition duration-300 flex items-center justify-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
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
                
                if (!email) {
                    e.preventDefault();
                    alert('Please enter your email address.');
                    return false;
                }
                
                if (!email.includes('@')) {
                    e.preventDefault();
                    alert('Please enter a valid email address.');
                    return false;
                }
            });
        });

        // Auto-hide success/error messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const statusMessages = document.querySelectorAll('.bg-green-50, .bg-red-50');
            statusMessages.forEach(message => {
                setTimeout(() => {
                    message.style.transition = 'opacity 0.5s ease-out';
                    message.style.opacity = '0';
                    setTimeout(() => {
                        message.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>