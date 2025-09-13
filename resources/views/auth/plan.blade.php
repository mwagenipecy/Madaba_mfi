<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Plan - Wibook finance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .custom-green { background: linear-gradient(135deg, #176836 0%, #0f4a25 100%); }
        .light-green-bg { background-color: #C5E4D4; }
        .curved-border { position: relative; overflow: hidden; }
        .curved-border::after { content:''; position:absolute; top:52%; right:-38%; width:56%; height:140%; background:#fff; border-radius:50%; transform:translateY(-50%) rotate(-13deg); z-index:0; pointer-events:none; }
        .right-curve { position:relative; overflow:hidden; }
        .right-curve::before { content:''; position:absolute; top:-30%; right:-28%; width:70%; height:70%; background:linear-gradient(135deg,#176836 0%, #0f4a25 100%); border-radius:50%; filter:drop-shadow(0 4px 8px rgba(0,0,0,0.08)); z-index:0; pointer-events:none; }
        .right-curve > * { position:relative; z-index:1; }
        @media (max-width:768px){ .curved-border::after, .right-curve::before{ display:none; } }
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
                <h1 class="text-4xl font-semibold mb-4">Select your plan</h1>
                <p class="text-sm opacity-90 mb-8">Choose a service plan and enter your payment reference to submit for approval.</p>
            </div>
        </div>

        <div class="hidden md:flex w-1/2 flex-col justify-center items-center bg-white p-8 right-curve">
            <div class="w-full max-w-xl">
                <h2 class="text-4xl font-semibold text-green-800 mb-2 text-center" style="color:#176836;">plans</h2>
                <p class="text-gray-600 text-center mb-8">Pick a plan to continue</p>

                <form method="POST" action="{{ route('register.plan.store') }}" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="border rounded-lg p-4 cursor-pointer hover:shadow-sm bg-white">
                            <input type="radio" name="plan" value="basic" class="sr-only" required>
                            <div class="text-sm font-medium">Basic</div>
                            <div class="text-xs text-gray-500">Starter tools</div>
                            <div class="mt-2 text-green-700 font-semibold">$9/mo</div>
                        </label>
                        <label class="border rounded-lg p-4 cursor-pointer hover:shadow-sm bg-white">
                            <input type="radio" name="plan" value="standard" class="sr-only">
                            <div class="text-sm font-medium">Standard</div>
                            <div class="text-xs text-gray-500">Most popular</div>
                            <div class="mt-2 text-green-700 font-semibold">$19/mo</div>
                        </label>
                        <label class="border rounded-lg p-4 cursor-pointer hover:shadow-sm bg-white">
                            <input type="radio" name="plan" value="premium" class="sr-only">
                            <div class="text-sm font-medium">Premium</div>
                            <div class="text-xs text-gray-500">Full suite</div>
                            <div class="mt-2 text-green-700 font-semibold">$39/mo</div>
                        </label>
                    </div>

                    <div>
                        <input id="payment_reference" name="payment_reference" class="w-full px-6 py-4 rounded-full light-green-bg placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-600" placeholder="Payment reference........................" required>
                        @error('payment_reference')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 px-8 rounded-full text-white font-medium transition duration-300 hover:opacity-90 custom-green">Submit for approval</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="flex md:hidden w-full flex-col justify-center items-center bg-white p-8">
            <div class="w-full max-w-md">
                <div class="mb-6 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-green-100 rounded-full mb-2 overflow-hidden">
                        <img src="{{ asset('logo/image.png') }}" alt="Logo" class="w-12 h-12 object-contain" />
                    </div>
                </div>
                <h2 class="text-3xl font-semibold mb-2 text-center" style="color:#176836;">Plans</h2>
                <p class="text-gray-600 text-center mb-6 text-sm">Choose plan and enter payment reference</p>
                <form method="POST" action="{{ route('register.plan.store') }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <label class="border rounded p-2 text-center bg-white"><input type="radio" name="plan" value="basic" class="sr-only" required>Basic</label>
                        <label class="border rounded p-2 text-center bg-white"><input type="radio" name="plan" value="standard" class="sr-only">Standard</label>
                        <label class="border rounded p-2 text-center bg-white"><input type="radio" name="plan" value="premium" class="sr-only">Premium</label>
                    </div>
                    <input id="m_payment_reference" name="payment_reference" class="w-full px-5 py-3 rounded-full light-green-bg placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-600 text-sm" placeholder="Payment reference........................" required>
                    <button type="submit" class="w-full py-3 px-6 rounded-full text-white font-medium transition duration-300 hover:opacity-90 custom-green text-sm">Submit</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>


