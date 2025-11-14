/**
 * Mobile App Shell
 * 
 * App JS avanzatissima tipo mobile che unisce tutti i widget
 * PWA-ready con service worker, offline support, e UI mobile-first
 */

(function() {
    'use strict';
    
    class MobileAppShell {
        constructor(container, options = {}) {
            this.container = container;
            this.currentPage = 'home';
            this.widgets = new Map();
            this.theme = options.theme || 'dark';
            this.mode = options.mode || 'full';
            
            this.init();
        }
        
        init() {
            this.setupServiceWorker();
            this.setupEventListeners();
            this.setupNavigation();
            this.loadWidgets();
            this.setupGestures();
            this.setupTheme();
            this.setupOfflineSupport();
        }
        
        async setupServiceWorker() {
            if ('serviceWorker' in navigator) {
                try {
                    const registration = await navigator.serviceWorker.register('/sw.js');
                    console.log('Service Worker registered:', registration);
                } catch (error) {
                    console.warn('Service Worker registration failed:', error);
                }
            }
        }
        
        setupEventListeners() {
            // Menu button
            const menuBtn = this.container.querySelector('#app-menu-btn');
            if (menuBtn) {
                menuBtn.addEventListener('click', () => this.toggleMenu());
            }
            
            // Search button
            const searchBtn = this.container.querySelector('#app-search-btn');
            if (searchBtn) {
                searchBtn.addEventListener('click', () => this.openSearch());
            }
            
            // Navigation items
            const navItems = this.container.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    const page = item.dataset.page;
                    this.navigateToPage(page);
                });
            });
            
            // Back button (mobile)
            window.addEventListener('popstate', () => {
                this.handleBackButton();
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeOverlays();
                }
            });
        }
        
        setupNavigation() {
            // Smooth page transitions
            this.container.querySelectorAll('.app-page').forEach(page => {
                page.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s';
            });
        }
        
        navigateToPage(pageId) {
            // Hide current page
            const currentPage = this.container.querySelector('.app-page.active');
            if (currentPage) {
                currentPage.classList.remove('active');
                if (typeof gsap !== 'undefined') {
                    gsap.to(currentPage, {
                        opacity: 0,
                        x: -20,
                        duration: 0.3,
                        ease: 'power2.in'
                    });
                }
            }
            
            // Show new page
            const newPage = this.container.querySelector(`#page-${pageId}`);
            if (newPage) {
                newPage.classList.add('active');
                if (typeof gsap !== 'undefined') {
                    gsap.fromTo(newPage, 
                        { opacity: 0, x: 20 },
                        { opacity: 1, x: 0, duration: 0.3, ease: 'power2.out' }
                    );
                }
                
                // Update nav
                this.container.querySelectorAll('.nav-item').forEach(item => {
                    item.classList.toggle('active', item.dataset.page === pageId);
                });
                
                this.currentPage = pageId;
                
                // Load widgets for this page
                this.loadPageWidgets(pageId);
            }
        }
        
        loadWidgets() {
            // Lazy load widgets when needed
            const widgetContainers = this.container.querySelectorAll('.widget-container');
            widgetContainers.forEach(container => {
                const widgetType = container.dataset.widget;
                if (widgetType) {
                    this.loadWidget(widgetType, container);
                }
            });
        }
        
        async loadWidget(widgetType, container) {
            // Dynamic import widget
            try {
                const widgetModule = await import(`./widgets/${widgetType}.js`);
                const WidgetClass = widgetModule.default || widgetModule[widgetType];
                if (WidgetClass) {
                    const widget = new WidgetClass(container);
                    this.widgets.set(widgetType, widget);
                }
            } catch (error) {
                console.warn(`Widget ${widgetType} not found, using fallback`);
                this.loadWidgetFallback(widgetType, container);
            }
        }
        
        loadWidgetFallback(widgetType, container) {
            // Fallback: load via shortcode
            container.innerHTML = `[${widgetType.replace(/-/g, '_')}]`;
        }
        
        loadPageWidgets(pageId) {
            const page = this.container.querySelector(`#page-${pageId}`);
            if (!page) return;
            
            const widgets = page.querySelectorAll('.widget-container');
            widgets.forEach(container => {
                if (!container.dataset.loaded) {
                    const widgetType = container.dataset.widget;
                    if (widgetType) {
                        this.loadWidget(widgetType, container);
                        container.dataset.loaded = 'true';
                    }
                }
            });
        }
        
        setupGestures() {
            if (typeof Hammer === 'undefined') return;
            
            const content = this.container.querySelector('.app-content');
            if (!content) return;
            
            const hammer = new Hammer(content);
            
            // Swipe between pages
            hammer.on('swipeleft', () => {
                this.swipeToNextPage();
            });
            
            hammer.on('swiperight', () => {
                this.swipeToPrevPage();
            });
            
            // Pull to refresh
            let pullDistance = 0;
            hammer.on('pan', (e) => {
                if (e.deltaY > 0 && this.currentPage === 'home') {
                    pullDistance = e.deltaY;
                    if (pullDistance > 100) {
                        this.triggerRefresh();
                    }
                }
            });
        }
        
        swipeToNextPage() {
            const pages = ['home', 'colors', 'ikea', 'rooms', 'psycho'];
            const currentIndex = pages.indexOf(this.currentPage);
            if (currentIndex < pages.length - 1) {
                this.navigateToPage(pages[currentIndex + 1]);
            }
        }
        
        swipeToPrevPage() {
            const pages = ['home', 'colors', 'ikea', 'rooms', 'psycho'];
            const currentIndex = pages.indexOf(this.currentPage);
            if (currentIndex > 0) {
                this.navigateToPage(pages[currentIndex - 1]);
            }
        }
        
        triggerRefresh() {
            // Pull to refresh
            if (typeof gsap !== 'undefined') {
                gsap.to(this.container.querySelector('.app-content'), {
                    y: 50,
                    duration: 0.3,
                    onComplete: () => {
                        this.refreshData();
                        gsap.to(this.container.querySelector('.app-content'), {
                            y: 0,
                            duration: 0.3
                        });
                    }
                });
            }
        }
        
        refreshData() {
            // Reload widgets
            this.loadPageWidgets(this.currentPage);
        }
        
        toggleMenu() {
            const nav = this.container.querySelector('#app-nav');
            if (nav) {
                nav.classList.toggle('open');
            }
        }
        
        openSearch() {
            // Open search overlay
            const overlay = this.container.querySelector('#app-overlay');
            if (overlay) {
                overlay.classList.add('search-open');
                overlay.innerHTML = `
                    <div class="search-container">
                        <input type="text" class="search-input" placeholder="Cerca widget, colori, IKEA..." autofocus>
                        <button class="search-close">✕</button>
                        <div class="search-results"></div>
                    </div>
                `;
                
                const input = overlay.querySelector('.search-input');
                if (input) {
                    input.addEventListener('input', (e) => {
                        this.performSearch(e.target.value);
                    });
                }
                
                const closeBtn = overlay.querySelector('.search-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        this.closeOverlays();
                    });
                }
            }
        }
        
        performSearch(query) {
            // Search through widgets and content
            const results = this.searchWidgets(query);
            const resultsContainer = this.container.querySelector('.search-results');
            if (resultsContainer) {
                resultsContainer.innerHTML = results.map(result => `
                    <div class="search-result" data-widget="${result.type}">
                        <h4>${result.title}</h4>
                        <p>${result.description}</p>
                    </div>
                `).join('');
            }
        }
        
        searchWidgets(query) {
            // Mock search results
            const allWidgets = [
                { type: 'color-harmony', title: 'Color Harmony Visualizer', description: 'Visualizza armonie colori' },
                { type: 'palette-generator', title: 'Palette Generator', description: 'Genera palette colori' },
                // ... altri widget
            ];
            
            return allWidgets.filter(widget => 
                widget.title.toLowerCase().includes(query.toLowerCase()) ||
                widget.description.toLowerCase().includes(query.toLowerCase())
            );
        }
        
        closeOverlays() {
            const overlay = this.container.querySelector('#app-overlay');
            if (overlay) {
                overlay.classList.remove('search-open');
                overlay.innerHTML = '';
            }
            
            const nav = this.container.querySelector('#app-nav');
            if (nav) {
                nav.classList.remove('open');
            }
        }
        
        setupTheme() {
            // Apply theme
            this.container.classList.add(`theme-${this.theme}`);
            
            // Theme toggle
            const themeBtn = this.container.querySelector('.theme-toggle');
            if (themeBtn) {
                themeBtn.addEventListener('click', () => {
                    this.theme = this.theme === 'dark' ? 'light' : 'dark';
                    this.container.classList.toggle('theme-dark', this.theme === 'dark');
                    this.container.classList.toggle('theme-light', this.theme === 'light');
                    localStorage.setItem('app-theme', this.theme);
                });
            }
        }
        
        setupOfflineSupport() {
            // Check online status
            window.addEventListener('online', () => {
                this.showNotification('Connessione ripristinata', 'success');
            });
            
            window.addEventListener('offline', () => {
                this.showNotification('Modalità offline', 'warning');
            });
        }
        
        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `app-notification ${type}`;
            notification.textContent = message;
            this.container.appendChild(notification);
            
            if (typeof gsap !== 'undefined') {
                gsap.fromTo(notification,
                    { y: -50, opacity: 0 },
                    { y: 0, opacity: 1, duration: 0.3 }
                );
                
                setTimeout(() => {
                    gsap.to(notification, {
                        y: -50,
                        opacity: 0,
                        duration: 0.3,
                        onComplete: () => notification.remove()
                    });
                }, 3000);
            }
        }
        
        handleBackButton() {
            // Handle browser back button
            if (this.currentPage !== 'home') {
                this.navigateToPage('home');
            }
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.mobile-app-shell');
        containers.forEach(container => {
            const options = {
                theme: container.dataset.theme || 'dark',
                mode: container.dataset.mode || 'full'
            };
            
            new MobileAppShell(container, options);
        });
    });
    
    window.MobileAppShell = MobileAppShell;
})();

