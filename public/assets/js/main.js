document.addEventListener('DOMContentLoaded', () => {
    const heroLogo = document.querySelector('.hero-logo');
    const navLogo = document.querySelector('.nav-logo');
    const mainNav = document.querySelector('.main-nav');
    const menuToggle = document.querySelector('.menu-toggle');
    const menuClose = document.querySelector('.menu-close');
    const menuOverlay = document.querySelector('.menu-overlay');
    const menuBackdrop = document.querySelector('.menu-backdrop');
    const menuItems = document.querySelectorAll('.menu-category');
    const shopSection = document.querySelector('.shop-section');
    const shopImages = document.querySelectorAll('.shop-images img');
    const prevButton = document.querySelector('.nav-arrow.prev');
    const nextButton = document.querySelector('.nav-arrow.next');
    const shopContent = document.querySelector('.shop-content');
    let currentImageIndex = 0;
    let lastScrollTop = 0;
    let ticking = false;
    let isFirstImage = true;

    // Add index to menu items for staggered animation
    menuItems.forEach((item, index) => {
        item.style.setProperty('--index', index);
    });

    // Shop Image Navigation with content position
    function showImage(index) {
        shopImages.forEach(img => img.classList.remove('active'));
        currentImageIndex = (index + shopImages.length) % shopImages.length;
        shopImages[currentImageIndex].classList.add('active');
        
        // Update content position based on image index
        if (currentImageIndex === 0) {
            shopContent.classList.remove('bottom');
            isFirstImage = true;
        } else {
            shopContent.classList.add('bottom');
            isFirstImage = false;
        }
    }

    // Handle mouse movement for underline
    shopSection.addEventListener('mousemove', (e) => {
        const { top, height } = shopSection.getBoundingClientRect();
        const mouseY = e.clientY - top;
        const percentage = (mouseY / height) * 100;
        
        if (percentage > 60) { // Mouse is in bottom 40%
            shopSection.classList.add('show-underline');
        } else {
            shopSection.classList.remove('show-underline');
        }
    });

    // Remove underline when mouse leaves section
    shopSection.addEventListener('mouseleave', () => {
        shopSection.classList.remove('show-underline');
    });

    // Handle scroll events
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;

        if (!ticking) {
            window.requestAnimationFrame(() => {
                if (currentScroll > 50) {
                    mainNav.classList.add('scrolled');
                    heroLogo.classList.add('scrolled');
                    navLogo.classList.add('visible');
                } else {
                    mainNav.classList.remove('scrolled');
                    heroLogo.classList.remove('scrolled');
                    navLogo.classList.remove('visible');
                }

                // Check if shop section is in view
                const rect = shopSection.getBoundingClientRect();
                const isInView = (rect.top <= window.innerHeight / 2) && (rect.bottom >= window.innerHeight / 2);
                
                if (isInView) {
                    shopSection.classList.add('active');
                } else {
                    shopSection.classList.remove('active');
                    // Reset to first image when out of view
                    if (!isFirstImage) {
                        showImage(0);
                    }
                }

                lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
                ticking = false;
            });

            ticking = true;
        }
    });

    // Navigation buttons
    prevButton.addEventListener('click', () => {
        showImage(currentImageIndex - 1);
    });

    nextButton.addEventListener('click', () => {
        showImage(currentImageIndex + 1);
    });

    // Menu Toggle Function
    function toggleMenu(show) {
        if (show) {
            menuOverlay.classList.add('active');
            menuBackdrop.classList.add('active');
            menuClose.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            menuItems.forEach((item, index) => {
                item.style.transitionDelay = `${index * 0.1}s`;
            });
        } else {
            menuOverlay.classList.remove('active');
            menuBackdrop.classList.remove('active');
            menuClose.classList.remove('active');
            document.body.style.overflow = '';
            
            menuItems.forEach(item => {
                item.style.transitionDelay = '0s';
            });
        }
    }

    // Toggle menu
    menuToggle.addEventListener('click', () => toggleMenu(true));
    menuClose.addEventListener('click', () => toggleMenu(false));
    menuBackdrop.addEventListener('click', () => toggleMenu(false));

    // Close menu with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            toggleMenu(false);
        }
    });

    // Initialize Swiper for announcement bar
    new Swiper('.announcement-swiper', {
        direction: 'horizontal',
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
    });

    // Search toggle
    const searchToggle = document.querySelector('.search-toggle');
    if (searchToggle) {
        searchToggle.addEventListener('click', () => {
            document.body.classList.toggle('search-open');
        });
    }

    // Products Page Functionality
    const urlParams = new URLSearchParams(window.location.search);
    const category = urlParams.get('category');
    
    if (category && document.querySelector('.products-header h1')) {
        // Update page title based on category
        const categoryTitle = document.querySelector('.products-header h1');
        categoryTitle.textContent = category.charAt(0).toUpperCase() + category.slice(1).replace('-', ' ');
        
        // Update document title
        document.title = `${categoryTitle.textContent} - MIZA`;
    }

    // Sort functionality
    const sortSelect = document.querySelector('.sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function(e) {
            const value = e.target.value;
            // Here you would typically make an API call to sort products
            console.log('Sorting by:', value);
        });
    }

    // Pagination functionality
    const paginationLinks = document.querySelectorAll('.pagination a');
    if (paginationLinks) {
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.textContent;
                // Here you would typically make an API call to fetch the next page
                console.log('Navigate to page:', page);
            });
        });
    }
}); 