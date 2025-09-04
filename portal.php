<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Management Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <style>
        section {
            min-height: 100vh;
            height: 100vh;
            overflow-y: hidden;
        }
        body {
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        .max-w-5xl {
            max-width: 80rem;
        }
        /* Show only the targeted section */
        section {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }
        section:target {
            display: flex;
        }
        /* Hide all other sections when one is targeted */
        #home:target ~ section,
        #code-of-conduct:target ~ section:not(#code-of-conduct),
        #announcements:target ~ section:not(#announcements),
        #about:target ~ section:not(#about),
        #services:target ~ section:not(#services),
        #contact:target ~ section:not(#contact) {
            display: none;
        }
        /* Navbar highlight for active section */
        nav a {
            position: relative;
        }
        nav a:target,
        nav a[href="#home"]:target,
        nav a[href="#code-of-conduct"]:target,
        nav a[href="#announcements"]:target,
        nav a[href="#about"]:target,
        nav a[href="#services"]:target,
        nav a[href="#contact"]:target {
            color: #3b82f6; /* Highlight color */
        }
        nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background-color: #3b82f6;
            transition: width 0.3s ease, left 0.3s ease;
        }
        nav a:hover::after,
        nav a:target::after,
        nav a[href="#home"]:target::after,
        nav a[href="#code-of-conduct"]:target::after,
        nav a[href="#announcements"]:target::after,
        nav a[href="#about"]:target::after,
        nav a[href="#services"]:target::after,
        nav a[href="#contact"]:target::after {
            width: 100%;
            left: 0;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <!-- Navigation -->
    <nav class="bg-gray-800 text-white p-6 sticky top-0 shadow-xl z-50 h-16">
        <div class="max-w-7xl mx-auto flex justify-between items-center h-full">
            <h1 class="text-3xl font-bold">Gym Management Portal</h1>
            <div class="flex space-x-8 text-lg">
                <a href="#home" class="hover:text-blue-400 transition duration-300 ease-in-out">Home</a>
                <a href="#code-of-conduct" class="hover:text-blue-400 transition duration-300 ease-in-out">Code of Conduct</a>
                <a href="#announcements" class="hover:text-blue-400 transition duration-300 ease-in-out">Announcements</a>
                <a href="#about" class="hover:text-blue-400 transition duration-300 ease-in-out">About</a>
                <a href="#services" class="hover:text-blue-400 transition duration-300 ease-in-out">Services</a>
                <a href="#contact" class="hover:text-blue-400 transition duration-300 ease-in-out">Contact</a>
            </div>
        </div>
    </nav>

    <!-- Home Section -->
    <section id="home" class="flex items-center justify-center bg-gradient-to-br from-blue-100 to-green-100" data-aos="fade-up" data-aos-duration="1000">
        <div class="text-center max-w-5xl mx-auto px-6">
            <h2 class="text-5xl font-bold mb-6 text-gray-800">Welcome to Our Gym Management System</h2>
            <p class="text-xl text-gray-600 mb-8">Manage your gym experience with ease. Log in to access your dashboard, track your progress, and explore our services.</p>
            <a href="index.php" class="bg-blue-600 text-white px-8 py-4 rounded-lg hover:bg-blue-700 transition duration-300 ease-in-out font-semibold text-lg shadow-md">Log In</a>
        </div>
    </section>

    <!-- Code of Conduct Section -->
    <section id="code-of-conduct" class="flex items-center justify-center bg-white" data-aos="fade-right" data-aos-duration="1000">
        <div class="max-w-5xl mx-auto px-6">
            <div class="bg-white p-10 rounded-2xl shadow-2xl">
                <h2 class="text-5xl font-bold mb-8 text-center text-gray-800">Code of Conduct</h2>
                <p class="text-xl text-gray-600 mb-10">Our Code of Conduct is designed to foster a safe, respectful, and inclusive environment for all members, staff, and visitors at our gym. By adhering to these guidelines, we ensure that everyone can enjoy their fitness journey without disruption or discomfort.</p>
                <ul class="list-disc list-inside space-y-6 text-gray-700 text-lg">
                    <li>Respect all members, staff, and guests at all times. Harassment, discrimination, or bullying of any kind will not be tolerated.</li>
                    <li>Maintain proper hygiene by wiping down equipment after use and using deodorant as needed.</li>
                    <li>Follow all safety protocols, including using spotters for heavy lifts and not dropping weights unnecessarily.</li>
                    <li>Keep noise levels reasonable; avoid excessive grunting or loud music without headphones.</li>
                    <li>Do not monopolize equipment; allow others to work in during your rest periods.</li>
                    <li>Return weights, mats, and other equipment to their proper places after use.</li>
                    <li>Adhere to the gym's dress code: appropriate athletic wear and closed-toe shoes required.</li>
                    <li>No unauthorized filming or photography without consent from those involved.</li>
                    <li>Report any damaged equipment or unsafe conditions to staff immediately.</li>
                    <li>Children under 16 must be supervised by an adult at all times.</li>
                    <li>Violations of this code may result in suspension or termination of membership.</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Announcements Section -->
    <section id="announcements" class="flex items-center justify-center bg-gray-50" data-aos="fade-left" data-aos-duration="1000">
        <div class="max-w-5xl mx-auto px-6 text-center">
            <h2 class="text-5xl font-bold mb-8 text-gray-800">Announcements</h2>
            <p class="text-xl text-gray-600 mb-10">Stay updated with the latest news and events at our gym.</p>
            <div class="space-y-8">
                <div class="bg-white p-8 rounded-xl shadow-xl transition duration-300 ease-in-out hover:shadow-2xl">
                    <h3 class="text-2xl font-semibold mb-4">New Yoga Classes Starting Next Week</h3>
                    <p class="text-lg text-gray-600">Join our new beginner-friendly yoga sessions every Monday and Wednesday at 7 PM. Sign up at the front desk!</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-xl transition duration-300 ease-in-out hover:shadow-2xl">
                    <h3 class="text-2xl font-semibold mb-4">Gym Maintenance Closure</h3>
                    <p class="text-lg text-gray-600">The gym will be closed for maintenance on August 20th from 10 AM to 2 PM. We apologize for any inconvenience.</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-xl transition duration-300 ease-in-out hover:shadow-2xl">
                    <h3 class="text-2xl font-semibold mb-4">Membership Discount Promotion</h3>
                    <p class="text-lg text-gray-600">Refer a friend and get 20% off your next month's membership. Limited time offer!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="flex items-center justify-center bg-white" data-aos="fade-up" data-aos-duration="1000">
        <div class="max-w-5xl mx-auto px-6 text-center">
            <h2 class="text-5xl font-bold mb-8 text-gray-800">About Us</h2>
            <p class="text-xl text-gray-600 mb-8">Our gym is dedicated to helping you achieve your fitness goals with state-of-the-art facilities and expert trainers. We offer a welcoming environment for all fitness levels.</p>
            <p class="text-xl text-gray-600 mb-8">Founded in 2015, we have grown to serve over 1,000 members with personalized training programs and community events.</p>
            <p class="text-xl text-gray-600">Our mission is to promote health and wellness through accessible and enjoyable fitness experiences.</p>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="flex items-center justify-center bg-gray-50" data-aos="zoom-in" data-aos-duration="1000">
        <div class="max-w-5xl mx-auto px-6 text-center">
            <h2 class="text-5xl font-bold mb-8 text-gray-800">Our Services</h2>
            <p class="text-xl text-gray-600 mb-10">From personal training to group classes, we offer a wide range of services to support your fitness journey.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-xl transition duration-300 ease-in-out hover:shadow-2xl">
                    <h3 class="text-2xl font-semibold mb-4">Personal Training</h3>
                    <p class="text-lg text-gray-600">One-on-one sessions with certified trainers tailored to your goals.</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-xl transition duration-300 ease-in-out hover:shadow-2xl">
                    <h3 class="text-2xl font-semibold mb-4">Group Classes</h3>
                    <p class="text-lg text-gray-600">Fun and energetic classes like Zumba, Spin, and HIIT.</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-xl transition duration-300 ease-in-out hover:shadow-2xl">
                    <h3 class="text-2xl font-semibold mb-4">Nutrition Counseling</h3>
                    <p class="text-lg text-gray-600">Expert advice on diet and meal planning to complement your workouts.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="flex items-center justify-center bg-white" data-aos="fade-down" data-aos-duration="1000">
        <div class="max-w-5xl mx-auto px-6 text-center">
            <h2 class="text-5xl font-bold mb-8 text-gray-800">Contact Us</h2>
            <p class="text-xl text-gray-600 mb-10">Have questions? Reach out to our team for support or inquiries. We're here to help you every step of the way.</p>
            <?php
            $success = false;
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Simple form submission check (replace with your actual validation)
                if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['message'])) {
                    $success = true;
                }
            }
            ?>
            <form method="POST" action="#contact" class="max-w-lg mx-auto space-y-8">
                <input type="text" name="name" placeholder="Your Name" class="block w-full p-5 border border-gray-300 rounded-xl text-lg">
                <input type="email" name="email" placeholder="Your Email" class="block w-full p-5 border border-gray-300 rounded-xl text-lg">
                <textarea name="message" placeholder="Your Message" class="block w-full p-5 border border-gray-300 rounded-xl h-40 text-lg"></textarea>
                <button type="submit" class="w-full bg-blue-600 text-white p-5 rounded-xl hover:bg-blue-700 transition duration-300 ease-in-out font-semibold text-lg shadow-md">Send Message</button>
            </form>
            <?php if ($success): ?>
                <div class="mt-6 bg-green-50 border-l-4 border-green-500 p-6 rounded-lg text-green-700 text-xl font-semibold flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    Thank you! Your message has been sent successfully.
                </div>
            <?php endif; ?>
            <p class="mt-8 text-xl text-gray-600">Or call us at +1 (234) 567-890 or email info@gymmanagement.com</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="w-full bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Quick Links -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-3 text-lg">
                        <li><a href="#home" class="hover:text-blue-400 transition duration-200">Home</a></li>
                        <li><a href="#code-of-conduct" class="hover:text-blue-400 transition duration-200">Code of Conduct</a></li>
                        <li><a href="#announcements" class="hover:text-blue-400 transition duration-200">Announcements</a></li>
                        <li><a href="#about" class="hover:text-blue-400 transition duration-200">About</a></li>
                        <li><a href="#services" class="hover:text-blue-400 transition duration-200">Services</a></li>
                        <li><a href="#contact" class="hover:text-blue-400 transition duration-200">Contact</a></li>
                    </ul>
                </div>
                <!-- Quick Contact -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">Quick Contact</h3>
                    <ul class="space-y-3 text-lg">
                        <li>
                            <a href="tel:+1234567890" class="hover:text-blue-400 transition duration-200 flex items-center">
                                <i class="fas fa-phone-alt mr-3"></i> +1 (234) 567-890
                            </a>
                        </li>
                        <li>
                            <a href="mailto:info@gymmanagement.com" class="hover:text-blue-400 transition duration-200 flex items-center">
                                <i class="fas fa-envelope mr-3"></i> info@gymmanagement.com
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Operating Hours -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">Operating Hours</h3>
                    <ul class="space-y-3 text-lg">
                        <li>Mon - Fri: 6:00 AM - 10:00 PM</li>
                        <li>Sat: 8:00 AM - 8:00 PM</li>
                        <li>Sun: 10:00 AM - 6:00 PM</li>
                    </ul>
                </div>
            </div>
            <p class="mt-10 text-center text-lg">&copy; <?php echo date("Y"); ?> Gym Management. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init();
        // Show Home by default
        document.getElementById('home').style.display = 'flex';
        // Highlight active nav link based on URL hash
        function updateActiveNav() {
            const hash = window.location.hash;
            document.querySelectorAll('nav a').forEach(link => {
                link.classList.remove('text-blue-400');
                if (link.getAttribute('href') === hash) {
                    link.classList.add('text-blue-400');
                }
            });
        }
        window.addEventListener('hashchange', updateActiveNav);
        updateActiveNav(); // Run on page load
    </script>
</body>
</html>