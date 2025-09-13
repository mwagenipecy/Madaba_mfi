<div class="flex h-full">
    <div class="custom-green w-full md:w-1/2 flex flex-col justify-center items-center text-white p-8 relative curved-border">
        <div class="relative z-10 text-center max-w-sm">
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full mb-4 overflow-hidden">
                        <img src="{{ asset('logo/image.png') }}" alt="Logo" class="w-16 h-16 object-contain" />
                    </div>
                </div>
            <h1 class="text-4xl font-semibold mb-4">{{ $step === 'plan' ? 'Select your plan' : 'Create your account' }}</h1>
            <p class="text-sm opacity-90 mb-8">
                @if ($step === 'plan')
                    Choose a plan and enter your payment reference to submit for approval.
                @elseif ($step === 'done')
                    Your request was submitted. You can now log in after approval.
                @else
                    Request access to get started. After approval and payment you can proceed.
                @endif
            </p>
        </div>
    </div>

    <div class="hidden md:flex w-1/2 flex-col justify-center items-center bg-white p-8 right-curve">
        <div class="w-full max-w-md">
            @if (session('status'))
                <div class="mb-4 text-green-800 bg-green-50 border border-green-200 rounded p-3 text-sm">{{ session('status') }}</div>
            @endif
            @if ($step === 'details')
                <h2 class="text-4xl font-semibold text-green-800 mb-2 text-center" style="color:#176836;">register</h2>
                <p class="text-gray-600 text-center mb-8">Enter details to request access</p>
                <form wire:submit.prevent="submitDetails" class="space-y-4">
                    <div>
                        <input type="text" wire:model.defer="name" placeholder="Full name....................." class="w-full px-6 py-4 rounded-full light-green-bg placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-600 transition duration-300" required>
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <input type="email" wire:model.defer="email" placeholder="Email..........................." class="w-full px-6 py-4 rounded-full light-green-bg placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-600 transition duration-300" required>
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-3 text-sm text-gray-700 pt-2">
                        <input type="checkbox" wire:model.defer="terms" class="h-4 w-4 rounded border-gray-300 text-green-700 focus:ring-green-600" required>
                        <span>I agree to the <a target="_blank" href="{{ route('terms.show') }}" class="underline">Terms of Service</a> and <a target="_blank" href="{{ route('policy.show') }}" class="underline">Privacy Policy</a></span>
                    </label>
                    <div class="p-3 rounded border border-green-200 bg-green-50 text-sm text-green-900">I request system access. Upon approval and payment, I can proceed to activate my account.</div>
                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 px-8 rounded-full text-white font-medium transition duration-300 hover:opacity-90 custom-green">Continue</button>
                    </div>
                </form>
                <p class="text-center mt-6 text-gray-600">Already registered? <a href="{{ route('login') }}" class="text-green-700 hover:underline font-medium" style="color:#176836;">log in</a></p>
            @elseif ($step === 'plan')
                <h2 class="text-4xl font-semibold text-green-800 mb-2 text-center" style="color:#176836;">plans</h2>
                <p class="text-gray-600 text-center mb-8">Pick a plan to continue</p>
                <form wire:submit.prevent="submitPlan" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="border rounded-lg p-4 cursor-pointer hover:shadow-sm bg-white">
                            <input type="radio" wire:model="plan" value="basic" class="sr-only" required>
                            <div class="text-sm font-medium">Basic</div>
                            <div class="text-xs text-gray-500">Starter tools</div>
                            <div class="mt-2 text-green-700 font-semibold">$9/mo</div>
                        </label>
                        <label class="border rounded-lg p-4 cursor-pointer hover:shadow-sm bg-white">
                            <input type="radio" wire:model="plan" value="standard" class="sr-only">
                            <div class="text-sm font-medium">Standard</div>
                            <div class="text-xs text-gray-500">Most popular</div>
                            <div class="mt-2 text-green-700 font-semibold">$19/mo</div>
                        </label>
                        <label class="border rounded-lg p-4 cursor-pointer hover:shadow-sm bg-white">
                            <input type="radio" wire:model="plan" value="premium" class="sr-only">
                            <div class="text-sm font-medium">Premium</div>
                            <div class="text-xs text-gray-500">Full suite</div>
                            <div class="mt-2 text-green-700 font-semibold">$39/mo</div>
                        </label>
                    </div>
                    <div>
                        <input wire:model.defer="payment_reference" placeholder="Payment reference........................" class="w-full px-6 py-4 rounded-full light-green-bg placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-600" required>
                        @error('payment_reference')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 px-8 rounded-full text-white font-medium transition duration-300 hover:opacity-90 custom-green">Submit for approval</button>
                    </div>
                </form>
            @else
                <div class="text-center">
                    <h2 class="text-3xl font-semibold mb-4" style="color:#176836;">Request submitted</h2>
                    <p class="text-gray-600 mb-6">We will review your request and notify you once approved.</p>
                    <a href="{{ route('login') }}" class="inline-block py-3 px-6 rounded-full text-white custom-green">Go to login</a>
                </div>
            @endif
        </div>
    </div>
</div>


