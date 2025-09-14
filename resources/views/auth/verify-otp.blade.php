<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-gradient-to-br from-green-600 to-green-700 rounded-xl flex items-center justify-center mb-4">
                    <span class="text-2xl font-bold text-white">W</span>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Verify Your Identity</h2>
                <p class="mt-2 text-sm text-gray-600">
                    We've sent a 6-digit code to your email address
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ Auth::user()->email }}
                </p>
            </div>

            <!-- OTP Form -->
            <form class="mt-8 space-y-6" method="POST" action="{{ route('otp.verify') }}">
                @csrf
                
                <div>
                    <label for="otp_code" class="sr-only">OTP Code</label>
                    <div class="relative">
                        <input 
                            id="otp_code" 
                            name="otp_code" 
                            type="text" 
                            maxlength="6"
                            required 
                            class="appearance-none rounded-lg relative block w-full px-4 py-4 text-center text-2xl font-mono tracking-widest border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm @error('otp_code') border-red-500 @enderror"
                            placeholder="000000"
                            autocomplete="one-time-code"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                        />
                    </div>
                    @error('otp_code')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Timer and Resend -->
                <div class="text-center space-y-4">
                    <div id="timer" class="text-sm text-gray-500">
                        Code expires in <span id="countdown" class="font-medium text-green-600">10:00</span>
                    </div>
                    
                    <div class="flex justify-center space-x-4">
                        <button 
                            type="button" 
                            id="resendBtn" 
                            onclick="resendOTP()"
                            class="text-sm text-green-600 hover:text-green-500 font-medium disabled:text-gray-400 disabled:cursor-not-allowed"
                            disabled
                        >
                            Resend Code
                        </button>
                        
                        <button 
                            type="button" 
                            onclick="logout()"
                            class="text-sm text-gray-600 hover:text-gray-500 font-medium"
                        >
                            Use Different Account
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-green-500 group-hover:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </span>
                        Verify & Continue
                    </button>
                </div>

                <!-- Help Text -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Can't find the email?</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Check your spam/junk folder</li>
                                    <li>Make sure the email address is correct</li>
                                    <li>Wait a few minutes for the email to arrive</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-focus on OTP input
        document.getElementById('otp_code').focus();

        // Auto-format OTP input (numbers only)
        document.getElementById('otp_code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) {
                // Auto-submit when 6 digits are entered
                setTimeout(() => {
                    this.form.submit();
                }, 500);
            }
        });

        // Timer functionality
        let timeLeft = 600; // 10 minutes in seconds
        const timerElement = document.getElementById('countdown');
        const resendBtn = document.getElementById('resendBtn');

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                timerElement.textContent = 'Expired';
                resendBtn.disabled = false;
                resendBtn.textContent = 'Resend Code';
                clearInterval(timerInterval);
            } else {
                timeLeft--;
            }
        }

        const timerInterval = setInterval(updateTimer, 1000);
        updateTimer(); // Initial call

        // Resend OTP function
        function resendOTP() {
            if (resendBtn.disabled) return;
            
            resendBtn.disabled = true;
            resendBtn.textContent = 'Sending...';
            
            fetch('{{ route("otp.resend") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reset timer
                    timeLeft = 600;
                    updateTimer();
                    timerInterval = setInterval(updateTimer, 1000);
                    
                    // Show success message
                    showMessage('OTP code has been resent to your email.', 'success');
                } else {
                    showMessage('Failed to resend OTP. Please try again.', 'error');
                    resendBtn.disabled = false;
                    resendBtn.textContent = 'Resend Code';
                }
            })
            .catch(error => {
                showMessage('An error occurred. Please try again.', 'error');
                resendBtn.disabled = false;
                resendBtn.textContent = 'Resend Code';
            });
        }

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout and use a different account?')) {
                window.location.href = '{{ route("otp.logout") }}';
            }
        }

        // Show message function
        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            messageDiv.textContent = message;
            document.body.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }

        // Handle form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const otpCode = document.getElementById('otp_code').value;
            if (otpCode.length !== 6) {
                e.preventDefault();
                showMessage('Please enter a valid 6-digit OTP code.', 'error');
            }
        });
    </script>
</x-guest-layout>
