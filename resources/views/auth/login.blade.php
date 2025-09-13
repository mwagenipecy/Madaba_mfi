<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wibook finance - Login</title>
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
                        <img src="{{ asset('logo/image.png') }}" alt="Logo" class="w-16 h-16 object-contain" />
                    </div>
                </div>

                <!-- Welcome Text -->
                <h1 class="text-4xl font-semibold mb-4">Welcome Back!</h1>
                <p class="text-sm opacity-90 mb-8">To stay connected with us<br>please login with your personal info</p>

                <!-- Sign In Button -->
                <button class="w-full py-3 px-8 border-2 border-white rounded-full text-white font-medium hover:bg-white hover:text-green-800 transition duration-300 mb-8">
                    SIGN IN
                </button>

                <!-- Creator Links -->
                <div class="text-xs opacity-80">
                    <span>CREATOR </span>
                    <a href="#" class="underline hover:no-underline">HERE</a>
                    <span> | DIRECTOR </span>
                    <a href="#" class="underline hover:no-underline">HERE</a>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="hidden md:flex w-1/2 flex-col justify-center items-center bg-white p-8 right-curve">
            <div class="w-full max-w-md">
                <!-- Welcome Header -->
                <h2 class="text-4xl font-semibold text-green-800 mb-2 text-center" style="color: #176836;">welcome</h2>
                <p class="text-gray-600 text-center mb-8">Login in to your account to continue</p>

                <!-- Login Form -->
                <form class="space-y-4" method="POST" action="{{ route('login') }}">
                    @csrf
                    <!-- Email Input -->
                    <div>
                        <input 
                            type="email" 
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Email..........................."
                            autocomplete="username"
                            class="w-full px-6 py-4 rounded-full light-green-bg placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-600 transition duration-300"
                            required
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div>
                        <input 
                            type="password" 
                            name="password"
                            placeholder="Password..........................."
                            autocomplete="current-password"
                            class="w-full px-6 py-4 rounded-full light-green-bg placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-600 transition duration-300"
                            required
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="text-center">
                        <a href="{{ route('password.request') }}" class="text-sm text-gray-600 hover:text-green-700 transition duration-300">Forgot your password?</a>
                    </div>

                    <!-- Login Button -->
                    <div class="pt-4">
                        <button 
                            type="submit" 
                            class="w-full py-4 px-8 rounded-full text-white font-medium transition duration-300 hover:opacity-90 custom-green"
                        >
                            LOG IN
                        </button>
                    </div>
                </form>

                <!-- Sign Up Link -->
                <p class="text-center mt-6 text-gray-600">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="text-green-700 hover:underline font-medium" style="color: #176836;">sign up</a>
                </p>
            </div>
        </div>

        <!-- Mobile View - Login Form (shown on small screens) -->
        <div class="flex md:hidden w-full flex-col justify-center items-center bg-white p-8">
            <div class="w-full max-w-md">
                <!-- Logo for mobile -->
                <div class="mb-6 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-green-100 rounded-full mb-2 overflow-hidden">
                        <img src="{{ asset('logo/image.png') }}" alt="Wibook finance logo" class="w-12 h-12 object-contain" />
                    </div>
                    <h3 class="text-lg font-medium text-green-800" style="color: #176836;">Wibook finance</h3>
                </div>

                <!-- Welcome Header -->
                <h2 class="text-3xl font-semibold text-green-800 mb-2 text-center" style="color: #176836;">Welcome Back!</h2>
                <p class="text-gray-600 text-center mb-6 text-sm">Login to your account to continue</p>

                <!-- Login Form -->
                <form class="space-y-3" method="POST" action="{{ route('login') }}">
                    @csrf
                    <!-- Email Input -->
                    <div>
                        <input 
                            type="email" 
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Email..........................."
                            autocomplete="username"
                            class="w-full px-5 py-3 rounded-full light-green-bg placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-600 transition duration-300 text-sm"
                            required
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div>
                        <input 
                            type="password" 
                            name="password"
                            placeholder="Password..........................."
                            autocomplete="current-password"
                            class="w-full px-5 py-3 rounded-full light-green-bg placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-600 transition duration-300 text-sm"
                            required
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="text-center">
                        <a href="{{ route('password.request') }}" class="text-xs text-gray-600 hover:text-green-700 transition duration-300">Forgot your password?</a>
                    </div>

                    <!-- Login Button -->
                    <div class="pt-3">
                        <button 
                            type="submit" 
                            class="w-full py-3 px-6 rounded-full text-white font-medium transition duration-300 hover:opacity-90 custom-green text-sm"
                        >
                            LOG IN
                        </button>
                    </div>
                </form>

                <!-- Sign Up Link -->
                <p class="text-center mt-4 text-gray-600 text-sm">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="text-green-700 hover:underline font-medium" style="color: #176836;">sign up</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>