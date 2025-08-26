<?php
/**
 * Volunteer Page
 * 
 * This page allows users to apply for volunteer positions and view volunteer opportunities.
 */

// Include necessary files
require_once 'config/init.php';
require_once 'includes/public-header.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-primary to-primary-dark text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-4">Join Our Team</h1>
        <p class="text-xl">Make a difference in your community by volunteering with us.</p>
    </div>
</div>

<!-- Volunteer Information -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Opportunities -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-4">Available Positions</h2>
            <ul class="space-y-2 text-gray-600">
                <li>• Store Assistant</li>
                <li>• Donation Processor</li>
                <li>• Customer Service</li>
                <li>• Event Coordinator</li>
                <li>• Social Media Helper</li>
                <li>• Delivery Assistant</li>
            </ul>
        </div>

        <!-- Requirements -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-4">Requirements</h2>
            <ul class="space-y-2 text-gray-600">
                <li>• Must be 16 years or older</li>
                <li>• Commit to minimum 4 hours/week</li>
                <li>• Attend orientation session</li>
                <li>• Complete background check</li>
                <li>• Reliable transportation</li>
            </ul>
        </div>

        <!-- Benefits -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-4">Volunteer Benefits</h2>
            <ul class="space-y-2 text-gray-600">
                <li>• Store discount</li>
                <li>• Flexible schedule</li>
                <li>• Professional references</li>
                <li>• Skills development</li>
                <li>• Community involvement</li>
            </ul>
        </div>
    </div>
</div>

<!-- Application Form -->
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-semibold mb-6">Volunteer Application</h2>
        <form action="process_volunteer.php" method="POST" class="space-y-6">
            <!-- Personal Information -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium">Personal Information</h3>
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
                    <div>
                        <label for="dob" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                        <input type="date" name="dob" id="dob" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                    </div>
                </div>
            </div>

            <!-- Volunteer Preferences -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium">Volunteer Preferences</h3>
                <div>
                    <label for="position" class="block text-sm font-medium text-gray-700">Preferred Position</label>
                    <select name="position" id="position" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                        <option value="">Select a position</option>
                        <option value="store_assistant">Store Assistant</option>
                        <option value="donation_processor">Donation Processor</option>
                        <option value="customer_service">Customer Service</option>
                        <option value="event_coordinator">Event Coordinator</option>
                        <option value="social_media">Social Media Helper</option>
                        <option value="delivery">Delivery Assistant</option>
                    </select>
                </div>
                <div>
                    <label for="availability" class="block text-sm font-medium text-gray-700">Availability</label>
                    <textarea name="availability" id="availability" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" placeholder="Please list your available days and times"></textarea>
                </div>
                <div>
                    <label for="experience" class="block text-sm font-medium text-gray-700">Relevant Experience</label>
                    <textarea name="experience" id="experience" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" placeholder="Please describe any relevant experience"></textarea>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium">Emergency Contact</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="emergency_name" class="block text-sm font-medium text-gray-700">Contact Name</label>
                        <input type="text" name="emergency_name" id="emergency_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="emergency_phone" class="block text-sm font-medium text-gray-700">Contact Phone</label>
                        <input type="tel" name="emergency_phone" id="emergency_phone" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50 transition-colors">
                    Submit Application
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/public-footer.php'; ?>