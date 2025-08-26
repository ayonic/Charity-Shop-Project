<?php
/**
 * Public Shop Front Page
 * 
 * This is the main public-facing page for the Charity Shop Management System.
 */

// Include necessary files
require_once 'includes/public-header.php';

// Get featured items from the database
$featured_items = [];
$connection = db_connect();

$featured_query = "SELECT i.*, c.name as category_name 
                  FROM inventory i 
                  JOIN categories c ON i.category_id = c.id
                  WHERE i.featured = 1 AND i.quantity > 0 AND i.public_visible = 1
                  ORDER BY i.created_at DESC 
                  LIMIT 6";
                  
$featured_items = db_fetch_all($featured_query);

// Get categories for filtering
$categories = [];
$category_query = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name";
$categories = db_fetch_all($category_query);

// Get recent news/events
$events = [];
$events_query = "SELECT * FROM events WHERE public_visible = 1 AND start_date >= NOW() ORDER BY start_date LIMIT 3";
$events = db_fetch_all($events_query);

db_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charity Shop - Supporting Our Community</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/public-styles.css">
</head>
<body class="bg-gray-50 font-sans">
    <!-- Header/Navigation -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                        <i class="ri-heart-fill text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Charity Shop</h1>
                        <p class="text-xs text-gray-500">Supporting Our Community</p>
                    </div>
                </div>
                
                <nav class="hidden md:flex space-x-6">
                    <a href="index.php" class="text-primary font-medium">Home</a>
                    <a href="shop.php" class="text-gray-600 hover:text-primary">Shop</a>
                    <a href="donate.php" class="text-gray-600 hover:text-primary">Donate</a>
                    <a href="volunteer.php" class="text-gray-600 hover:text-primary">Volunteer</a>
                    <a href="about.php" class="text-gray-600 hover:text-primary">About Us</a>
                    <a href="contact.php" class="text-gray-600 hover:text-primary">Contact</a>
                </nav>
                
                <div class="flex items-center space-x-4">
                    <a href="account.php" class="text-gray-600 hover:text-primary">
                        <i class="ri-user-line text-xl"></i>
                    </a>
                    <a href="cart.php" class="text-gray-600 hover:text-primary relative">
                        <i class="ri-shopping-bag-line text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">0</span>
                    </a>
                    <button class="md:hidden text-gray-600" id="mobileMenuButton">
                        <i class="ri-menu-line text-2xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Menu (Hidden by default) -->
            <div class="md:hidden hidden" id="mobileMenu">
                <nav class="flex flex-col space-y-3 mt-4 pb-4">
                    <a href="index.php" class="text-primary font-medium">Home</a>
                    <a href="shop.php" class="text-gray-600 hover:text-primary">Shop</a>
                    <a href="donate.php" class="text-gray-600 hover:text-primary">Donate</a>
                    <a href="volunteer.php" class="text-gray-600 hover:text-primary">Volunteer</a>
                    <a href="about.php" class="text-gray-600 hover:text-primary">About Us</a>
                    <a href="contact.php" class="text-gray-600 hover:text-primary">Contact</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-8 md:mb-0">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">Shop with Purpose</h1>
                    <p class="text-lg mb-6">Every purchase helps fund vital community programs and supports those in need.</p>
                    <div class="flex space-x-4">
                        <a href="shop.php" class="bg-white text-indigo-600 hover:bg-gray-100 px-6 py-3 rounded-lg font-medium">Shop Now</a>
                        <a href="donate.php" class="bg-transparent border border-white hover:bg-white hover:text-indigo-600 px-6 py-3 rounded-lg font-medium">Donate Items</a>
                    </div>
                </div>
                <div class="md:w-1/2">
                    <img src="images/hero-image.jpg" alt="Charity Shop" class="rounded-lg shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Items Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Featured Items</h2>
                <p class="text-gray-600">Discover unique treasures while supporting a good cause</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <?php if (empty($featured_items)): ?>
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500">No featured items available at the moment. Please check back soon!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($featured_items as $item): ?>
                        <div class="bg-gray-50 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
                            <div class="h-48 overflow-hidden">
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        <i class="ri-image-line text-4xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded-full"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                <h3 class="text-lg font-semibold mt-2"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="text-gray-600 text-sm mt-1 line-clamp-2"><?php echo htmlspecialchars($item['description']); ?></p>
                                <div class="flex items-center justify-between mt-4">
                                    <span class="text-lg font-bold text-gray-900">£<?php echo number_format($item['price'], 2); ?></span>
                                    <a href="item.php?id=<?php echo $item['id']; ?>" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-10">
                <a href="shop.php" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-3 rounded-lg">
                    View All Items
                </a>
            </div>
        </div>
    </section>

    <!-- Impact Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Our Impact</h2>
                <p class="text-gray-600">See how your support makes a difference in our community</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-recycle-line text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">5,000+</h3>
                    <p class="text-gray-600">Items recycled and reused annually</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-hand-heart-line text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">£25,000+</h3>
                    <p class="text-gray-600">Raised for community programs</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-team-line text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">50+</h3>
                    <p class="text-gray-600">Active volunteers supporting our mission</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">How It Works</h2>
                <p class="text-gray-600">Join our community of donors, shoppers, and volunteers</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4 relative">
                        <i class="ri-gift-line text-indigo-600 text-3xl"></i>
                        <span class="absolute -top-2 -right-2 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold">1</span>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Donate</h3>
                    <p class="text-gray-600">Drop off your gently used items at our shop or schedule a pickup</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4 relative">
                        <i class="ri-store-2-line text-indigo-600 text-3xl"></i>
                        <span class="absolute -top-2 -right-2 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold">2</span>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Shop</h3>
                    <p class="text-gray-600">Browse our selection of quality items at affordable prices</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4 relative">
                        <i class="ri-heart-line text-indigo-600 text-3xl"></i>
                        <span class="absolute -top-2 -right-2 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold">3</span>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Support</h3>
                    <p class="text-gray-600">Your purchases fund vital community programs and services</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Upcoming Events</h2>
                <p class="text-gray-600">Join us for these special events and activities</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php if (empty($events)): ?>
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500">No upcoming events at the moment. Please check back soon!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <div class="bg-white rounded-lg overflow-hidden shadow-md">
                            <div class="p-6">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                        <i class="ri-calendar-event-line text-indigo-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg"><?php echo htmlspecialchars($event['title']); ?></h3>
                                        <p class="text-gray-500 text-sm">
                                            <?php 
                                            $start_date = new DateTime($event['start_date']);
                                            echo $start_date->format('F j, Y - g:i A'); 
                                            ?>
                                        </p>
                                    </div>
                                </div>
                                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($event['description']); ?></p>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="ri-map-pin-line mr-1"></i>
                                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                            </div>
                            <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
                                <a href="event.php?id=<?php echo $event['id']; ?>" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">Learn More</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-10">
                <a href="events.php" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-3 rounded-lg">
                    View All Events
                </a>
            </div>
        </div>
    </section>

    <!-- Volunteer CTA Section -->
    <section class="py-16 bg-indigo-600 text-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="md:w-2/3 mb-8 md:mb-0">
                    <h2 class="text-3xl font-bold mb-4">Become a Volunteer</h2>
                    <p class="text-indigo-100 text-lg">Join our team of dedicated volunteers and make a difference in your community. We have flexible schedules and various roles available.</p>
                </div>
                <div>
                    <a href="volunteer.php" class="inline-block bg-white text-indigo-600 hover:bg-gray-100 font-medium px-8 py-4 rounded-lg">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">What People Say</h2>
                <p class="text-gray-600">Hear from our community of donors, shoppers, and volunteers</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gray-200 rounded-full overflow-hidden mr-4">
                            <img src="images/testimonial-1.jpg" alt="Sarah J." class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h4 class="font-bold">Sarah J.</h4>
                            <p class="text-gray-500 text-sm">Regular Shopper</p>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"I love shopping here! I always find unique items at great prices, and it feels good knowing my purchases support important community programs."</p>
                    <div class="flex text-yellow-400 mt-4">
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-6 rounded-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gray-200 rounded-full overflow-hidden mr-4">
                            <img src="images/testimonial-2.jpg" alt="Michael T." class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h4 class="font-bold">Michael T.</h4>
                            <p class="text-gray-500 text-sm">Donor</p>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"Donating my unused items was so easy. The staff was friendly and helpful, and I'm glad my things are getting a second life while helping others."</p>
                    <div class="flex text-yellow-400 mt-4">
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-6 rounded-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gray-200 rounded-full overflow-hidden mr-4">
                            <img src="images/testimonial-3.jpg" alt="Emma R." class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h4 class="font-bold">Emma R.</h4>
                            <p class="text-gray-500 text-sm">Volunteer</p>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"Volunteering here has been such a rewarding experience. I've met wonderful people and gained valuable skills while helping my community."</p>
                    <div class="flex text-yellow-400 mt-4">
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-half-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Stay Updated</h2>
                <p class="text-gray-600 mb-6">Subscribe to our newsletter for updates on new items, special events, and volunteer opportunities</p>
                
                <form class="flex flex-col sm:flex-row gap-2">
                    <input type="email" placeholder="Your email address" class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-3 rounded-lg">
                        Subscribe
                    </button>
                </form>
                <p class="text-xs text-gray-500 mt-4">We respect your privacy and will never share your information.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                            <i class="ri-heart-fill text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">Charity Shop</h3>
                            <p class="text-xs text-gray-400">Supporting Our Community</p>
                        </div>
                    </div>
                    <p class="text-gray-400 mb-4">We're dedicated to making a positive impact in our community through sustainable shopping and charitable giving.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="ri-facebook-fill text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="ri-twitter-fill text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="ri-instagram-fill text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="shop.php" class="text-gray-400 hover:text-white">Shop</a></li>
                        <li><a href="donate.php" class="text-gray-400 hover:text-white">Donate</a></li>
                        <li><a href="volunteer.php" class="text-gray-400 hover:text-white">Volunteer</a></li>
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Us</h4>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <i class="ri-map-pin-line text-indigo-400 mt-1 mr-2"></i>
                            <span class="text-gray-400">123 Charity Lane, Townsville, TS1 2AB</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-phone-line text-indigo-400 mr-2"></i>
                            <span class="text-gray-400">01234 567890</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-mail-line text-indigo-400 mr-2"></i>
                            <span class="text-gray-400">info@charityshop.org</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Opening Hours</h4>
                    <ul class="space-y-2">
                        <li class="flex justify-between">
                            <span class="text-gray-400">Monday - Friday:</span>
                            <span class="text-white">9:00 AM - 5:30 PM</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="text-gray-400">Saturday:</span>
                            <span class="text-white">9:00 AM - 4:00 PM</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="text-gray-400">Sunday:</span>
                            <span class="text-white">Closed</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-400">&copy; <?php echo date('Y'); ?> Charity Shop Management System. All rights reserved.</p>
            </div>
        </div>
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
