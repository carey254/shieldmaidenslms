/**
 * Mobile Touch Interactions Utility
 * Provides touch-friendly interactions and gesture support for mobile devices
 */

class MobileInteractions {
  constructor() {
    this.isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    this.isMobile = window.innerWidth <= 767;
    this.touchStartX = 0;
    this.touchStartY = 0;
    this.touchEndX = 0;
    this.touchEndY = 0;
    
    this.init();
  }

  init() {
    if (!this.isTouchDevice) return;

    // Add touch-friendly classes to body
    document.body.classList.add('touch-device');
    
    // Prevent double-tap zoom on buttons
    this.preventDoubleTapZoom();
    
    // Add swipe gestures for navigation
    this.addSwipeGestures();
    
    // Add touch feedback
    this.addTouchFeedback();
    
    // Handle viewport issues
    this.handleViewport();
  }

  preventDoubleTapZoom() {
    let lastTouchEnd = 0;
    
    document.addEventListener('touchend', (e) => {
      const now = Date.now();
      if (now - lastTouchEnd <= 300) {
        e.preventDefault();
      }
      lastTouchEnd = now;
    }, { passive: false });
  }

  addSwipeGestures() {
    let swipeElement = document.querySelector('.student-dashboard, .admin-courses, .courses-page');
    
    if (!swipeElement) return;

    swipeElement.addEventListener('touchstart', (e) => {
      this.touchStartX = e.changedTouches[0].screenX;
      this.touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    swipeElement.addEventListener('touchend', (e) => {
      this.touchEndX = e.changedTouches[0].screenX;
      this.touchEndY = e.changedTouches[0].screenY;
      this.handleSwipe();
    }, { passive: true });
  }

  handleSwipe() {
    const deltaX = this.touchEndX - this.touchStartX;
    const deltaY = this.touchEndY - this.touchStartY;
    const minSwipeDistance = 50;

    // Horizontal swipe
    if (Math.abs(deltaX) > Math.abs(deltaY)) {
      if (deltaX > minSwipeDistance) {
        this.onSwipeRight();
      } else if (deltaX < -minSwipeDistance) {
        this.onSwipeLeft();
      }
    }
    // Vertical swipe
    else {
      if (deltaY > minSwipeDistance) {
        this.onSwipeDown();
      } else if (deltaY < -minSwipeDistance) {
        this.onSwipeUp();
      }
    }
  }

  onSwipeRight() {
    // Open sidebar
    const sidebar = document.querySelector('.left-sidebar');
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    
    if (sidebar && !sidebar.classList.contains('mobile-open')) {
      const event = new CustomEvent('openMobileSidebar');
      document.dispatchEvent(event);
    }
  }

  onSwipeLeft() {
    // Close sidebar
    const sidebar = document.querySelector('.left-sidebar');
    const mobileMenu = document.querySelector('.navbar-menu');
    
    if (sidebar && sidebar.classList.contains('mobile-open')) {
      const event = new CustomEvent('closeMobileSidebar');
      document.dispatchEvent(event);
    }
    
    if (mobileMenu && mobileMenu.classList.contains('mobile-open')) {
      mobileMenu.classList.remove('mobile-open');
    }
  }

  onSwipeUp() {
    // Can be used for pull-to-refresh or other actions
  }

  onSwipeDown() {
    // Can be used for pull-to-refresh or other actions
  }

  addTouchFeedback() {
    // Add ripple effect to buttons
    const style = document.createElement('style');
    style.textContent = `
      .touch-device .btn {
        position: relative;
        overflow: hidden;
      }
      
      .touch-device .btn::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
        pointer-events: none;
      }
      
      .touch-device .btn:active::after {
        width: 300px;
        height: 300px;
      }
      
      .touch-device .course-card,
      .touch-device .stat-card,
      .touch-device .module-card {
        transition: transform 0.1s ease;
      }
      
      .touch-device .course-card:active,
      .touch-device .stat-card:active,
      .touch-device .module-card:active {
        transform: scale(0.98);
      }
    `;
    document.head.appendChild(style);
  }

  handleViewport() {
    // Prevent zoom on input focus
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
      input.addEventListener('focus', () => {
        if (this.isMobile) {
          document.querySelector('meta[name="viewport"]').setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0');
        }
      });
      
      input.addEventListener('blur', () => {
        document.querySelector('meta[name="viewport"]').setAttribute('content', 'width=device-width, initial-scale=1.0');
      });
    });
  }

  // Add haptic feedback for supported devices
  hapticFeedback(type = 'light') {
    if ('vibrate' in navigator) {
      switch (type) {
        case 'light':
          navigator.vibrate(10);
          break;
        case 'medium':
          navigator.vibrate(20);
          break;
        case 'heavy':
          navigator.vibrate(30);
          break;
        case 'success':
          navigator.vibrate([10, 50, 10]);
          break;
        case 'error':
          navigator.vibrate([50, 30, 50, 30, 50]);
          break;
      }
    }
  }

  // Add scroll snap for mobile
  enableScrollSnap() {
    const style = document.createElement('style');
    style.textContent = `
      @media (max-width: 767px) {
        .courses-grid {
          scroll-snap-type: x mandatory;
          -webkit-overflow-scrolling: touch;
        }
        
        .course-card {
          scroll-snap-align: start;
        }
        
        .module-cards {
          scroll-snap-type: x mandatory;
        }
        
        .module-card {
          scroll-snap-align: start;
        }
      }
    `;
    document.head.appendChild(style);
  }
}

// Initialize mobile interactions
const mobileInteractions = new MobileInteractions();

export default mobileInteractions;
