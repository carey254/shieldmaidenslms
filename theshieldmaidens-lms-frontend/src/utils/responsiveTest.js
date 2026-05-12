/**
 * Responsive Design Testing Utility
 * Helps test and verify responsive design across different screen sizes
 */

class ResponsiveTest {
  constructor() {
    this.breakpoints = {
      mobile: 480,
      tablet: 768,
      desktop: 1024,
      large: 1200
    };
    
    this.currentBreakpoint = this.getCurrentBreakpoint();
    this.testResults = [];
    
    this.init();
  }

  init() {
    this.createTestPanel();
    this.addResizeListener();
    this.runTests();
  }

  getCurrentBreakpoint() {
    const width = window.innerWidth;
    
    if (width <= this.breakpoints.mobile) return 'mobile';
    if (width <= this.breakpoints.tablet) return 'tablet';
    if (width <= this.breakpoints.desktop) return 'desktop';
    return 'large';
  }

  createTestPanel() {
    // Create test panel UI
    const panel = document.createElement('div');
    panel.id = 'responsive-test-panel';
    panel.innerHTML = `
      <div class="test-panel-header">
        <h3>Responsive Test Panel</h3>
        <button id="toggle-test-panel">Hide</button>
      </div>
      <div class="test-panel-content">
        <div class="current-info">
          <p>Current Size: <span id="current-size">${window.innerWidth}x${window.innerHeight}</span></p>
          <p>Current Breakpoint: <span id="current-breakpoint">${this.currentBreakpoint}</span></p>
        </div>
        <div class="test-results">
          <h4>Test Results:</h4>
          <div id="test-list"></div>
        </div>
        <div class="test-controls">
          <button id="run-tests">Run Tests</button>
          <button id="export-results">Export Results</button>
        </div>
      </div>
    `;
    
    // Add styles
    const style = document.createElement('style');
    style.textContent = `
      #responsive-test-panel {
        position: fixed;
        top: 20px;
        right: 20px;
        width: 300px;
        background: white;
        border: 2px solid #333;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-family: Arial, sans-serif;
        font-size: 14px;
      }
      
      .test-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        background: #333;
        color: white;
        border-radius: 6px 6px 0 0;
      }
      
      .test-panel-header h3 {
        margin: 0;
        font-size: 16px;
      }
      
      .test-panel-header button {
        background: white;
        color: #333;
        border: none;
        padding: 4px 8px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
      }
      
      .test-panel-content {
        padding: 15px;
      }
      
      .current-info {
        margin-bottom: 15px;
        padding: 10px;
        background: #f5f5f5;
        border-radius: 4px;
      }
      
      .current-info p {
        margin: 5px 0;
      }
      
      .current-info span {
        font-weight: bold;
        color: #333;
      }
      
      .test-results h4 {
        margin: 0 0 10px 0;
        font-size: 14px;
      }
      
      .test-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px;
        margin: 5px 0;
        border-radius: 4px;
        font-size: 12px;
      }
      
      .test-item.pass {
        background: #d4edda;
        color: #155724;
      }
      
      .test-item.fail {
        background: #f8d7da;
        color: #721c24;
      }
      
      .test-controls {
        margin-top: 15px;
        display: flex;
        gap: 10px;
      }
      
      .test-controls button {
        flex: 1;
        padding: 8px;
        border: 1px solid #333;
        background: white;
        color: #333;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
      }
      
      .test-controls button:hover {
        background: #333;
        color: white;
      }
      
      @media (max-width: 767px) {
        #responsive-test-panel {
          width: 250px;
          right: 10px;
          top: 10px;
          font-size: 12px;
        }
        
        .test-panel-header h3 {
          font-size: 14px;
        }
        
        .test-panel-content {
          padding: 10px;
        }
      }
    `;
    
    document.head.appendChild(style);
    document.body.appendChild(panel);
    
    // Add event listeners
    document.getElementById('toggle-test-panel').addEventListener('click', () => {
      panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    });
    
    document.getElementById('run-tests').addEventListener('click', () => {
      this.runTests();
    });
    
    document.getElementById('export-results').addEventListener('click', () => {
      this.exportResults();
    });
  }

  addResizeListener() {
    window.addEventListener('resize', () => {
      this.currentBreakpoint = this.getCurrentBreakpoint();
      this.updateCurrentInfo();
      this.runTests();
    });
  }

  updateCurrentInfo() {
    document.getElementById('current-size').textContent = `${window.innerWidth}x${window.innerHeight}`;
    document.getElementById('current-breakpoint').textContent = this.currentBreakpoint;
  }

  runTests() {
    this.testResults = [];
    
    // Test mobile menu toggle
    this.testMobileMenuToggle();
    
    // Test responsive grid
    this.testResponsiveGrid();
    
    // Test sidebar behavior
    this.testSidebarBehavior();
    
    // Test touch interactions
    this.testTouchInteractions();
    
    // Test viewport meta tag
    this.testViewportMeta();
    
    // Test font sizes
    this.testFontSizes();
    
    // Test button sizes
    this.testButtonSizes();
    
    this.displayResults();
  }

  testMobileMenuToggle() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const isMobile = this.currentBreakpoint === 'mobile';
    
    if (isMobile && mobileMenuToggle) {
      this.addTestResult('Mobile Menu Toggle', true, 'Mobile menu toggle is visible on mobile');
    } else if (!isMobile && (!mobileMenuToggle || mobileMenuToggle.style.display === 'none')) {
      this.addTestResult('Mobile Menu Toggle', true, 'Mobile menu toggle is hidden on desktop');
    } else {
      this.addTestResult('Mobile Menu Toggle', false, 'Mobile menu toggle visibility issue');
    }
  }

  testResponsiveGrid() {
    const coursesGrid = document.querySelector('.courses-grid');
    const moduleCards = document.querySelector('.module-cards');
    
    if (coursesGrid) {
      const gridStyle = window.getComputedStyle(coursesGrid);
      const columns = gridStyle.gridTemplateColumns;
      
      if (this.currentBreakpoint === 'mobile' && columns.includes('1fr')) {
        this.addTestResult('Courses Grid', true, 'Courses grid is single column on mobile');
      } else if (this.currentBreakpoint !== 'mobile' && !columns.includes('1fr')) {
        this.addTestResult('Courses Grid', true, 'Courses grid is multi-column on larger screens');
      } else {
        this.addTestResult('Courses Grid', false, 'Courses grid layout issue');
      }
    }
    
    if (moduleCards) {
      const gridStyle = window.getComputedStyle(moduleCards);
      const columns = gridStyle.gridTemplateColumns;
      
      if (this.currentBreakpoint === 'mobile' && columns.includes('1fr')) {
        this.addTestResult('Module Cards Grid', true, 'Module cards grid is single column on mobile');
      } else if (this.currentBreakpoint !== 'mobile' && !columns.includes('1fr')) {
        this.addTestResult('Module Cards Grid', true, 'Module cards grid is multi-column on larger screens');
      } else {
        this.addTestResult('Module Cards Grid', false, 'Module cards grid layout issue');
      }
    }
  }

  testSidebarBehavior() {
    const sidebar = document.querySelector('.left-sidebar');
    
    if (sidebar) {
      const sidebarStyle = window.getComputedStyle(sidebar);
      const transform = sidebarStyle.transform;
      
      if (this.currentBreakpoint === 'mobile') {
        if (transform.includes('translateX(-100%)') || !sidebar.classList.contains('mobile-open')) {
          this.addTestResult('Mobile Sidebar', true, 'Sidebar is hidden on mobile');
        } else {
          this.addTestResult('Mobile Sidebar', false, 'Sidebar should be hidden on mobile');
        }
      } else {
        this.addTestResult('Desktop Sidebar', true, 'Sidebar is visible on desktop');
      }
    }
  }

  testTouchInteractions() {
    const hasTouchClass = document.body.classList.contains('touch-device');
    const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    
    if (isTouchDevice && hasTouchClass) {
      this.addTestResult('Touch Interactions', true, 'Touch device class is applied');
    } else if (!isTouchDevice && !hasTouchClass) {
      this.addTestResult('Touch Interactions', true, 'Touch device class is not applied on non-touch devices');
    } else {
      this.addTestResult('Touch Interactions', false, 'Touch device class issue');
    }
  }

  testViewportMeta() {
    const viewportMeta = document.querySelector('meta[name="viewport"]');
    
    if (viewportMeta) {
      const content = viewportMeta.getAttribute('content');
      if (content.includes('width=device-width')) {
        this.addTestResult('Viewport Meta', true, 'Viewport meta tag is properly set');
      } else {
        this.addTestResult('Viewport Meta', false, 'Viewport meta tag missing width=device-width');
      }
    } else {
      this.addTestResult('Viewport Meta', false, 'Viewport meta tag is missing');
    }
  }

  testFontSizes() {
    const body = document.body;
    const bodyStyle = window.getComputedStyle(body);
    const fontSize = parseFloat(bodyStyle.fontSize);
    
    if (this.currentBreakpoint === 'mobile' && fontSize >= 14) {
      this.addTestResult('Font Size', true, `Font size is appropriate for mobile: ${fontSize}px`);
    } else if (this.currentBreakpoint !== 'mobile' && fontSize >= 14) {
      this.addTestResult('Font Size', true, `Font size is appropriate: ${fontSize}px`);
    } else {
      this.addTestResult('Font Size', false, `Font size may be too small: ${fontSize}px`);
    }
  }

  testButtonSizes() {
    const buttons = document.querySelectorAll('.btn');
    let validButtons = 0;
    let totalButtons = 0;
    
    buttons.forEach(button => {
      const rect = button.getBoundingClientRect();
      const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
      
      totalButtons++;
      
      if (isTouchDevice) {
        if (rect.width >= 44 && rect.height >= 44) {
          validButtons++;
        }
      } else {
        validButtons++;
      }
    });
    
    if (validButtons === totalButtons && totalButtons > 0) {
      this.addTestResult('Button Sizes', true, `All ${totalButtons} buttons meet size requirements`);
    } else {
      this.addTestResult('Button Sizes', false, `${validButtons}/${totalButtons} buttons meet size requirements`);
    }
  }

  addTestResult(testName, passed, message) {
    this.testResults.push({
      name: testName,
      passed,
      message,
      breakpoint: this.currentBreakpoint,
      timestamp: new Date().toISOString()
    });
  }

  displayResults() {
    const testList = document.getElementById('test-list');
    testList.innerHTML = '';
    
    this.testResults.forEach(result => {
      const testItem = document.createElement('div');
      testItem.className = `test-item ${result.passed ? 'pass' : 'fail'}`;
      testItem.innerHTML = `
        <span>${result.name}</span>
        <span>${result.passed ? '✓' : '✗'}</span>
      `;
      testItem.title = result.message;
      testList.appendChild(testItem);
    });
  }

  exportResults() {
    const results = {
      timestamp: new Date().toISOString(),
      viewport: {
        width: window.innerWidth,
        height: window.innerHeight,
        breakpoint: this.currentBreakpoint
      },
      userAgent: navigator.userAgent,
      results: this.testResults
    };
    
    const blob = new Blob([JSON.stringify(results, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `responsive-test-results-${Date.now()}.json`;
    a.click();
    URL.revokeObjectURL(url);
  }
}

// Initialize responsive test utility
const responsiveTest = new ResponsiveTest();

export default responsiveTest;
