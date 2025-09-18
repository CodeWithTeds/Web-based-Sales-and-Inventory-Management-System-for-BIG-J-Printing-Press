<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BIG J Printing Press - Sales and Inventory Management System</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#D62F1A',     // Chili Red
                        accent: '#BB822B',      // Dark Goldenrod
                        background: '#F8F8F5',  // Dirty White (off-white)
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8F8F5;
        }
        .btn-primary {
            background-color: #D62F1A;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #B82815;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .feature-card {
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        .accent-text {
            color: #BB822B;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="bg-white shadow-sm py-4">
        <div class="container mx-auto px-4 md:px-6 flex justify-between items-center">
            <div class="flex items-center">
                <span class="text-2xl font-bold text-primary">BIG J</span>
                <span class="ml-2 text-gray-700">Printing Press</span>
            </div>
            <div class="hidden md:flex space-x-6">
                <a href="#features" class="text-gray-600 hover:text-primary transition-colors">Features</a>
                <a href="#why-us" class="text-gray-600 hover:text-primary transition-colors">Why Us</a>
            </div>
            <a href="{{ route('login') }}" class="btn-primary px-5 py-2 rounded-md font-medium">Login</a>
        </div>
    </nav>

    {{ $slot }}

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="mb-8 md:mb-0">
                    <div class="flex items-center mb-4">
                        <span class="text-2xl font-bold text-primary">BIG J</span>
                        <span class="ml-2 text-gray-300">Printing Press</span>
                    </div>
                    <p class="text-gray-400 max-w-xs">
                        Providing quality printing services and now equipped with a powerful management system.
                    </p>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
                            <li><a href="#features" class="text-gray-400 hover:text-white transition-colors">Features</a></li>
                            <li><a href="#why-us" class="text-gray-400 hover:text-white transition-colors">Why Us</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Contact</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Contact</h3>
                        <ul class="space-y-2">
                            <li class="flex items-center">
                                <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                                <span class="text-gray-400">123 Printing Ave, Manila</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-phone text-primary mr-2"></i>
                                <span class="text-gray-400">+63 123 456 7890</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-envelope text-primary mr-2"></i>
                                <span class="text-gray-400">info@bigjprinting.com</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Follow Us</h3>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <i class="fab fa-facebook-f text-xl"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <i class="fab fa-twitter text-xl"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <i class="fab fa-instagram text-xl"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <i class="fab fa-linkedin-in text-xl"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; 2023 BIG J Printing Press. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript for smooth scrolling -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>