<?php
require_once 'config/init.php';

// Include header
include_once INCLUDES_PATH . '/public-header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Help Center</h1>

    <div class="mb-8">
        <p class="text-lg text-gray-600">Welcome to our Help Center. Find answers to common questions and learn how to make the most of our platform.</p>
    </div>

    <!-- Search Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <div class="max-w-2xl mx-auto">
            <div class="relative">
                <input type="text" placeholder="Search for help..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-primary">
                <div class="absolute left-3 top-2.5">
                    <i class="ri-search-line text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="grid md:grid-cols-3 gap-6 mb-12">
        <a href="#account" class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
            <i class="ri-user-line text-2xl text-primary mb-3"></i>
            <h3 class="font-semibold text-gray-900 mb-2">Account Help</h3>
            <p class="text-gray-600 text-sm">Managing your account, profile, and settings</p>
        </a>
        <a href="#donations" class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
            <i class="ri-heart-line text-2xl text-primary mb-3"></i>
            <h3 class="font-semibold text-gray-900 mb-2">Donations</h3>
            <p class="text-gray-600 text-sm">Learn about making and managing donations</p>
        </a>
        <a href="#payments" class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
            <i class="ri-bank-card-line text-2xl text-primary mb-3"></i>
            <h3 class="font-semibold text-gray-900 mb-2">Payments</h3>
            <p class="text-gray-600 text-sm">Payment methods and transaction issues</p>
        </a>
    </div>

    <!-- FAQ Sections -->
    <div class="space-y-8">
        <!-- Account Section -->
        <section id="account" class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Account FAQ</h2>
            <div class="space-y-4">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="font-medium text-gray-900 mb-2">How do I create an account?</h3>
                    <p class="text-gray-600">Click the 'Sign Up' button in the top right corner and fill out the registration form with your details.</p>
                </div>
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="font-medium text-gray-900 mb-2">How can I reset my password?</h3>
                    <p class="text-gray-600">Use the 'Forgot Password' link on the login page to receive password reset instructions via email.</p>
                </div>
                <div class="pb-4">
                    <h3 class="font-medium text-gray-900 mb-2">How do I update my profile information?</h3>
                    <p class="text-gray-600">Log in to your account and navigate to the Profile Settings page to update your information.</p>
                </div>
            </div>
        </section>

        <!-- Donations Section -->
        <section id="donations" class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Donations FAQ</h2>
            <div class="space-y-4">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="font-medium text-gray-900 mb-2">How do I make a donation?</h3>
                    <p class="text-gray-600">Choose a campaign, select your donation amount, and follow the payment process using your preferred payment method.</p>
                </div>
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="font-medium text-gray-900 mb-2">Can I get a receipt for my donation?</h3>
                    <p class="text-gray-600">Yes, you'll automatically receive a receipt via email after your donation is processed.</p>
                </div>
                <div class="pb-4">
                    <h3 class="font-medium text-gray-900 mb-2">How can I view my donation history?</h3>
                    <p class="text-gray-600">Log in to your account and visit the 'My Donations' section to view your complete donation history.</p>
                </div>
            </div>
        </section>

        <!-- Payments Section -->
        <section id="payments" class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Payments FAQ</h2>
            <div class="space-y-4">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="font-medium text-gray-900 mb-2">What payment methods do you accept?</h3>
                    <p class="text-gray-600">We accept credit/debit cards through Stripe, PayPal, and direct bank transfers.</p>
                </div>
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="font-medium text-gray-900 mb-2">Is my payment information secure?</h3>
                    <p class="text-gray-600">Yes, all payments are processed securely through our trusted payment partners with industry-standard encryption.</p>
                </div>
                <div class="pb-4">
                    <h3 class="font-medium text-gray-900 mb-2">What should I do if my payment fails?</h3>
                    <p class="text-gray-600">Double-check your payment details and try again. If issues persist, contact your bank or our support team for assistance.</p>
                </div>
            </div>
        </section>
    </div>

    <!-- Contact Support -->
    <div class="mt-12 bg-gray-50 rounded-lg p-6 text-center">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Still Need Help?</h2>
        <p class="text-gray-600 mb-6">Our support team is here to help you with any questions or concerns.</p>
        <a href="mailto:support@charity-shop.com" class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
            <i class="ri-mail-line mr-2"></i>
            Contact Support
        </a>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>