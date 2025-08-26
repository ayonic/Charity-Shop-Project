<?php
/**
 * Navbar Template
 * 
 * This file contains the top navigation bar for all pages.
 */

// Get current user
$current_user = get_logged_in_user();
$user_initials = $current_user ? get_user_initials($current_user['first_name'], $current_user['last_name']) : 'GU';
?>

<!-- Top navbar -->
<div class="flex-shrink-0 glass-effect border-b border-white/20">
    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
        <!-- Mobile menu button -->
        <div class="flex items-center">
            <button class="md:hidden -ml-0.5 -mt-0.5 h-12 w-12 inline-flex items-center justify-center rounded-xl text-gray-500 hover:text-gray-900 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary transition-colors duration-200" id="mobileSidebarToggle">
                <i class="ri-menu-line text-xl"></i>
            </button>
            <div class="ml-4 md:hidden">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                        <i class="ri-store-2-line text-white"></i>
                    </div>
                    <h1 class="text-lg font-bold text-gray-900">Charity Shop</h1>
                </div>
            </div>
        </div>
        
        <!-- Search -->
        <div class="flex-1 flex justify-center px-2 lg:ml-6 lg:justify-end">
            <div class="max-w-lg w-full lg:max-w-xs">
                <label for="search" class="sr-only">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                    <input id="search" name="search" 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300/50 rounded-xl leading-5 bg-white/50 backdrop-blur-sm placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition-all duration-200" 
                           placeholder="Search..." type="search">
                </div>
            </div>
        </div>
        
        <!-- Right side buttons -->
        <div class="ml-4 flex items-center md:ml-6 space-x-2">
            <!-- Notifications -->
            <button class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200 relative">
                <i class="ri-notification-3-line text-xl"></i>
                <span class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-400 ring-2 ring-white"></span>
            </button>
            
            <!-- Calendar -->
            <button class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200">
                <i class="ri-calendar-line text-xl"></i>
            </button>
            
            <!-- User menu -->
            <div class="ml-3 relative">
                <div>
                    <button class="max-w-xs bg-white/10 backdrop-blur-sm flex items-center text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary p-2 hover:bg-white/20 transition-colors duration-200" id="userMenuButton">
                        <span class="sr-only">Open user menu</span>
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-purple-600 flex items-center justify-center text-white font-medium shadow-lg">
                            <?php echo $user_initials; ?>
                        </div>
                        <i class="ri-arrow-down-s-line ml-2 text-gray-500"></i>
                    </button>
                </div>
                
                <!-- User dropdown menu -->
                <div class="hidden origin-top-right absolute right-0 mt-2 w-56 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none backdrop-blur-sm border border-gray-200/50 animate-scale-in" id="userMenu" role="menu">
                    <div class="py-2">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900"><?php echo $user_name; ?></p>
                            <p class="text-xs text-gray-500"><?php echo $user_role; ?></p>
                        </div>
                        <a href="profile.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200" role="menuitem">
                            <i class="ri-user-line mr-3 text-gray-400"></i>
                            Your Profile
                        </a>
                        <a href="settings.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200" role="menuitem">
                            <i class="ri-settings-3-line mr-3 text-gray-400"></i>
                            Settings
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-200" role="menuitem">
                            <i class="ri-logout-box-line mr-3 text-gray-400"></i>
                            Sign out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile sidebar overlay -->
<div class="md:hidden fixed inset-0 z-40 hidden modal-backdrop" id="mobileSidebar">
    <div class="fixed inset-0" id="mobileSidebarOverlay"></div>
    <div class="relative flex-1 flex flex-col max-w-xs w-full pt-5 pb-4 bg-white shadow-xl transform transition-transform duration-300 ease-in-out -translate-x-full" id="mobileSidebarPanel">
        <div class="absolute top-0 right-0 -mr-12 pt-2">
            <button class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white bg-white/10 backdrop-blur-sm" id="closeMobileSidebar">
                <span class="sr-only">Close sidebar</span>
                <i class="ri-close-line text-white text-xl"></i>
            </button>
        </div>
        
        <!-- Mobile sidebar content -->
        <div class="flex-shrink-0 flex items-center px-4 mb-6">
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
        
        <div class="mt-5 flex-1 h-0 overflow-y-auto custom-scrollbar">
            <nav class="px-4 space-y-2">
                <?php foreach ($menu_items as $item): ?>
                    <a href="<?php echo $item['url']; ?>" 
                       class="sidebar-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 <?php echo $current_page === $item['url'] ? 'active' : 'text-gray-700 hover:text-gray-900'; ?>">
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
        
        <!-- Mobile user profile -->
        <div class="flex-shrink-0 p-4 border-t border-gray-200">
            <div class="flex items-center p-3 rounded-xl bg-gray-50">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-purple-600 flex items-center justify-center text-white font-bold shadow-lg">
                        <?php echo $user_initials; ?>
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900"><?php echo $user_name; ?></p>
                    <p class="text-xs text-gray-500"><?php echo $user_role; ?></p>
                </div>
                <a href="logout.php" class="ml-2 p-2 text-gray-400 rounded-lg hover:text-gray-600 hover:bg-gray-100 transition-colors duration-200">
                    <i class="ri-logout-box-line text-lg"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile sidebar toggle
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const mobileSidebarPanel = document.getElementById('mobileSidebarPanel');
    const mobileSidebarOverlay = document.getElementById('mobileSidebarOverlay');
    const closeMobileSidebar = document.getElementById('closeMobileSidebar');
    
    function openSidebar() {
        mobileSidebar.classList.remove('hidden');
        setTimeout(() => {
            mobileSidebarPanel.classList.remove('-translate-x-full');
        }, 10);
    }
    
    function closeSidebar() {
        mobileSidebarPanel.classList.add('-translate-x-full');
        setTimeout(() => {
            mobileSidebar.classList.add('hidden');
        }, 300);
    }
    
    mobileSidebarToggle.addEventListener('click', openSidebar);
    closeMobileSidebar.addEventListener('click', closeSidebar);
    mobileSidebarOverlay.addEventListener('click', closeSidebar);
    
    // User menu toggle
    const userMenuButton = document.getElementById('userMenuButton');
    const userMenu = document.getElementById('userMenu');
    
    const userMenuContainer = userMenuButton.closest('.relative');
    
    userMenuContainer.addEventListener('mouseenter', function() {
        userMenu.classList.remove('hidden');
    });
    
    userMenuContainer.addEventListener('mouseleave', function() {
        userMenu.classList.add('hidden');
    });
    });
    
    // Search functionality
    const searchInput = document.getElementById('search');
    searchInput.addEventListener('focus', function() {
        this.parentElement.classList.add('ring-2', 'ring-primary');
    });
    
    searchInput.addEventListener('blur', function() {
        this.parentElement.classList.remove('ring-2', 'ring-primary');
    });
});
</script>
