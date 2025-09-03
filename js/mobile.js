// Mobile Navigation and Touch Enhancements
document.addEventListener('DOMContentLoaded', function() {
    
    // Add mobile menu toggle to all pages
    addMobileMenuToggle();
    
    // Initialize mobile navigation
    initMobileNavigation();
    
    // Add touch enhancements
    addTouchEnhancements();
    
    // Make tables mobile-friendly
    makeMobileFriendlyTables();
    
    // Add swipe gestures for tables
    addSwipeGestures();
    
    // Optimize modals for mobile
    optimizeModalsForMobile();
    
    // Add scroll to top functionality
    addScrollToTop();
    
    // Add loading states to forms
    addFormLoadingStates();
});

function addMobileMenuToggle() {
    // Find the navigation container
    const navContainer = document.querySelector('.nav-container');
    const navMenu = document.querySelector('.nav-menu');
    
    if (navContainer && navMenu) {
        // Create mobile menu toggle button
        const toggleButton = document.createElement('button');
        toggleButton.className = 'mobile-menu-toggle';
        toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
        toggleButton.setAttribute('aria-label', 'Toggle navigation menu');
        
        // Insert the toggle button
        navContainer.appendChild(toggleButton);
    }
}

function initMobileNavigation() {
    const toggleButton = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (toggleButton && navMenu) {
        toggleButton.addEventListener('click', function() {
            const isActive = navMenu.classList.contains('active');
            
            if (isActive) {
                // Close menu
                navMenu.classList.remove('active');
                toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
                document.body.style.overflow = '';
            } else {
                // Open menu
                navMenu.classList.add('active');
                toggleButton.innerHTML = '<i class="fas fa-times"></i>';
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            }
        });
        
        // Close menu when clicking on a link
        const navLinks = navMenu.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('active');
                toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
                document.body.style.overflow = '';
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navMenu.contains(e.target) && !toggleButton.contains(e.target)) {
                navMenu.classList.remove('active');
                toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
                document.body.style.overflow = '';
            }
        });
        
        // Close menu on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                navMenu.classList.remove('active');
                toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
                document.body.style.overflow = '';
            }
        });
    }
}

function addTouchEnhancements() {
    // Add touch feedback to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        // Add touch start and end events for better feedback
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });
        
        button.addEventListener('touchend', function() {
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
        
        // Prevent double-tap zoom on buttons
        button.addEventListener('touchend', function(e) {
            e.preventDefault();
            e.target.click();
        });
    });
    
    // Add touch feedback to cards
    const cards = document.querySelectorAll('.stat-card, .detail-card');
    cards.forEach(card => {
        card.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });
        
        card.addEventListener('touchend', function() {
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
}

function makeMobileFriendlyTables() {
    const tables = document.querySelectorAll('.table table');
    
    tables.forEach(table => {
        if (window.innerWidth <= 480) {
            // Add responsive data attributes for mobile stacking
            const headers = table.querySelectorAll('th');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach((cell, index) => {
                    if (headers[index]) {
                        cell.setAttribute('data-label', headers[index].textContent.trim());
                    }
                });
            });
            
            // Add mobile-friendly classes
            table.classList.add('table-stack');
        }
    });
}

function addSwipeGestures() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(tableContainer => {
        let startX = 0;
        let scrollLeft = 0;
        let isDown = false;
        
        tableContainer.addEventListener('touchstart', function(e) {
            isDown = true;
            startX = e.touches[0].pageX - tableContainer.offsetLeft;
            scrollLeft = tableContainer.scrollLeft;
        });
        
        tableContainer.addEventListener('touchmove', function(e) {
            if (!isDown) return;
            e.preventDefault();
            const x = e.touches[0].pageX - tableContainer.offsetLeft;
            const walk = (x - startX) * 2;
            tableContainer.scrollLeft = scrollLeft - walk;
        });
        
        tableContainer.addEventListener('touchend', function() {
            isDown = false;
        });
    });
}

function optimizeModalsForMobile() {
    const modals = document.querySelectorAll('[id*="Modal"]');
    
    modals.forEach(modal => {
        const modalContent = modal.querySelector('div');
        if (modalContent) {
            // Add touch scrolling for modal content
            modalContent.style.webkitOverflowScrolling = 'touch';
            modalContent.style.maxHeight = '90vh';
            modalContent.style.overflowY = 'auto';
            
            // Close modal on backdrop touch (mobile-friendly)
            modal.addEventListener('touchstart', function(e) {
                if (e.target === modal) {
                    e.preventDefault();
                    // Close modal logic
                    if (typeof hideCreateStudentModal === 'function') {
                        hideCreateStudentModal();
                    }
                }
            });
        }
    });
}

function addScrollToTop() {
    // Create scroll to top button
    const scrollButton = document.createElement('button');
    scrollButton.innerHTML = '<i class="fas fa-chevron-up"></i>';
    scrollButton.className = 'scroll-to-top';
    scrollButton.setAttribute('aria-label', 'Scroll to top');
    
    document.body.appendChild(scrollButton);
    
    // Show/hide scroll button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollButton.classList.add('show');
        } else {
            scrollButton.classList.remove('show');
        }
    });
    
    // Smooth scroll to top
    scrollButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

function addFormLoadingStates() {
    // Add loading states to forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                showLoadingState(submitButton);
            }
        });
    });
}

function showLoadingState(element) {
    if (element) {
        element.style.opacity = '0.6';
        element.style.pointerEvents = 'none';
        
        const originalText = element.innerHTML;
        element.setAttribute('data-original-text', originalText);
        
        const icon = element.querySelector('i');
        if (icon) {
            icon.className = 'fas fa-spinner fa-spin';
        } else {
            element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        }
    }
}

function hideLoadingState(element) {
    if (element) {
        element.style.opacity = '';
        element.style.pointerEvents = '';
        
        const originalText = element.getAttribute('data-original-text');
        if (originalText) {
            element.innerHTML = originalText;
        }
    }
}

// Handle orientation change
window.addEventListener('orientationchange', function() {
    // Close mobile menu on orientation change
    const navMenu = document.querySelector('.nav-menu');
    const toggleButton = document.querySelector('.mobile-menu-toggle');
    
    if (navMenu && toggleButton) {
        setTimeout(() => {
            navMenu.classList.remove('active');
            toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
            document.body.style.overflow = '';
        }, 100);
    }
    
    // Refresh table responsiveness
    setTimeout(() => {
        makeMobileFriendlyTables();
    }, 300);
});

// Prevent zoom on input focus (iOS)
document.addEventListener('touchstart', function() {
    const viewportMeta = document.querySelector('meta[name="viewport"]');
    if (viewportMeta) {
        viewportMeta.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';
    }
});

// Re-enable zoom after input blur
document.addEventListener('touchend', function() {
    setTimeout(() => {
        const viewportMeta = document.querySelector('meta[name="viewport"]');
        if (viewportMeta) {
            viewportMeta.content = 'width=device-width, initial-scale=1.0, user-scalable=yes';
        }
    }, 500);
});

// Performance optimization for mobile
if ('ontouchstart' in window) {
    // Add class for touch devices
    document.documentElement.classList.add('touch-device');
}

// Add confirmation dialogs for delete actions
function addMobileConfirmations() {
    const deleteLinks = document.querySelectorAll('a[href*="delete"], .btn-danger');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.href && this.href.includes('delete')) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this item?')) {
                    window.location.href = this.href;
                }
            }
        });
    });
}

// Initialize mobile confirmations
addMobileConfirmations();

// Viewport height fix for mobile browsers
function setViewportHeight() {
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
}

setViewportHeight();
window.addEventListener('resize', setViewportHeight);
window.addEventListener('orientationchange', () => {
    setTimeout(setViewportHeight, 100);
});

// Add pull-to-refresh hint for mobile (optional)
if ('ontouchstart' in window && window.location.pathname.includes('index.php')) {
    const container = document.querySelector('.container');
    if (container) {
        let startY = 0;
        let pullDistance = 0;
        const maxPullDistance = 100;
        
        container.addEventListener('touchstart', function(e) {
            if (window.pageYOffset === 0) {
                startY = e.touches[0].pageY;
            }
        });
        
        container.addEventListener('touchmove', function(e) {
            if (window.pageYOffset === 0 && startY > 0) {
                pullDistance = e.touches[0].pageY - startY;
                
                if (pullDistance > 0 && pullDistance < maxPullDistance) {
                    e.preventDefault();
                    // Add visual feedback if desired
                }
            }
        });
        
        container.addEventListener('touchend', function() {
            if (pullDistance > 60) {
                // Refresh page
                window.location.reload();
            }
            startY = 0;
            pullDistance = 0;
        });
    }
}

// Enhanced table scrolling indicator
function addTableScrollIndicators() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(tableContainer => {
        const table = tableContainer.querySelector('table');
        if (table && table.scrollWidth > tableContainer.clientWidth) {
            // Add scroll indicators
            const leftIndicator = document.createElement('div');
            leftIndicator.className = 'scroll-indicator scroll-left';
            leftIndicator.innerHTML = '<i class="fas fa-chevron-left"></i>';
            
            const rightIndicator = document.createElement('div');
            rightIndicator.className = 'scroll-indicator scroll-right';
            rightIndicator.innerHTML = '<i class="fas fa-chevron-right"></i>';
            
            tableContainer.appendChild(leftIndicator);
            tableContainer.appendChild(rightIndicator);
            
            // Add CSS for indicators
            const indicatorCSS = `
                .scroll-indicator {
                    position: absolute;
                    top: 50%;
                    transform: translateY(-50%);
                    background: rgba(0, 0, 0, 0.7);
                    color: white;
                    padding: 0.5rem;
                    border-radius: 50%;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                    pointer-events: none;
                    z-index: 10;
                }
                
                .scroll-left {
                    left: 10px;
                }
                
                .scroll-right {
                    right: 10px;
                }
                
                .table {
                    position: relative;
                }
            `;
            
            if (!document.getElementById('scroll-indicator-styles')) {
                const style = document.createElement('style');
                style.id = 'scroll-indicator-styles';
                style.textContent = indicatorCSS;
                document.head.appendChild(style);
            }
            
            // Show/hide indicators based on scroll position
            tableContainer.addEventListener('scroll', function() {
                const scrollLeft = this.scrollLeft;
                const maxScroll = this.scrollWidth - this.clientWidth;
                
                leftIndicator.style.opacity = scrollLeft > 10 ? '0.7' : '0';
                rightIndicator.style.opacity = scrollLeft < maxScroll - 10 ? '0.7' : '0';
            });
            
            // Initial state
            rightIndicator.style.opacity = '0.7';
        }
    });
}

// Initialize table scroll indicators
addTableScrollIndicators();

// Add haptic feedback for supported devices
function addHapticFeedback() {
    if ('vibrate' in navigator) {
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                navigator.vibrate(50); // Short vibration
            });
        });
    }
}

// Initialize haptic feedback
addHapticFeedback();

// Mobile-specific modal enhancements
window.showCreateStudentModal = function(userId, firstName, lastName) {
    document.getElementById('modalUserId').value = userId;
    document.getElementById('modalUserName').textContent = firstName + ' ' + lastName;
    document.getElementById('createStudentModal').style.display = 'block';
    
    // Prevent background scrolling on mobile
    document.body.style.overflow = 'hidden';
};

window.hideCreateStudentModal = function() {
    document.getElementById('createStudentModal').style.display = 'none';
    
    // Re-enable background scrolling
    document.body.style.overflow = '';
};

// Enhanced error handling for mobile
window.addEventListener('error', function(e) {
    console.error('Mobile error:', e.error);
    
    // Show user-friendly error message on mobile
    if ('ontouchstart' in window) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-error mobile-error';
        errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Something went wrong. Please try again.';
        errorDiv.style.position = 'fixed';
        errorDiv.style.top = '20px';
        errorDiv.style.left = '10px';
        errorDiv.style.right = '10px';
        errorDiv.style.zIndex = '9999';
        
        document.body.appendChild(errorDiv);
        
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
});

// Initialize all mobile features
console.log('Mobile enhancements loaded successfully!');


// Language Switcher JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initLanguageSwitcher();
    addLanguageSwitcherToNav();
});

function initLanguageSwitcher() {
    // Close language dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const languageDropdown = document.querySelector('.language-dropdown');
        const languageOptions = document.getElementById('languageOptions');
        
        if (languageDropdown && !languageDropdown.contains(e.target)) {
            languageOptions?.classList.remove('show');
            document.querySelector('.language-toggle')?.classList.remove('active');
        }
    });
    
    // Handle language option clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('language-option') || e.target.closest('.language-option')) {
            const option = e.target.classList.contains('language-option') ? e.target : e.target.closest('.language-option');
            
            // Add loading state
            showLanguageChangeLoading();
            
            // Small delay for better UX
            setTimeout(() => {
                window.location.href = option.href;
            }, 200);
        }
    });
}

function toggleLanguageDropdown() {
    const languageOptions = document.getElementById('languageOptions');
    const languageToggle = document.querySelector('.language-toggle');
    
    if (languageOptions && languageToggle) {
        const isOpen = languageOptions.classList.contains('show');
        
        if (isOpen) {
            languageOptions.classList.remove('show');
            languageToggle.classList.remove('active');
        } else {
            languageOptions.classList.add('show');
            languageToggle.classList.add('active');
        }
    }
}

function addLanguageSwitcherToNav() {
    const navContainer = document.querySelector('.nav-container');
    if (navContainer) {
        // Check if language switcher already exists
        if (!navContainer.querySelector('.language-switcher')) {
            // Create language switcher HTML (this would normally be generated by PHP)
            const languageSwitcher = createLanguageSwitcherHTML();
            
            // Insert before mobile menu toggle, or at the end
            const mobileToggle = navContainer.querySelector('.mobile-menu-toggle');
            if (mobileToggle) {
                navContainer.insertBefore(languageSwitcher, mobileToggle);
            } else {
                navContainer.appendChild(languageSwitcher);
            }
        }
    }
}

function createLanguageSwitcherHTML() {
    // This is a fallback - normally this HTML would be generated by PHP
    const div = document.createElement('div');
    div.className = 'language-switcher';
    
    // Get current language from page or default to English
    const currentLang = document.documentElement.lang || 'en';
    const currentLangName = currentLang === 'fi' ? 'Suomi' : 'English';
    const currentFlag = currentLang === 'fi' ? '🇫🇮' : '🇺🇸';
    
    div.innerHTML = `
        <div class="language-dropdown">
            <button class="language-toggle" onclick="toggleLanguageDropdown()">
                <i class="fas fa-globe"></i> ${currentFlag} ${currentLangName}
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="language-options" id="languageOptions">
                <a href="?lang=en" class="language-option ${currentLang === 'en' ? 'active' : ''}">
                    <span class="flag">🇺🇸</span> English
                </a>
                <a href="?lang=fi" class="language-option ${currentLang === 'fi' ? 'active' : ''}">
                    <span class="flag">🇫🇮</span> Suomi
                </a>
            </div>
        </div>
    `;
    
    return div;
}

function showLanguageChangeLoading() {
    // Create loading overlay
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'language-change-loading';
    loadingOverlay.innerHTML = `
        <div class="loading-content">
            <i class="fas fa-globe fa-spin"></i>
            <p>Changing language...</p>
        </div>
    `;
    
    // Add CSS for loading overlay
    const style = document.createElement('style');
    style.textContent = `
        .language-change-loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(102, 126, 234, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        }
        
        .loading-content {
            text-align: center;
            color: white;
            font-size: 1.1rem;
        }
        
        .loading-content i {
            font-size: 2rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .loading-content p {
            margin: 0;
            font-weight: 500;
        }
    `;
    
    document.head.appendChild(style);
    document.body.appendChild(loadingOverlay);
    
    // Prevent scrolling
    document.body.style.overflow = 'hidden';
}

// Handle page transition after language change
function handleLanguageTransition() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('lang')) {
        document.body.classList.add('page-transition');
        
        setTimeout(() => {
            document.body.classList.add('loaded');
        }, 100);
        
        // Remove lang parameter from URL after processing
        urlParams.delete('lang');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }
}

// Initialize page transition
handleLanguageTransition();

// Keyboard navigation for language switcher
document.addEventListener('keydown', function(e) {
    const languageOptions = document.getElementById('languageOptions');
    const languageToggle = document.querySelector('.language-toggle');
    
    if (!languageOptions || !languageToggle) return;
    
    if (e.key === 'Escape' && languageOptions.classList.contains('show')) {
        languageOptions.classList.remove('show');
        languageToggle.classList.remove('active');
        languageToggle.focus();
    }
    
    if (e.key === 'Enter' && document.activeElement === languageToggle) {
        toggleLanguageDropdown();
    }
});

// ARIA accessibility improvements
function improveLanguageSwitcherAccessibility() {
    const languageToggle = document.querySelector('.language-toggle');
    const languageOptions = document.getElementById('languageOptions');
    
    if (languageToggle && languageOptions) {
        languageToggle.setAttribute('aria-haspopup', 'true');
        languageToggle.setAttribute('aria-expanded', 'false');
        languageToggle.setAttribute('aria-label', 'Choose language');
        
        languageOptions.setAttribute('role', 'menu');
        
        const options = languageOptions.querySelectorAll('.language-option');
        options.forEach(option => {
            option.setAttribute('role', 'menuitem');
        });
        
        // Update aria-expanded when dropdown opens/closes
        const originalToggle = window.toggleLanguageDropdown;
        window.toggleLanguageDropdown = function() {
            originalToggle();
            const isOpen = languageOptions.classList.contains('show');
            languageToggle.setAttribute('aria-expanded', isOpen.toString());
        };
    }
}

// Initialize accessibility improvements
setTimeout(improveLanguageSwitcherAccessibility, 100);

// Auto-detect browser language on first visit
function autoDetectLanguage() {
    // Only run if no language is set and not already detected
    if (!sessionStorage.getItem('languageDetected')) {
        const browserLang = navigator.language || navigator.languages[0];
        
        if (browserLang.startsWith('fi')) {
            // Redirect to Finnish if Finnish browser and not already on Finnish page
            const currentLang = document.documentElement.lang;
            if (currentLang !== 'fi') {
                sessionStorage.setItem('languageDetected', 'true');
                window.location.href = window.location.href + '?lang=fi';
            }
        }
        
        sessionStorage.setItem('languageDetected', 'true');
    }
}

// Run auto-detection (uncomment if you want this feature)
// autoDetectLanguage();