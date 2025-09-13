<div>
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        @if (!$registration_complete)
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <span class="text-2xl font-bold text-white">₦</span>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Register Your Organization</h2>
                <p class="text-gray-600 mt-2">Start your microfinance journey with MicroFin Pro</p>
            </div>

            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
                    <span class="{{ $current_step >= 1 ? 'text-green-600 font-medium' : '' }}">Organization Details</span>
                    <span class="{{ $current_step >= 2 ? 'text-green-600 font-medium' : '' }}">Business Information</span>
                    <span class="{{ $current_step >= 3 ? 'text-green-600 font-medium' : '' }}">Admin Account</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full transition-all duration-500" 
                         style="width: {{ ($current_step / $total_steps) * 100 }}%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>Step {{ $current_step }} of {{ $total_steps }}</span>
                    <span>{{ round(($current_step / $total_steps) * 100) }}% Complete</span>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <form wire:submit.prevent="{{ $current_step == $total_steps ? 'register' : 'nextStep' }}">
                    <div class="p-8">
                        @if ($current_step == 1)
                            <!-- Step 1: Organization Details -->
                            <div class="space-y-6">
                                <div class="text-center mb-6">
                                    <h3 class="text-xl font-semibold text-gray-900">Organization Details</h3>
                                    <p class="text-gray-600 text-sm mt-1">Tell us about your organization</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Organization Name <span class="text-red-500">*</span>
                                        </label>
                                        <input wire:model="organization_name" type="text" 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                               placeholder="Enter your organization name">
                                        @error('organization_name') 
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Authorized Capital (₦)
                                        </label>
                                        <input wire:model="authorized_capital" type="number" step="0.01" 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                               placeholder="1000000.00">
                                        @error('authorized_capital') 
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Incorporation Date
                                        </label>
                                        <input wire:model="incorporation_date" type="date" 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200">
                                        @error('incorporation_date') 
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                                        @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Organization Logo
                                        </label>
                                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-green-500 transition-colors duration-200">
                                            @if ($logo)
                                                <div class="mb-4">
                                                    <img src="{{ $logo->temporaryUrl() }}" class="h-20 w-20 mx-auto rounded-lg object-cover">
                                                </div>
                                            @endif
                                            <input wire:model="logo" type="file" accept="image/*" class="hidden" id="logo-upload">
                                            <label for="logo-upload" class="cursor-pointer">
                                                <div class="w-12 h-12 bg-gray-100 rounded-lg mx-auto mb-2 flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                                <p class="text-sm text-gray-600">Click to upload logo</p>
                                                <p class="text-xs text-gray-400">PNG, JPG up to 2MB</p>
                                            </label>
                                        </div>
                                        @error('logo') 
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                                        @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Description
                                        </label>
                                        <textarea wire:model="description" rows="4" 
                                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                                  placeholder="Tell us about your organization's mission and services"></textarea>
                                        @error('description') 
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($current_step == 3)
                            <!-- Step 3: Admin Account -->
                            <div class="space-y-6">
                                <div class="text-center mb-6">
                                    <h3 class="text-xl font-semibold text-gray-900">Create Admin Account</h3>
                                    <p class="text-gray-600 text-sm mt-1">Set up the primary administrator account</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            First Name <span class="text-red-500">*</span>
                                        </label>
                                        <input wire:model="first_name" type="text" 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                               placeholder="John">
                                        @error('first_name') 
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Last Name <span class="text-red-500">*</span>
                                        </label>
                                        <input wire:model="last_name" type="text" 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                               placeholder="Doe">
                                        @error('last_name') 
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Email Address <span class="text-red-500">*</span>
                                        </label>
                                        <input wire:model="user_email" type="email" 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                               placeholder="admin@example.com">
                                        @error('user_email') 
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Phone Number
                                        </label>
                                        <input wire:model="user_phone" type="tel" 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                               placeholder="+234 123 456 7890">
                                        @error('user_phone') 
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Password <span class="text-red-500">*</span>
                                        </label>
                                        <input wire:model="password" type="password" 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                               placeholder="••••••••">
                                        @error('password') 
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Confirm Password <span class="text-red-500">*</span>
                                        </label>
                                        <input wire:model="password_confirmation" type="password" 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                               placeholder="••••••••">
                                        @error('password_confirmation') 
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                                        @enderror
                                    </div>
                                </div>

                                <!-- Admin Permissions Info -->
                                <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                                    <h4 class="font-medium text-green-800 mb-2">Administrator Permissions</h4>
                                    <p class="text-sm text-green-700 mb-3">This account will have full administrative privileges including:</p>
                                    <div class="grid grid-cols-2 gap-2 text-sm text-green-600">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            User Management
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Client Management
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Loan Management
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Reports & Analytics
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            System Settings
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Savings Management
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Form Actions -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        @if ($current_step > 1)
                            <button type="button" wire:click="previousStep" 
                                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors duration-200 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Previous
                            </button>
                        @else
                            <div></div>
                        @endif

                        <button type="submit" 
                                class="px-8 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all duration-200 flex items-center gap-2 font-medium shadow-lg hover:shadow-xl transform hover:scale-105"
                                wire:loading.attr="disabled" 
                                wire:target="{{ $current_step == $total_steps ? 'register' : 'nextStep' }}">
                            
                            <span wire:loading.remove wire:target="{{ $current_step == $total_steps ? 'register' : 'nextStep' }}">
                                @if ($current_step == $total_steps)
                                    Register Organization
                                @else
                                    Continue
                                @endif
                            </span>
                            
                            <span wire:loading wire:target="{{ $current_step == $total_steps ? 'register' : 'nextStep' }}" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>

                            @if ($current_step < $total_steps)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="nextStep">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        @else
            <!-- Registration Success -->
            <div class="text-center">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-12">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Registration Successful!</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        Your organization has been registered successfully. We've sent a confirmation email with next steps.
                    </p>
                    
                    <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200 mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div class="text-left">
                                <p class="font-medium text-yellow-800">Pending Approval</p>
                                <p class="text-sm text-yellow-700 mt-1">Your organization is currently under review. You'll be notified once approved.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="font-medium text-gray-900">Organization ID</p>
                            <p class="text-gray-600">#ORG{{ str_pad($organization_id, 4, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="font-medium text-gray-900">Admin User ID</p>
                            <p class="text-gray-600">#USR{{ str_pad($user_id, 4, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="/login" class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all duration-200 font-medium">
                            Go to Login
                        </a>
                        <a href="/contact" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-xl shadow-lg z-50">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-xl shadow-lg z-50">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif
    </div>

    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.fixed.top-4.right-4');
            messages.forEach(message => {
                message.style.transition = 'opacity 0.5s ease-out';
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            });
        }, 5000);
    </script>
</div>

</div>
