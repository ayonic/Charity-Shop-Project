<?php
/**
 * About Page
 * 
 * This page provides information about the charity shop, its mission, and team.
 */

// Include necessary files
require_once 'config/init.php';
require_once 'includes/public-header.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-primary to-primary-dark text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-4">About Us</h1>
        <p class="text-xl">Making a difference in our community through sustainable shopping and charitable giving.</p>
    </div>
</div>

<!-- Mission Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div>
            <h2 class="text-3xl font-bold mb-6">Our Mission</h2>
            <p class="text-gray-600 mb-4">We believe in creating positive change through sustainable retail practices and community support. Our charity shop serves as a bridge between generous donors and those in need, while promoting environmental responsibility through reuse and recycling.</p>
            <p class="text-gray-600">Every purchase and donation helps fund our community programs, supporting local families and initiatives that make our neighborhood a better place for everyone.</p>
        </div>
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold">Community Impact</h3>
                </div>
                <p class="text-gray-600">Over 1,000 families supported annually through our programs and initiatives.</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold">Environmental Impact</h3>
                </div>
                <p class="text-gray-600">Thousands of items diverted from landfills through our reuse and recycling programs.</p>
            </div>
        </div>
    </div>
</div>

<!-- Values Section -->
<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-12">Our Values</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-4">Community First</h3>
                <p class="text-gray-600">We prioritize the needs of our community and work tirelessly to create positive change.</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-4">Sustainability</h3>
                <p class="text-gray-600">We promote environmental responsibility through reuse, recycling, and sustainable practices.</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-4">Inclusivity</h3>
                <p class="text-gray-600">We welcome everyone and believe in creating opportunities for all members of our community.</p>
            </div>
        </div>
    </div>
</div>

<!-- Team Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h2 class="text-3xl font-bold text-center mb-12">Our Team</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
            <img src="images/team/director.jpg" alt="Sarah Johnson" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover">
            <h3 class="text-xl font-semibold">Sarah Johnson</h3>
            <p class="text-gray-600 mb-2">Executive Director</p>
            <p class="text-gray-600 text-sm">Leading our mission with over 15 years of non-profit experience.</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
            <img src="images/team/operations.jpg" alt="Michael Chen" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover">
            <h3 class="text-xl font-semibold">Michael Chen</h3>
            <p class="text-gray-600 mb-2">Operations Manager</p>
            <p class="text-gray-600 text-sm">Ensuring smooth daily operations and volunteer coordination.</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
            <img src="images/team/community.jpg" alt="Emma Thompson" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover">
            <h3 class="text-xl font-semibold">Emma Thompson</h3>
            <p class="text-gray-600 mb-2">Community Outreach</p>
            <p class="text-gray-600 text-sm">Building partnerships and connecting with our community.</p>
        </div>
    </div>
</div>

<!-- Join Us Section -->
<div class="bg-primary text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-6">Join Our Mission</h2>
        <p class="text-xl mb-8">Be part of our journey to create positive change in the community.</p>
        <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
            <a href="volunteer.php" class="bg-white text-primary px-8 py-3 rounded-md hover:bg-gray-100 transition-colors">Volunteer With Us</a>
            <a href="donate.php" class="border-2 border-white text-white px-8 py-3 rounded-md hover:bg-white hover:text-primary transition-colors">Make a Donation</a>
        </div>
    </div>
</div>

<?php require_once 'includes/public-footer.php'; ?>