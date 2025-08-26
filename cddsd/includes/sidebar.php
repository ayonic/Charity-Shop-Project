<?php
$universal_items = [
    [
        'text' => 'Shop',
        'url' => '/shop.php',
        'icon' => 'ri-store-2-line',
        'description' => 'Browse products and causes'
    ],
    [
        'text' => 'My Orders',
        'url' => '../my-orders.php',
        'icon' => 'ri-shopping-bag-line',
        'description' => 'View your order history'
    ],
    [
        'text' => 'Wishlist',
        'url' => '../wishlist.php',
        'icon' => 'ri-heart-line',
        'description' => 'Your saved items'
    ],
    [
        'text' => 'Profile',
        'url' => '../profile.php',
        'icon' => 'ri-user-line',
        'description' => 'Manage your account'
    ]
];
/**
 * Sidebar Template
 * 
 * This file contains the role-based sidebar navigation for all pages.
 */

// Get current page for active state
$current_page = current_page();

// Get current user data
$current_user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// Get user role using the new function
$user_role = get_user_role();

// Define menu items based on role
$menu_items = [];

// Only show admin menu items if user is admin
if ($user_role === 'admin') {
    $menu_items = [
        [
            'url' => 'dashboard.php',
            'icon' => 'ri-dashboard-3-line',
            'text' => 'Dashboard',
            'description' => 'Overview & Analytics'
        ],
        [
            'url' => 'users.php',
            'icon' => 'ri-user-settings-line',
            'text' => 'User Management',
            'description' => 'Manage All Users'
        ],
        [
            'url' => 'inventory.php',
            'icon' => 'ri-store-2-line',
            'text' => 'Inventory',
            'description' => 'Manage Stock & Items'
        ],
        [
            'url' => 'donations.php',
            'icon' => 'ri-gift-line',
            'text' => 'Donations',
            'description' => 'Track Contributions'
        ],
        [
            'url' => 'sales.php',
            'icon' => 'ri-shopping-cart-line',
            'text' => 'Sales & POS',
            'description' => 'Process Transactions'
        ],
        [
            'url' => 'volunteers.php',
            'icon' => 'ri-team-line',
            'text' => 'Volunteers',
            'description' => 'Manage Team & Hours'
        ],
        [
            'url' => 'reports.php',
            'icon' => 'ri-bar-chart-box-line',
            'text' => 'Reports',
            'description' => 'Analytics & Insights'
        ],
        [
            'url' => 'activities.php',
            'icon' => 'ri-history-line',
            'text' => 'Activities',
            'description' => 'System Activity Log'
        ],
        [
            'url' => 'settings.php',
            'icon' => 'ri-settings-3-line',
            'text' => 'Settings',
            'description' => 'System Configuration'
        ],
        [
            'url' => 'payment_settings.php',
            'icon' => 'ri-bank-card-line',
            'text' => 'Payment Settings',
            'description' => 'Configure Payment Gateways'
        ]
    ];
} elseif ($user_role === 'moderator') {
    $menu_items = [
        [
            'url' => 'moderator/dashboard.php',
            'icon' => 'ri-dashboard-3-line',
            'text' => 'Dashboard',
            'description' => 'Moderator Overview'
        ],
        [
            'url' => 'content-review.php',
            'icon' => 'ri-file-list-3-line',
            'text' => 'Content Review',
            'description' => 'Review & Approve Content'
        ],
        [
            'url' => 'user-reports.php',
            'icon' => 'ri-flag-line',
            'text' => 'User Reports',
            'description' => 'Handle User Reports'
        ],
        [
            'url' => 'activities.php',
            'icon' => 'ri-history-line',
            'text' => 'Activities',
            'description' => 'Activity Log'
        ],
        [
            'url' => 'profile.php',
            'icon' => 'ri-user-settings-line',
            'text' => 'Profile',
            'description' => 'Manage Account'
        ]
    ];
} elseif ($user_role === 'customer') {
    $menu_items = [
        [
            'url' => '/customer/dashboard.php',
            'icon' => 'ri-dashboard-3-line',
            'text' => 'Dashboard',
            'description' => 'Customer Overview'
        ]
    ];
    $menu_items = array_merge($universal_items, $menu_items);
} elseif ($user_role === 'donor') {
    $menu_items = [
        [
            'url' => '../donor/dashboard.php',
            'icon' => 'ri-dashboard-3-line',
            'text' => 'Dashboard',
            'description' => 'Donor Overview'
        ],
        [
            'url' => '../my-donations.php',
            'icon' => 'ri-gift-line',
            'text' => 'My Donations',
            'description' => 'Donation History'
        ],
        [
            'url' => '../donate.php',
            'icon' => 'ri-hand-heart-line',
            'text' => 'Make Donation',
            'description' => 'Start Donating'
        ],
        [
            'url' => '../impact.php',
            'icon' => 'ri-line-chart-line',
            'text' => 'My Impact',
            'description' => 'View Contribution Impact'
        ],
        [
            'url' => '../profile.php',
            'icon' => 'ri-user-settings-line',
            'text' => 'Profile',
            'description' => 'Manage Account'
        ]
    ];
} elseif ($user_role === 'volunteer') {
    $menu_items = [
        [
            'url' => '../volunteer/dashboard.php',
            'icon' => 'ri-dashboard-3-line',
            'text' => 'Dashboard',
            'description' => 'My Overview'
        ],
        [
            'url' => '../my-hours.php',
            'icon' => 'ri-time-line',
            'text' => 'My Hours',
            'description' => 'Track Volunteer Hours'
        ],
        [
            'url' => '../events.php',
            'icon' => 'ri-calendar-event-line',
            'text' => 'Events',
            'description' => 'View & Join Events'
        ],
        [
            'url' => '../profile.php',
            'icon' => 'ri-user-settings-line',
            'text' => 'Profile',
            'description' => 'Manage Account'
        ]
    ];
} elseif ($user_role === 'manager' || $user_role === 'cashier') {
    $menu_items = [
        [
            'url' => ($user_role === 'manager' ? '../manager/dashboard.php' : 'cashier/dashboard.php'),
            'icon' => 'ri-dashboard-3-line',
            'text' => ($user_role === 'manager' ? 'Manager Dashboard' : 'Cashier Dashboard'),
            'description' => ($user_role === 'manager' ? 'Manager Overview' : 'Cashier Overview')
        ],
        [
            'url' => '../sales.php',
            'icon' => 'ri-shopping-cart-line',
            'text' => 'Sales & POS',
            'description' => 'Process Transactions'
        ],
        [
            'url' => '../inventory.php',
            'icon' => 'ri-store-2-line',
            'text' => 'Inventory',
            'description' => 'Manage Stock & Items'
        ],
        [
            'url' => '../reports.php',
            'icon' => 'ri-bar-chart-box-line',
            'text' => 'Reports',
            'description' => 'Analytics & Insights'
        ],
        [
            'url' => '../profile.php',
            'icon' => 'ri-user-settings-line',
            'text' => 'Profile',
            'description' => 'Manage Account'
        ]
    ];
} else {
    $menu_items = [
        [
            'url' => '../login.php',
            'icon' => 'ri-login-box-line',
            'text' => 'Login',
            'description' => 'Sign In'
        ]
    ];
}

$user_initials = $current_user ? get_user_initials($current_user['first_name'], $current_user['last_name']) : 'GU';
$user_name = $current_user ? $current_user['first_name'] . ' ' . $current_user['last_name'] : 'Guest User';
$user_role_display = $current_user && isset($current_user['role']) ? ucfirst($current_user['role']) : 'Guest';
?>

<!-- Sidebar -->
<div class="hidden md:flex md:flex-shrink-0">
    <div class="flex flex-col w-72 glass-effect border-r border-white/20">
        <!-- Logo -->
        <div class="flex items-center justify-center h-20 px-6 border-b border-white/10">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center shadow-lg">
                    <i class="ri-store-2-line text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Charity Shop</h1>
                    <p class="text-sm text-gray-500">Management System</p>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="flex flex-col flex-grow px-4 py-6 overflow-y-auto custom-scrollbar">
            <nav class="space-y-2">
                <?php foreach ($menu_items as $item): ?>
                    <a href="<?php echo $item['url']; ?>" 
                       class="sidebar-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group <?php echo $current_page === $item['url'] ? 'active' : 'text-gray-700 hover:text-gray-900'; ?>">
                        <div class="w-6 h-6 mr-4 flex items-center justify-center">
                            <i class="<?php echo $item['icon']; ?> text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-medium"><?php echo $item['text']; ?></div>
                            <div class="text-xs opacity-70 <?php echo $current_page === $item['url'] ? 'text-white/70' : 'text-gray-500'; ?>">
                                <?php echo $item['description']; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
        
        <!-- User Profile -->
        <?php if ($current_user): ?>
        <div class="flex-shrink-0 p-4 border-t border-white/10">
            <div class="flex items-center p-3 rounded-xl bg-white/5 hover:bg-white/10 transition-colors duration-200">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-purple-600 flex items-center justify-center text-white font-bold shadow-lg">
                        <?php echo $user_initials; ?>
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900"><?php echo $user_name; ?></p>
                    <p class="text-xs text-gray-500"><?php echo $user_role_display; ?></p>
                </div>
                <div class="ml-2">
                    <a href="logout.php" class="flex-shrink-0 p-2 text-gray-400 rounded-lg hover:text-gray-600 hover:bg-white/10 transition-colors duration-200" title="Sign Out">
                        <i class="ri-logout-box-line text-lg"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>