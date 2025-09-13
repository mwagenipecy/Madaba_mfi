<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .custom-green { background: linear-gradient(135deg, #176836 0%, #0f4a25 100%); }
        .light-green-bg { background-color: #C5E4D4; }
        .curved-border { position: relative; overflow: hidden; }
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
        .right-curve { position: relative; overflow: hidden; }
        .right-curve::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -28%;
            width: 70%;
            height: 70%;
            background: linear-gradient(135deg, #176836 0%, #0f4a25 100%);
            border-radius: 50%;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.08));
            z-index: 0;
            pointer-events: none;
        }
        .right-curve > * { position: relative; z-index: 1; }
        @media (max-width: 768px) { .curved-border::after, .right-curve::before { display:none; } }
    </style>
</head>
<body class="h-screen overflow-hidden">
    <div class="flex h-full">
        <div class="custom-green w-full md:w-1/2 flex flex-col justify-center items-center text-white p-8 relative curved-border">
            <div class="relative z-10 text-center max-w-sm">
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full mb-4 overflow-hidden">
                        <img src="{{ asset('logo/image.png') }}" alt="Logo" class="w-16 h-16 object-contain" />
                    </div>
                </div>
                <h1 class="text-4xl font-semibold mb-4">Create your account</h1>
                <p class="text-sm opacity-90 mb-8">Request access to get started.<br/>After approval and payment you can proceed.</p>
            </div>
        </div>

        <div class="hidden md:flex w-1/2 flex-col justify-center items-center bg-white p-8 right-curve">
            <div class="w-full max-w-md">
                <h2 class="text-4xl font-semibold text-green-800 mb-2 text-center" style="color: #176836;">register</h2>
                <p class="text-gray-600 text-center mb-8">Enter details to request access</p>

                <form class="space-y-4" method="POST" action="{{ route('register.step1.store') }}">
                    @csrf
                    <div>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Full name....................." class="w-full px-6 py-4 rounded-full light-green-bg placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-600 transition duration-300" required autocomplete="name">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email..........................." class="w-full px-6 py-4 rounded-full light-green-bg placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-600 transition duration-300" required autocomplete="username">
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-700 pt-2">
                        <input id="terms" name="terms" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-green-700 focus:ring-green-600" required>
                        <label for="terms">I agree to the <a target="_blank" href="{{ route('terms.show') }}" class="underline">Terms of Service</a> and <a target="_blank" href="{{ route('policy.show') }}" class="underline">Privacy Policy</a></label>
                    </div>
                    <div class="p-3 rounded border border-green-200 bg-green-50 text-sm text-green-900">I request system access. Upon approval and payment, I can proceed to activate my account.</div>
                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 px-8 rounded-full text-white font-medium transition duration-300 hover:opacity-90 custom-green">Continue</button>
                    </div>
                </form>

                <p class="text-center mt-6 text-gray-600">Already registered? <a href="{{ route('login') }}" class="text-green-700 hover:underline font-medium" style="color:#176836;">log in</a></p>
            </div>
        </div>

        <div class="flex md:hidden w-full flex-col justify-center items-center bg-white p-8">
            <div class="w-full max-w-md">
                <div class="mb-6 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-green-100 rounded-full mb-2 overflow-hidden">
                        <img src="{{ asset('logo/image.png') }}" alt="Logo" class="w-12 h-12 object-contain" />
                    </div>
                </div>
                <h2 class="text-3xl font-semibold mb-2 text-center" style="color:#176836;">Register</h2>
                <p class="text-gray-600 text-center mb-6 text-sm">Request access to continue</p>
                <form class="space-y-3" method="POST" action="{{ route('register.step1.store') }}">
                    @csrf
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Full name....................." class="w-full px-5 py-3 rounded-full light-green-bg placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-600 transition duration-300 text-sm" required>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email..........................." class="w-full px-5 py-3 rounded-full light-green-bg placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-600 transition duration-300 text-sm" required>
                    <label class="flex items-center gap-2 text-xs text-gray-700"><input id="mterms" name="terms" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-green-700 focus:ring-green-600" required> I agree to the <a target="_blank" href="{{ route('terms.show') }}" class="underline">Terms</a> & <a target="_blank" href="{{ route('policy.show') }}" class="underline">Privacy</a></label>
                    <div class="p-2 rounded border border-green-200 bg-green-50 text-xs text-green-900">Request access; proceed after approval and payment.</div>
                    <button type="submit" class="w-full py-3 px-6 rounded-full text-white font-medium transition duration-300 hover:opacity-90 custom-green text-sm">Continue</button>
                </form>
                <p class="text-center mt-4 text-gray-600 text-sm">Already registered? <a href="{{ route('login') }}" class="text-green-700 hover:underline font-medium" style="color:#176836;">log in</a></p>
            </div>
        </div>
    </div>
</body>
</html>
