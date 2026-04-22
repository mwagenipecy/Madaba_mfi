<aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col shadow-sm h-screen fixed left-0 top-0 z-40 overflow-hidden">
    {{-- Logo Section --}}
    <div class="h-16 flex items-center justify-center border-b border-gray-200 bg-white px-4 flex-shrink-0">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logo/wibook.png') }}" class="w-10 h-10 object-contain" alt="logo">
            <span class="text-lg font-bold text-green-600">Financing</span>
        </div>
    </div>

    @php
        $isLoanOfficer = auth()->user() && strtolower(auth()->user()->role ?? '') === 'loan_officer';

        // Helper-style inline: build the entire nav structure as a single data tree.
        // Each section has a label + items. Items can be single links or dropdowns with children.
        $nav = [
            [
                'section' => 'General',
                'show'    => true,
                'items'   => [
                    ['type' => 'link', 'label' => 'Dashboard', 'route' => 'dashboard',
                     'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z M8 21v-4a2 2 0 012-2h4a2 2 0 012 2v4'],
                    ['type' => 'link', 'label' => 'Profile', 'route' => 'profile.show',
                     'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ],
            ],
            [
                'section' => 'Operations',
                'show'    => ! $isLoanOfficer,
                'items'   => [
                    ['type' => 'link', 'label' => 'Branch Management', 'route' => 'branches.index',
                     'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                    ['type' => 'link', 'label' => 'Payments', 'route' => 'payments.index',
                     'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                ],
            ],
            [
                'section' => 'Administration',
                'show'    => ! $isLoanOfficer,
                'items'   => [
                    ['type' => 'group', 'label' => 'Organizations',
                     'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                     'children' => [
                        ['label' => 'Organizations List', 'route' => 'super-admin.organizations.index'],
                        ['label' => 'Organization Profile', 'route' => 'organizations.profile'],
                     ]],
                    ['type' => 'group', 'label' => 'Organization Settings',
                     'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                     'children' => [
                        ['label' => 'Settings Dashboard', 'route' => 'organization-settings.index'],
                        ['label' => 'Organization Details', 'route' => 'organization-settings.details'],
                        ['label' => 'Manage Users', 'route' => 'organization-settings.users'],
                        ['label' => 'Mapped Account Balances', 'route' => 'organization-settings.mapped-account-balances'],
                     ]],
                    ['type' => 'group', 'label' => 'System Management',
                     'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                     'children' => [
                        ['label' => 'Users', 'route' => 'management.users'],
                        ['label' => 'System Logs', 'route' => 'management.system-logs'],
                     ]],
                ],
            ],
            [
                'section' => 'Accounting',
                'show'    => true,
                'items'   => [
                    ['type' => 'group', 'label' => 'Accounts',
                     'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',
                     'children' => [
                        ['label' => 'All Accounts', 'route' => 'accounts.index'],
                        ['label' => 'Main Accounts', 'route' => 'accounts.main-accounts'],
                        ['label' => 'Branch Accounts', 'route' => 'accounts.branch-accounts'],
                        ['label' => 'Real Accounts', 'route' => 'accounts.real-accounts'],
                        ['label' => 'GL Statement', 'route' => 'accounts.general-ledger'],
                        ['label' => 'Balance Sheet', 'route' => 'accounts.balance-sheet'],
                        ['label' => 'Mapped Accounts', 'route' => 'accounts.mapped'],
                     ]],
                    ['type' => 'link', 'label' => 'Cash & Mobile Wallet', 'route' => 'daily-till.index',
                     'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'],
                ],
            ],
            [
                'section' => 'Lending',
                'show'    => true,
                'items'   => [
                    ['type' => 'group', 'label' => 'Loan Products',
                     'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                     'children' => [
                        ['label' => 'All Products', 'route' => 'loan-products.index'],
                        ['label' => 'Create Product', 'route' => 'loan-products.create'],
                     ]],
                    ['type' => 'group', 'label' => 'Clients',
                     'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                     'children' => [
                        ['label' => 'All Clients', 'route' => 'clients.index'],
                        ['label' => 'Add Individual Client', 'route' => 'clients.create'],
                     ]],
                    ['type' => 'group', 'label' => 'Loan Operations',
                     'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                     'children' => [
                        ['label' => 'Dashboard', 'route' => 'loans.dashboard'],
                        ['label' => 'All Loans', 'route' => 'loans.index'],
                        ['label' => 'Create Loan', 'route' => 'loans.create'],
                        ['label' => 'Applications', 'route' => 'loans.applications'],
                        ['label' => 'Approvals', 'route' => 'loans.approvals'],
                        ['label' => 'Disbursements', 'route' => 'loans.disbursements'],
                        ['label' => 'Repayments', 'route' => 'loans.repayments'],
                        ['label' => 'Reports', 'route' => 'loans.reports'],
                        ['label' => 'Loan Charges', 'route' => 'loan-charges.index'],
                        ['label' => 'Arrears', 'route' => 'loan-charges.arrears'],
                     ]],
                ],
            ],
            [
                'section' => 'Workflow',
                'show'    => true,
                'items'   => [
                    ['type' => 'group', 'label' => 'Approvals',
                     'show' => ! $isLoanOfficer,
                     'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                     'children' => [
                        ['label' => 'Pending Approvals', 'route' => 'approvals.pending'],
                        ['label' => 'Loan Approvals', 'route' => 'approvals.loans'],
                        ['label' => 'Fund Transfers', 'route' => 'approvals.fund-transfers'],
                        ['label' => 'Account Recharges', 'route' => 'approvals.account-recharges'],
                        ['label' => 'Expense Requests', 'route' => 'approvals.expenses'],
                        ['label' => 'Approval History', 'route' => 'approvals.history'],
                     ]],
                    ['type' => 'group', 'label' => 'Expenses',
                     'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                     'children' => [
                        ['label' => 'Request Expense', 'route' => 'expenses.repayment'],
                        ['label' => 'My Requests', 'route' => 'expenses.requests'],
                        ['label' => 'Expense History', 'route' => 'expenses.history'],
                     ]],
                ],
            ],
            [
                'section' => 'Insights',
                'show'    => true,
                'items'   => [
                    ['type' => 'group', 'label' => 'Analytics',
                     'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                     'children' => [
                        ['label' => 'Analytics Dashboard', 'route' => 'reports.index'],
                        ['label' => 'Payment Analytics', 'route' => 'reports.weekly-payments'],
                        ['label' => 'Risk Analytics', 'route' => 'reports.arrears'],
                        ['label' => 'Portfolio Analytics', 'route' => 'reports.par'],
                        ['label' => 'Daily Loan Report', 'route' => 'reports.daily-loans'],
                        ['label' => 'Daily Repayment Report', 'route' => 'reports.daily-repayments'],
                        ['label' => 'Disbursement Analytics', 'route' => 'reports.loan-disbursements'],
                        ['label' => 'Collection Analytics', 'route' => 'reports.loan-collections'],
                        ['label' => 'Expense Analytics', 'route' => 'reports.expenses'],
                        ['label' => 'Client Analytics', 'route' => 'reports.customers'],
                        ['label' => 'Performance Analytics', 'route' => 'reports.repayments'],
                        ['label' => 'CRB Report', 'route' => 'reports.crb'],
                     ]],
                ],
            ],
        ];
    @endphp

    {{-- Scrollable Navigation --}}
    <div class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden sidebar-scroll" style="overscroll-behavior: contain;">
        <nav class="px-3 py-4 space-y-1">

            @foreach($nav as $sectionIndex => $section)
                @continue(! ($section['show'] ?? true))

                {{-- Section divider (skip top border for the first visible section) --}}
                <div class="pt-3 {{ $sectionIndex > 0 ? 'mt-2 border-t border-gray-100' : '' }}">
                    <p class="px-3 mb-2 text-[10px] font-semibold tracking-[0.12em] uppercase text-gray-400">
                        {{ $section['section'] }}
                    </p>

                    <div class="space-y-0.5">
                        @foreach($section['items'] as $item)
                            @continue(isset($item['show']) && ! $item['show'])

                            @if($item['type'] === 'link')
                                @php $active = request()->routeIs($item['route'].'*'); @endphp
                                <a href="{{ route($item['route']) }}"
                                   class="sidebar-item group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $active ? 'bg-green-50 text-green-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    <svg class="w-4 h-4 flex-shrink-0 {{ $active ? 'text-green-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                    </svg>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                    @if($active)
                                        <div class="ml-auto w-1.5 h-1.5 bg-green-600 rounded-full"></div>
                                    @endif
                                </a>

                            @elseif($item['type'] === 'group')
                                @php
                                    // Open the group automatically if any child is active
                                    $groupActive = collect($item['children'])->contains(fn($c) => request()->routeIs($c['route'].'*'));
                                @endphp
                                <div x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }" class="space-y-0.5">
                                    <button @click="open = !open"
                                            :class="open ? 'text-gray-900' : 'text-gray-600'"
                                            class="sidebar-item group flex items-center justify-between w-full px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-50 hover:text-gray-900">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-4 h-4 flex-shrink-0 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                            </svg>
                                            <span class="truncate">{{ $item['label'] }}</span>
                                        </div>
                                        <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>

                                    <div x-show="open" x-collapse
                                         class="ml-4 pl-3 border-l border-gray-100 space-y-0.5">
                                        @foreach($item['children'] as $child)
                                            @php $childActive = request()->routeIs($child['route'].'*'); @endphp
                                            <a href="{{ route($child['route']) }}"
                                               class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-all duration-200 {{ $childActive ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' }}">
                                                <span class="w-1 h-1 rounded-full {{ $childActive ? 'bg-green-600' : 'bg-gray-300' }}"></span>
                                                <span class="truncate">{{ $child['label'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach

        </nav>
    </div>

    {{-- User Section (pinned to bottom) --}}
    <div class="flex-shrink-0 border-t border-gray-200 p-3 bg-white">
        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors">
            <div class="w-9 h-9 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-semibold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name ?? 'User' }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>
    </div>
</aside>