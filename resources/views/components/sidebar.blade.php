<aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col shadow-sm h-screen fixed left-0 top-0 z-40">
    <!-- Logo Section -->
    <div class="h-16 flex items-center justify-center border-b border-gray-200 bg-white px-4">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logo/wibook.png') }}" class="w-10 h-10 object-contain" alt="logo">
            <span class="text-lg font-bold text-green-600">Financing</span>
        </div>
    </div>
    
    <!-- Scrollable Navigation -->
    <div class="flex-1 overflow-hidden">
        <nav class="h-full overflow-y-auto px-3 py-4 space-y-1 sidebar-scroll">
            
            <!-- CORE NAVIGATION -->
            <div class="mb-6">
                <div class="px-3 py-2 mb-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Core Navigation</h3>
                </div>
                
                @php 
                $coreItems = [
                    [
                        'label' => 'Dashboard',
                        'route' => 'dashboard',
                        'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z M8 21v-4a2 2 0 012-2h4a2 2 0 012 2v4'
                    ],
                    [
                        'label' => 'Profile',
                        'route' => 'profile.show',
                        'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
                    ]
                ]; 
                @endphp
                
                @foreach($coreItems as $item)
                    @php $active = request()->routeIs($item['route'].'*'); @endphp
                    <a href="{{ route($item['route']) }}" 
                       class="sidebar-item group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'active bg-green-50 text-green-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200 {{ $active ? 'scale-110' : 'group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                        </svg>
                        <span class="truncate">{{ $item['label'] }}</span>
                        @if($active)
                            <div class="ml-auto w-2 h-2 bg-green-600 rounded-full animate-pulse"></div>
                        @endif
                    </a>
                @endforeach
            </div>
            
            <!-- BUSINESS OPERATIONS -->
            <div class="mb-6">
                <div class="px-3 py-2 mb-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Business Operations</h3>
                </div>
                
                @php 
                $businessItems = [
                    [
                        'label' => 'Branch Management',
                        'route' => 'branches.index',
                        'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
                    ],
                    [
                        'label' => 'Payments',
                        'route' => 'payments.index',
                        'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'
                    ]
                ]; 
                @endphp
                
                @foreach($businessItems as $item)
                    @php $active = request()->routeIs($item['route'].'*'); @endphp
                    <a href="{{ route($item['route']) }}" 
                       class="sidebar-item group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'active bg-green-50 text-green-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200 {{ $active ? 'scale-110' : 'group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                        </svg>
                        <span class="truncate">{{ $item['label'] }}</span>
                        @if($active)
                            <div class="ml-auto w-2 h-2 bg-green-600 rounded-full animate-pulse"></div>
                        @endif
                    </a>
                @endforeach
            </div>
            
            <!-- SYSTEM ADMINISTRATION -->
            <div class="mb-6">
                <div class="px-3 py-2 mb-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">System Administration</h3>
                </div>
                
                <!-- Administration Dropdown -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" class="sidebar-item group flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200 group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="truncate">Administration</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Dropdown Submenu -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-6 space-y-1">
                        @php 
                        $adminItems = [
                            [
                                'label' => 'Organizations List',
                                'route' => 'super-admin.organizations.index',
                                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
                            ],
                            [
                                'label' => 'Organization Profile',
                                'route' => 'organizations.profile',
                                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
                            ]
                        ]; 
                        @endphp
                        
                        @foreach($adminItems as $item)
                            @php $active = request()->routeIs($item['route'].'*'); @endphp
                            <a href="{{ route($item['route']) }}" 
                               class="sidebar-item group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'active bg-green-50 text-green-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 {{ $active ? 'scale-110' : 'group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if($active)
                                    <div class="ml-auto w-1.5 h-1.5 bg-green-600 rounded-full animate-pulse"></div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                
                <!-- Organization Settings Dropdown -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" class="sidebar-item group flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200 group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span class="truncate">Organization Settings</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Organization Settings Submenu -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-6 space-y-1">
                        @php 
                        $orgSettingsItems = [
                            [
                                'label' => 'Settings Dashboard',
                                'route' => 'organization-settings.index',
                                'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z'
                            ],
                            [
                                'label' => 'Organization Details',
                                'route' => 'organization-settings.details',
                                'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                            ],
                            [
                                'label' => 'Manage Users',
                                'route' => 'organization-settings.users',
                                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'
                            ]
                        ]; 
                        @endphp
                        
                        @foreach($orgSettingsItems as $item)
                            @php $active = request()->routeIs($item['route'].'*'); @endphp
                            <a href="{{ route($item['route']) }}" 
                               class="sidebar-item group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'active bg-green-50 text-green-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 {{ $active ? 'scale-110' : 'group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if($active)
                                    <div class="ml-auto w-1.5 h-1.5 bg-green-600 rounded-full animate-pulse"></div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                
                <!-- Management Dropdown -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" class="sidebar-item group flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200 group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span class="truncate">System Management</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Management Submenu -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-6 space-y-1">
                        @php 
                        $managementItems = [
                            [
                                'label' => 'Users',
                                'route' => 'management.users',
                                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'
                            ],
                            [
                                'label' => 'System Logs',
                                'route' => 'management.system-logs',
                                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
                            ]
                        ]; 
                        @endphp
                        
                        @foreach($managementItems as $item)
                            @php $active = request()->routeIs($item['route'].'*'); @endphp
                            <a href="{{ route($item['route']) }}" 
                               class="sidebar-item group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'active bg-green-50 text-green-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 {{ $active ? 'scale-110' : 'group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if($active)
                                    <div class="ml-auto w-1.5 h-1.5 bg-green-600 rounded-full animate-pulse"></div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- FINANCIAL MANAGEMENT -->
            <div class="mb-6">
                <div class="px-3 py-2 mb-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Financial Management</h3>
                </div>
                
                <!-- Accounts Dropdown -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" class="sidebar-item group flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200 group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span class="truncate">Accounts</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Accounts Submenu -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-6 space-y-1">
                        @php 
                        $accountsItems = [
                            [
                                'label' => 'All Accounts',
                                'route' => 'accounts.index',
                                'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4'
                            ],
                            [
                                'label' => 'Main Accounts',
                                'route' => 'accounts.main-accounts',
                                'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z M8 21v-4a2 2 0 012-2h4a2 2 0 012 2v4'
                            ],
                            [
                                'label' => 'Branch Accounts',
                                'route' => 'accounts.branch-accounts',
                                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
                            ],
                            [
                                'label' => 'Real Accounts',
                                'route' => 'accounts.real-accounts',
                                'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'
                            ],
                            [
                                'label' => 'GL Statement',
                                'route' => 'accounts.general-ledger',
                                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
                            ],
                            [
                                'label' => 'Balance Sheet',
                                'route' => 'accounts.balance-sheet',
                                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
                            ]
                        ]; 
                        @endphp
                        
                        @foreach($accountsItems as $item)
                            @php $active = request()->routeIs($item['route'].'*'); @endphp
                            <a href="{{ route($item['route']) }}" 
                               class="sidebar-item group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'active bg-green-50 text-green-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 {{ $active ? 'scale-110' : 'group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if($active)
                                    <div class="ml-auto w-1.5 h-1.5 bg-green-600 rounded-full animate-pulse"></div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- LOAN MANAGEMENT -->
            <div class="mb-6">
                <div class="px-3 py-2 mb-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Loan Management</h3>
                </div>
                
                <!-- Loan Products Menu -->
                @auth
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" class="sidebar-item group flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200 group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            <span class="truncate">Loan Products</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Loan Products Submenu -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-6 space-y-1">
                        @php 
                        $loanProductsItems = [
                            [
                                'label' => 'All Products',
                                'route' => 'loan-products.index',
                                'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'
                            ],
                            [
                                'label' => 'Create Product',
                                'route' => 'loan-products.create',
                                'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6'
                            ]
                        ]; 
                        @endphp
                        
                        @foreach($loanProductsItems as $item)
                            @php $active = request()->routeIs($item['route'].'*'); @endphp
                            <a href="{{ route($item['route']) }}" 
                               class="sidebar-item group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'active bg-green-50 text-green-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 {{ $active ? 'scale-110' : 'group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if($active)
                                    <div class="ml-auto w-1.5 h-1.5 bg-green-600 rounded-full animate-pulse"></div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                @endauth

                <!-- Client Management Menu -->
                @auth
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" class="sidebar-item group flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200 group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="truncate">Client Management</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    {{-- Client Management Submenu --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-6 space-y-1">
                        @php 
                        $clientManagementItems = [
                            [
                                'label' => 'All Clients',
                                'route' => 'clients.index',
                                'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'
                            ],
                            [
                                'label' => 'Add Individual Client',
                                'route' => 'clients.create',
                                'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6'
                            ]
                        ]; 
                        @endphp
                        
                        @foreach($clientManagementItems as $item)
                            @php $active = request()->routeIs($item['route'].'*'); @endphp
                            <a href="{{ route($item['route']) }}" 
                               class="sidebar-item group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'active bg-green-50 text-green-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 {{ $active ? 'scale-110' : 'group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if($active)
                                    <div class="ml-auto w-1.5 h-1.5 bg-green-600 rounded-full animate-pulse"></div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                @endauth
            </div>

                <!-- Loan Operations Menu -->
                @auth
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" class="sidebar-item group flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200 group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="truncate">Loan Operations</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Loan Operations Submenu -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-6 space-y-1">
                        @php 
                        $loanOperationsItems = [
                            [
                                'label' => 'Dashboard',
                                'route' => 'loans.dashboard',
                                'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z'
                            ],
                            [
                                'label' => 'All Loans',
                                'route' => 'loans.index',
                                'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'
                            ],
                            [
                                'label' => 'Create Loan',
                                'route' => 'loans.create',
                                'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6'
                            ],
                            [
                                'label' => 'Loan Applications',
                                'route' => 'loans.applications',
                                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
                            ],
                            [
                                'label' => 'Loan Approvals',
                                'route' => 'loans.approvals',
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                            ],
                            [
                                'label' => 'Loan Disbursements',
                                'route' => 'loans.disbursements',
                                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'
                            ],
                            [
                                'label' => 'Repayments',
                                'route' => 'loans.repayments',
                                'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'
                            ],
                            [
                                'label' => 'Loan Reports',
                                'route' => 'loans.reports',
                                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
                            ]
                        ]; 
                        @endphp
                        
                        @foreach($loanOperationsItems as $item)
                            @php $active = request()->routeIs($item['route'].'*'); @endphp
                            <a href="{{ route($item['route']) }}" 
                               class="sidebar-item group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'active bg-green-50 text-green-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 {{ $active ? 'scale-110' : 'group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if($active)
                                    <div class="ml-auto w-1.5 h-1.5 bg-green-600 rounded-full animate-pulse"></div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                @endauth
            </div>

            <!-- WORKFLOW & APPROVALS -->
            <div class="mb-6">
                <div class="px-3 py-2 mb-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Workflow & Approvals</h3>
                </div>
                
                <!-- Approvals Menu -->
                @auth
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" class="sidebar-item group flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200 group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="truncate">Approvals</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Approvals Submenu -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-6 space-y-1">
                        @php 
                        $approvalsItems = [
                            [
                                'label' => 'Pending Approvals',
                                'route' => 'approvals.pending',
                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
                            ],
                            [
                                'label' => 'Loan Approvals',
                                'route' => 'approvals.loans',
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                            ],
                            [
                                'label' => 'Fund Transfers',
                                'route' => 'approvals.fund-transfers',
                                'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'
                            ],
                            [
                                'label' => 'Account Recharges',
                                'route' => 'approvals.account-recharges',
                                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'
                            ],
                            [
                                'label' => 'Expense Requests',
                                'route' => 'approvals.expenses',
                                'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'
                            ],
                            [
                                'label' => 'Approval History',
                                'route' => 'approvals.history',
                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
                            ]
                        ]; 
                        @endphp
                        
                        @foreach($approvalsItems as $item)
                            @php $active = request()->routeIs($item['route'].'*'); @endphp
                            <a href="{{ route($item['route']) }}" 
                               class="sidebar-item group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'active bg-green-50 text-green-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 {{ $active ? 'scale-110' : 'group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if($active)
                                    <div class="ml-auto w-1.5 h-1.5 bg-green-600 rounded-full animate-pulse"></div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                @endauth
                
                <!-- Expenses Menu -->
                @auth
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" class="sidebar-item group flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200 group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="truncate">Expenses</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Expenses Submenu -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-6 space-y-1">
                        @php 
                        $expensesItems = [
                            [
                                'label' => 'Request Expense',
                                'route' => 'expenses.repayment',
                                'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6'
                            ],
                            [
                                'label' => 'My Requests',
                                'route' => 'expenses.requests',
                                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
                            ],
                            [
                                'label' => 'Expense History',
                                'route' => 'expenses.history',
                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
                            ]
                        ]; 
                        @endphp
                        
                        @foreach($expensesItems as $item)
                            @php $active = request()->routeIs($item['route'].'*'); @endphp
                            <a href="{{ route($item['route']) }}" 
                               class="sidebar-item group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'active bg-green-50 text-green-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 {{ $active ? 'scale-110' : 'group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if($active)
                                    <div class="ml-auto w-1.5 h-1.5 bg-green-600 rounded-full animate-pulse"></div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                @endauth
            </div>

            <!-- ANALYTICS & REPORTING -->
            <div class="mb-6">
                <div class="px-3 py-2 mb-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Analytics & Reporting</h3>
                </div>
                
                <!-- Analytics Menu -->
                @auth
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open" class="sidebar-item group flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200 group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="truncate">Analytics</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Analytics Submenu -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="ml-6 space-y-1">
                        @php 
                        $analyticsItems = [
                            [
                                'label' => 'Analytics Dashboard',
                                'route' => 'reports.index',
                                'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z'
                            ],
                            [
                                'label' => 'Payment Analytics',
                                'route' => 'reports.weekly-payments',
                                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
                            ],
                            [
                                'label' => 'Risk Analytics',
                                'route' => 'reports.arrears',
                                'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z'
                            ],
                            [
                                'label' => 'Portfolio Analytics',
                                'route' => 'reports.par',
                                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
                            ],
                            [
                                'label' => 'Disbursement Analytics',
                                'route' => 'reports.loan-disbursements',
                                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'
                            ],
                            [
                                'label' => 'Collection Analytics',
                                'route' => 'reports.loan-collections',
                                'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'
                            ],
                            [
                                'label' => 'Expense Analytics',
                                'route' => 'reports.expenses',
                                'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'
                            ],
                            [
                                'label' => 'Client Analytics',
                                'route' => 'reports.customers',
                                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'
                            ],
                                [
                                    'label' => 'Performance Analytics',
                                    'route' => 'reports.repayments',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                                ],
                                [
                                    'label' => 'CRB Report',
                                    'route' => 'reports.crb',
                                    'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
                                ]
                        ]; 
                        @endphp
                        
                        @foreach($analyticsItems as $item)
                            @php $active = request()->routeIs($item['route'].'*'); @endphp
                            <a href="{{ route($item['route']) }}" 
                               class="sidebar-item group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'active bg-green-50 text-green-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}">
                                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200 {{ $active ? 'scale-110' : 'group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if($active)
                                    <div class="ml-auto w-1.5 h-1.5 bg-green-600 rounded-full animate-pulse"></div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                @endauth
            </div>
        </nav>
    </div>
    
    <!-- User Section -->
    <div class="p-3 border-t border-gray-200 bg-white">
        <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-50">
            <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                <span class="text-xs font-medium text-white">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-900 truncate">{{ auth()->user()->name ?? 'User' }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>
    </div>
</aside>
