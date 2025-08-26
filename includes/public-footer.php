<?php
/**
 * Public Footer Include
 * 
 * This file contains common footer elements for the public-facing pages.
 */

// Get site settings
$site_name = "Charity Shop";
$site_tagline = "Supporting Our Community";
$contact_address = "123 Charity Lane, Townsville, TS1 2AB";
$contact_phone = "01234 567890";
$contact_email = "info@charityshop.org";
$opening_hours = [
    'Monday - Friday' => '9:00 AM - 5:30 PM',
    'Saturday' => '9:00 AM - 4:00 PM',
    'Sunday' => 'Closed'
];

$connection = db_connect();

$query = $pdo->query("SELECT * FROM settings WHERE name = 'site_name' OR name = 'site_tagline'");
$stmt = $connection->query($settings_query);

if ($stmt) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['setting_key'] == 'site_name' && !empty($row['setting_value'])) {
            $site_name = $row['setting_value'];
        } elseif ($row['setting_key'] == 'site_tagline' && !empty($row['setting_value'])) {
            $site_tagline = $row['setting_value'];
        } elseif ($row['setting_key'] == 'contact_address' && !empty($row['setting_value'])) {
            $contact_address = $row['setting_value'];
        } elseif ($row['setting_key'] == 'contact_phone' && !empty($row['setting_value'])) {
            $contact_phone = $row['setting_value'];
        } elseif ($row['setting_key'] == 'contact_email' && !empty($row['setting_value'])) {
            $contact_email = $row['setting_value'];
        } elseif ($row['setting_key'] == 'opening_hours_weekday' && !empty($row['setting_value'])) {
            $opening_hours['Monday - Friday'] = $row['setting_value'];
        } elseif ($row['setting_key'] == 'opening_hours_saturday' && !empty($row['setting_value'])) {
            $opening_hours['Saturday'] = $row['setting_value'];
        } elseif ($row['setting_key'] == 'opening_hours_sunday' && !empty($row['setting_value'])) {
            $opening_hours['Sunday'] = $row['setting_value'];
        }
    }
}
?>

<!-- Footer -->
<footer class="bg-gray-900 text-white py-12 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold"><?php echo htmlspecialchars($site_name); ?></h3>
                        <p class="text-sm text-gray-400"><?php echo htmlspecialchars($site_tagline); ?></p>
                    </div>
                </div>
                <p class="text-gray-400 mb-6">Making a positive impact in our community through sustainable shopping and charitable initiatives.</p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.77,7.46H14.5v-1.9c0-.9.6-1.1,1-1.1h3V.5h-4.33C10.24.5,9.5,3.44,9.5,5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4Z"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.954 4.569c-.885.389-1.83.654-2.825.775 1.014-.611 1.794-1.574 2.163-2.723-.951.555-2.005.959-3.127 1.184-.896-.959-2.173-1.559-3.591-1.559-2.717 0-4.92 2.203-4.92 4.917 0 .39.045.765.127 1.124C7.691 8.094 4.066 6.13 1.64 3.161c-.427.722-.666 1.561-.666 2.475 0 1.71.87 3.213 2.188 4.096-.807-.026-1.566-.248-2.228-.616v.061c0 2.385 1.693 4.374 3.946 4.827-.413.111-.849.171-1.296.171-.314 0-.615-.03-.916-.086.631 1.953 2.445 3.377 4.604 3.417-1.68 1.319-3.809 2.105-6.102 2.105-.39 0-.779-.023-1.17-.067 2.189 1.394 4.768 2.209 7.557 2.209 9.054 0 13.999-7.496 13.999-13.986 0-.209 0-.42-.015-.63.961-.689 1.8-1.56 2.46-2.548l-.047-.02z"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <div>
                <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    <li><a href="index.php" class="text-gray-400 hover:text-white transition-colors duration-200">Home</a></li>
                    <li><a href="shop.php" class="text-gray-400 hover:text-white transition-colors duration-200">Shop</a></li>
                    <li><a href="donate.php" class="text-gray-400 hover:text-white transition-colors duration-200">Donate</a></li>
                    <li><a href="volunteer.php" class="text-gray-400 hover:text-white transition-colors duration-200">Volunteer</a></li>
                    <li><a href="about.php" class="text-gray-400 hover:text-white transition-colors duration-200">About Us</a></li>
                    <li><a href="contact.php" class="text-gray-400 hover:text-white transition-colors duration-200">Contact</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="text-lg font-semibold mb-4">Resources</h4>
                <ul class="space-y-2">
                    <li><a href="terms.php" class="text-gray-400 hover:text-white transition-colors duration-200">Terms of Service</a></li>
                    <li><a href="privacy.php" class="text-gray-400 hover:text-white transition-colors duration-200">Privacy Policy</a></li>
                    <li><a href="help.php" class="text-gray-400 hover:text-white transition-colors duration-200">Help Center</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="text-lg font-semibold mb-4">Contact Us</h4>
                <ul class="space-y-3">
                    <li class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-primary mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="text-gray-400"><?php echo nl2br(htmlspecialchars($contact_address)); ?></span>
                    </li>
                    <li class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span class="text-gray-400"><?php echo htmlspecialchars($contact_phone); ?></span>
                    </li>
                    <li class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <a href="mailto:<?php echo htmlspecialchars($contact_email); ?>" class="text-gray-400 hover:text-white transition-colors duration-200"><?php echo htmlspecialchars($contact_email); ?></a>
                    </li>
                </ul>
            </div>
            
            <div>
                <h4 class="text-lg font-semibold mb-4">Opening Hours</h4>
                <ul class="space-y-3">
                    <?php foreach ($opening_hours as $day => $hours): ?>
                        <li class="flex justify-between items-center">
                            <span class="text-gray-400"><?php echo htmlspecialchars($day); ?></span>
                            <span class="text-gray-300 font-medium"><?php echo htmlspecialchars($hours); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-gray-800 mt-12 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <p class="text-gray-400 text-sm">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved. Developed by Everest Itibi</p>
            </div>
        </div>
    </div>
    <?php include_once INCLUDES_PATH . '/creator-signature.php'; ?>
</footer>

<script>
    // Mobile menu toggle
    document.getElementById('mobileMenuButton').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobileMenu');
        mobileMenu.classList.toggle('hidden');
    });
</script>
</body>
</html>
