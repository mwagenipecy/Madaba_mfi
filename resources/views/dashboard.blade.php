<x-app-shell title="Dashboard" header="Dashboard">
    <div class="space-y-6">
        <!-- Critical Alerts Section -->
        @if(count($criticalAlerts) > 0)
        <div class="bg-white rounded-lg border border-red-200 p-4">
            <h3 class="text-lg font-semibold text-red-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                Critical Alerts
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($criticalAlerts as $alert)
                <div class="p-3 rounded-lg border-l-4 {{ $alert['type'] === 'danger' ? 'border-red-500 bg-red-50' : 'border-yellow-500 bg-yellow-50' }}">
                    <h4 class="font-medium {{ $alert['type'] === 'danger' ? 'text-red-800' : 'text-yellow-800' }}">
                        {{ $alert['title'] }}
                    </h4>
                    <p class="text-sm {{ $alert['type'] === 'danger' ? 'text-red-600' : 'text-yellow-600' }} mt-1">
                        {{ $alert['message'] }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('repayments.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    Process Repayment
                </a>
                
                <a href="{{ route('loans.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    New Loan Application
                </a>
                
                <a href="{{ route('clients.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    Add New Client
                </a>
                
                <a href="{{ route('approvals.loans') }}" 
                   class="inline-flex items-center px-4 py-2 bg-orange-600 text-white font-medium rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Loan Approvals
                </a>
            </div>
        </div>

        <!-- Key Performance Indicators -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Total Portfolio</p>
                        <p class="text-2xl font-bold">{{ number_format($stats['total_portfolio'], 2) }}</p>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Active Loans</p>
                        <p class="text-2xl font-bold">{{ $stats['active_loans'] }}</p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Total Clients</p>
                        <p class="text-2xl font-bold">{{ $stats['total_clients'] }}</p>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Overdue Loans</p>
                        <p class="text-2xl font-bold">{{ $stats['overdue_loans'] }}</p>
                    </div>
                    <div class="bg-orange-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        @include('partials.dashboard-charts')
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Force chart text visibility -->
    <style>
        #monthlyPerformanceChart, #loanStatusChart {
            color: #000000 !important;
        }
        
        .chart-container {
            background: white !important;
            color: #000000 !important;
        }
        
        .chart-container * {
            color: #000000 !important;
        }
    </style>
    
    <script>
        // Theme detection function
        function isDarkMode() {
            // Check for dark class on html element first
            if (document.documentElement.classList.contains('dark')) {
                return true;
            }
            // Check for dark class on body element
            if (document.body.classList.contains('dark')) {
                return true;
            }
            // Check for data-theme attribute
            if (document.documentElement.getAttribute('data-theme') === 'dark') {
                return true;
            }
            // Fallback to system preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                return true;
            }
            // Default to light mode if no dark indicators found
            return false;
        }

        // Manual override for testing - set to true to force light mode colors
        const FORCE_LIGHT_MODE = true; // Temporarily set to true to test light mode visibility
        
        // Get theme-aware colors
        function getThemeColors() {
            const isDark = isDarkMode() && !FORCE_LIGHT_MODE;
            
            // Force visible colors for light mode if theme detection might be failing
            const lightModeColors = {
                text: '#000000',           // Pure black for maximum contrast
                textSecondary: '#000000',  // Pure black for maximum contrast  
                textMuted: '#000000',      // Pure black for maximum contrast
                axisLabels: '#000000',     // Pure black for maximum contrast
            };
            
            const darkModeColors = {
                text: 'rgba(255, 255, 255, 0.9)',
                textSecondary: 'rgba(255, 255, 255, 0.8)',
                textMuted: 'rgba(255, 255, 255, 0.7)',
                axisLabels: 'rgba(255, 255, 255, 0.8)',
            };
            
            const baseColors = isDark ? darkModeColors : lightModeColors;
            
            return {
                // Text colors - ensuring proper contrast
                text: baseColors.text,
                textSecondary: baseColors.textSecondary,
                textMuted: baseColors.textMuted,
                axisLabels: baseColors.axisLabels,
                
                // Grid colors
                gridColor: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
                borderColor: isDark ? 'rgba(255, 255, 255, 0.2)' : 'rgba(0, 0, 0, 0.2)',
                
                // Chart colors (maintaining brand colors but with theme-aware backgrounds)
                disbursements: {
                    border: 'rgb(34, 197, 94)',      // Green
                    background: isDark ? 'rgba(34, 197, 94, 0.2)' : 'rgba(34, 197, 94, 0.1)'
                },
                collections: {
                    border: 'rgb(59, 130, 246)',     // Blue
                    background: isDark ? 'rgba(59, 130, 246, 0.2)' : 'rgba(59, 130, 246, 0.1)'
                },
                
                // Status colors (maintaining semantic meaning)
                statusColors: [
                    'rgb(34, 197, 94)',   // Green for active
                    'rgb(239, 68, 68)',   // Red for overdue
                    'rgb(59, 130, 246)',  // Blue for pending
                    'rgb(245, 158, 11)',  // Yellow for completed
                    'rgb(139, 92, 246)'   // Purple for defaulted
                ]
            };
        }

        // Initialize charts with theme-aware configuration
        function initializeCharts() {
            const colors = getThemeColors();
            
            // Monthly Performance Chart
            const monthlyCtx = document.getElementById('monthlyPerformanceChart').getContext('2d');
            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: @json($monthlyData['months']),
                    datasets: [{
                        label: 'Disbursements',
                        data: @json($monthlyData['disbursements']),
                        borderColor: colors.disbursements.border,
                        backgroundColor: colors.disbursements.background,
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Collections',
                        data: @json($monthlyData['collections']),
                        borderColor: colors.collections.border,
                        backgroundColor: colors.collections.background,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 20,
                            left: 10,
                            right: 10
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Month',
                                color: '#000000', // Force black color
                                font: {
                                    size: 14,
                                    weight: 'bold',
                                    family: 'Arial, sans-serif'
                                },
                                padding: 20
                            },
                            grid: {
                                color: '#e5e7eb',
                                drawBorder: true,
                                borderColor: '#d1d5db'
                            },
                            ticks: {
                                color: '#000000', // Force black color
                                padding: 10,
                                font: {
                                    size: 13,
                                    weight: 'bold',
                                    family: 'Arial, sans-serif'
                                }
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Amount (₵)',
                                color: '#000000', // Force black color
                                font: {
                                    size: 14,
                                    weight: 'bold',
                                    family: 'Arial, sans-serif'
                                },
                                padding: 20
                            },
                            beginAtZero: true,
                            grid: {
                                color: '#e5e7eb',
                                drawBorder: true,
                                borderColor: '#d1d5db'
                            },
                            ticks: {
                                color: '#000000', // Force black color
                                padding: 10,
                                font: {
                                    size: 13,
                                    weight: 'bold',
                                    family: 'Arial, sans-serif'
                                },
                                callback: function(value) {
                                    return '₵' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Monthly Performance Trends',
                            color: '#000000', // Force black color
                            font: {
                                size: 16,
                                weight: 'bold',
                                family: 'Arial, sans-serif'
                            },
                            padding: 25
                        },
                        legend: {
                            position: 'top',
                            align: 'start',
                            labels: {
                                color: '#000000', // Force black color
                                usePointStyle: true,
                                padding: 20,
                                boxWidth: 10,
                                boxHeight: 10,
                                font: {
                                    size: 14,
                                    weight: 'bold',
                                    family: 'Arial, sans-serif'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: isDarkMode() ? 'rgba(0, 0, 0, 0.8)' : 'rgba(255, 255, 255, 0.9)',
                            titleColor: colors.text,
                            bodyColor: colors.text,
                            borderColor: colors.borderColor,
                            borderWidth: 1,
                            cornerRadius: 8
                        }
                    }
                }
            });

            // Loan Status Distribution Chart
            const statusCtx = document.getElementById('loanStatusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($loanStatusDistribution->pluck('status')),
                    datasets: [{
                        data: @json($loanStatusDistribution->pluck('count')),
                        backgroundColor: colors.statusColors,
                        borderColor: isDarkMode() ? 'rgba(255, 255, 255, 0.1)' : 'rgba(255, 255, 255, 0.8)',
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '50%',
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 40,
                            left: 10,
                            right: 10
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Loan Distribution by Status',
                            color: '#000000', // Force black color
                            font: {
                                size: 16,
                                weight: 'bold',
                                family: 'Arial, sans-serif'
                            },
                            padding: 25
                        },
                        legend: {
                            position: 'bottom',
                            align: 'center',
                            labels: {
                                color: '#000000', // Force black color
                                usePointStyle: true,
                                padding: 25,
                                boxWidth: 12,
                                boxHeight: 12,
                                font: {
                                    size: 14,
                                    weight: 'bold',
                                    family: 'Arial, sans-serif'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: isDarkMode() ? 'rgba(0, 0, 0, 0.8)' : 'rgba(255, 255, 255, 0.9)',
                            titleColor: colors.text,
                            bodyColor: colors.text,
                            borderColor: colors.borderColor,
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Debug function to check theme detection
        function debugTheme() {
            const isDark = isDarkMode();
            const colors = getThemeColors();
            console.log('Theme Debug:', {
                isDarkMode: isDark,
                textColor: colors.text,
                textSecondary: colors.textSecondary,
                textMuted: colors.textMuted,
                axisLabels: colors.axisLabels,
                htmlClasses: document.documentElement.className,
                bodyClasses: document.body.className,
                FORCE_LIGHT_MODE: FORCE_LIGHT_MODE
            });
            
            // Test if canvas elements exist
            const monthlyCanvas = document.getElementById('monthlyPerformanceChart');
            const statusCanvas = document.getElementById('loanStatusChart');
            console.log('Canvas elements:', {
                monthlyCanvas: monthlyCanvas ? 'Found' : 'Not found',
                statusCanvas: statusCanvas ? 'Found' : 'Not found'
            });
        }

        // Initialize charts when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            debugTheme(); // Debug theme detection
            initializeCharts();
        });

        // Re-initialize charts when theme changes (if theme switching is implemented)
        // This can be called when the theme toggle is used
        function reinitializeCharts() {
            // Destroy existing charts
            Chart.helpers.each(Chart.instances, function(chart) {
                chart.destroy();
            });
            // Re-initialize with new theme
            initializeCharts();
        }

        // Listen for theme changes (if implemented)
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    reinitializeCharts();
                }
            });
        });

        // Start observing
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });

        // Also listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            reinitializeCharts();
        });
    </script>
</x-app-shell>
