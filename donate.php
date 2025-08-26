<?php
/**
 * Donate Page
 * 
 * This page allows users to submit donations and view donation information.
 */

// Include necessary files
require_once 'config/init.php';
require_once 'includes/public-header.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-primary to-primary-dark text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-4">Make a Difference</h1>
        <p class="text-xl">Your donations help us support our community and create positive change.</p>
    </div>
</div>

<!-- Donation Information -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- What We Accept -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-4">What We Accept</h2>
            <ul class="space-y-2 text-gray-600">
                <li>• Clothing and Accessories</li>
                <li>• Books and Media</li>
                <li>• Home Goods and Furniture</li>
                <li>• Electronics (in working condition)</li>
                <li>• Toys and Games</li>
                <li>• Sports Equipment</li>
            </ul>
        </div>

        <!-- Monetary Donation -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-4">Make a Monetary Donation</h2>
            <form action="process_donation.php" method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <button type="button" class="donation-btn bg-white border-2 border-primary text-primary hover:bg-primary hover:text-white py-2 px-4 rounded-md transition-colors" data-amount="10">$10</button>
                    <button type="button" class="donation-btn bg-white border-2 border-primary text-primary hover:bg-primary hover:text-white py-2 px-4 rounded-md transition-colors" data-amount="25">$25</button>
                    <button type="button" class="donation-btn bg-white border-2 border-primary text-primary hover:bg-primary hover:text-white py-2 px-4 rounded-md transition-colors" data-amount="50">$50</button>
                    <button type="button" class="donation-btn bg-white border-2 border-primary text-primary hover:bg-primary hover:text-white py-2 px-4 rounded-md transition-colors" data-amount="100">$100</button>
                </div>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                    <input type="number" name="amount" id="custom-amount" class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" placeholder="Enter custom amount" min="1" step="0.01" required>
                </div>
                <script>
                    document.querySelectorAll('.donation-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            // Remove active class from all buttons
                            document.querySelectorAll('.donation-btn').forEach(btn => {
                                btn.classList.remove('bg-primary', 'text-white');
                                btn.classList.add('bg-white', 'text-primary');
                            });
                            
                            // Add active class to clicked button
                            this.classList.remove('bg-white', 'text-primary');
                            this.classList.add('bg-primary', 'text-white');
                            
                            // Update custom amount input
                            document.getElementById('custom-amount').value = this.dataset.amount;
                        });
                    });

                    // Handle custom amount input
                    document.getElementById('custom-amount').addEventListener('input', function() {
                        // Remove active class from all buttons when custom amount is entered
                        document.querySelectorAll('.donation-btn').forEach(btn => {
                            btn.classList.remove('bg-primary', 'text-white');
                            btn.classList.add('bg-white', 'text-primary');
                        });
                    });
                </script>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" class="amount-btn bg-gray-100 hover:bg-primary hover:text-white text-gray-800 font-semibold py-2 px-4 rounded" data-amount="10">£10</button>
                    <button type="button" class="amount-btn bg-gray-100 hover:bg-primary hover:text-white text-gray-800 font-semibold py-2 px-4 rounded" data-amount="25">£25</button>
                    <button type="button" class="amount-btn bg-gray-100 hover:bg-primary hover:text-white text-gray-800 font-semibold py-2 px-4 rounded" data-amount="50">£50</button>
                    <button type="button" class="amount-btn bg-gray-100 hover:bg-primary hover:text-white text-gray-800 font-semibold py-2 px-4 rounded" data-amount="100">£100</button>
                </div>
                <div class="mt-4">
                    <label for="custom-amount" class="block text-sm font-medium text-gray-700">Custom Amount (£)</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">£</span>
                        </div>
                        <input type="number" name="amount" id="custom-amount" class="focus:ring-primary focus:border-primary block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" step="0.01" min="0.01" required>
                    </div>
                </div>
                <button type="submit" class="w-full bg-primary text-white font-semibold py-2 px-4 rounded hover:bg-primary-dark transition duration-150">Donate Now</button>
                <script>
                    document.querySelectorAll('.amount-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            const amount = this.dataset.amount;
                            document.getElementById('custom-amount').value = amount;
                            
                            // Remove active class from all buttons
                            document.querySelectorAll('.amount-btn').forEach(btn => {
                                btn.classList.remove('bg-primary', 'text-white');
                                btn.classList.add('bg-gray-100', 'text-gray-800');
                            });
                            
                            // Add active class to clicked button
                            this.classList.remove('bg-gray-100', 'text-gray-800');
                            this.classList.add('bg-primary', 'text-white');
                        });
                    });
                </script>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="donation-amount px-4 py-2 border rounded-md hover:bg-primary hover:text-white" data-amount="10">$10</button>
                    <button type="button" class="donation-amount px-4 py-2 border rounded-md hover:bg-primary hover:text-white" data-amount="25">$25</button>
                    <button type="button" class="donation-amount px-4 py-2 border rounded-md hover:bg-primary hover:text-white" data-amount="50">$50</button>
                    <button type="button" class="donation-amount px-4 py-2 border rounded-md hover:bg-primary hover:text-white" data-amount="100">$100</button>
                </div>
                <div class="mt-4">
                    <label for="custom-amount" class="block text-sm font-medium text-gray-700">Custom Amount ($)</label>
                    <input type="number" id="custom-amount" name="amount" min="1" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                </div>
                <button type="submit" class="w-full bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Donate Now</button>
            </form>
            <script>
                document.querySelectorAll('.donation-amount').forEach(button => {
                    button.addEventListener('click', () => {
                        // Remove active class from all buttons
                        document.querySelectorAll('.donation-amount').forEach(btn => {
                            btn.classList.remove('bg-primary', 'text-white');
                        });
                        // Add active class to clicked button
                        button.classList.add('bg-primary', 'text-white');
                        // Set amount in custom input
                        document.getElementById('custom-amount').value = button.dataset.amount;
                    });
                });
            </script>
        </div>

        <!-- Donation Process -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-4">Donation Process</h2>
            <ol class="space-y-2 text-gray-600">
                <li>1. Fill out the donation form below</li>
                <li>2. Schedule a drop-off time</li>
                <li>3. Bring items to our location</li>
                <li>4. Receive a tax-deductible receipt</li>
            </ol>
        </div>

        <!-- Impact -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-4">Your Impact</h2>
            <p class="text-gray-600">Your donations help us:</p>
            <ul class="space-y-2 text-gray-600 mt-2">
                <li>• Support local families in need</li>
                <li>• Fund community programs</li>
                <li>• Reduce environmental waste</li>
                <li>• Create job opportunities</li>
            </ul>
        </div>
    </div>
</div>

<!-- Donation Form -->
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-semibold mb-6">Donation Form</h2>
        <form action="process_donation.php" method="POST" class="space-y-6">
            <!-- Contact Information -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium">Contact Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" name="first_name" id="first_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" name="last_name" id="last_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" name="phone" id="phone" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                    </div>
                </div>
            </div>

            <!-- Donation Details -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium">Donation Details</h3>
                <div>
                    <label for="items" class="block text-sm font-medium text-gray-700">Items to Donate</label>
                    <textarea name="items" id="items" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" placeholder="Please list the items you wish to donate"></textarea>
                </div>
                <div>
                    <label for="preferred_date" class="block text-sm font-medium text-gray-700">Preferred Drop-off Date</label>
                    <input type="date" name="preferred_date" id="preferred_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50 transition-colors">
                    Submit Donation
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/public-footer.php'; ?>