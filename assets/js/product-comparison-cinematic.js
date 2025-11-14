/**
 * Product Comparison Cinematic
 * Animazioni cinematiche tipo videogame per confronto prodotti
 */

(function() {
    'use strict';
    
    class ProductComparisonCinematic {
        constructor(container, options = {}) {
            this.container = container;
            this.products = [];
            this.currentIndex = 0;
            this.isAnimating = false;
            this.animationType = options.animation || 'cinematic';
            
            this.init();
        }
        
        init() {
            this.loadProducts();
            this.setupEventListeners();
            this.render();
        }
        
        loadProducts() {
            // Carica prodotti da attributi o da WordPress
            const productsAttr = this.container.dataset.products;
            if (productsAttr) {
                this.products = productsAttr.split(',').map(p => p.trim());
            } else {
                // Default products for demo
                this.products = [
                    { name: 'BILLY', image: '', features: ['Libreria', 'Modulare', 'Classica'] },
                    { name: 'KALLAX', image: '', features: ['Cubi', 'Versatile', 'Moderno'] },
                    { name: 'BESTA', image: '', features: ['Parete', 'Elegante', 'Minimalista'] }
                ];
            }
        }
        
        setupEventListeners() {
            const prevBtn = this.container.querySelector('.comparison-btn.prev');
            const nextBtn = this.container.querySelector('.comparison-btn.next');
            
            if (prevBtn) {
                prevBtn.addEventListener('click', () => this.previous());
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', () => this.next());
            }
            
            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (this.container.contains(document.activeElement) || 
                    document.activeElement === document.body) {
                    if (e.key === 'ArrowLeft') this.previous();
                    if (e.key === 'ArrowRight') this.next();
                }
            });
            
            // Touch gestures
            if (typeof Hammer !== 'undefined') {
                const hammer = new Hammer(this.container);
                hammer.on('swipeleft', () => this.next());
                hammer.on('swiperight', () => this.previous());
            }
        }
        
        render() {
            const container = this.container.querySelector('.comparison-container');
            if (!container) return;
            
            container.innerHTML = this.products.map((product, index) => {
                const isActive = index === this.currentIndex;
                return `
                    <div class="comparison-product ${isActive ? 'active' : ''}" data-index="${index}">
                        <div class="product-image">
                            <div class="product-placeholder">${product.name || product}</div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">${product.name || product}</h3>
                            ${product.features ? `
                                <ul class="product-features">
                                    ${product.features.map(f => `<li>${f}</li>`).join('')}
                                </ul>
                            ` : ''}
                        </div>
                    </div>
                `;
            }).join('');
            
            // Anima con GSAP
            if (typeof gsap !== 'undefined') {
                const products = container.querySelectorAll('.comparison-product');
                products.forEach((product, index) => {
                    if (index === this.currentIndex) {
                        gsap.fromTo(product, 
                            { opacity: 0, scale: 0.8, x: 100 },
                            { opacity: 1, scale: 1, x: 0, duration: 0.8, ease: 'power3.out' }
                        );
                    } else {
                        gsap.to(product, { opacity: 0.3, scale: 0.9, duration: 0.5 });
                    }
                });
            }
        }
        
        next() {
            if (this.isAnimating) return;
            this.isAnimating = true;
            
            this.currentIndex = (this.currentIndex + 1) % this.products.length;
            this.render();
            
            setTimeout(() => {
                this.isAnimating = false;
            }, 800);
        }
        
        previous() {
            if (this.isAnimating) return;
            this.isAnimating = true;
            
            this.currentIndex = (this.currentIndex - 1 + this.products.length) % this.products.length;
            this.render();
            
            setTimeout(() => {
                this.isAnimating = false;
            }, 800);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.product-comparison');
        containers.forEach(container => {
            const options = {};
            Array.from(container.attributes).forEach(attr => {
                if (attr.name.startsWith('data-')) {
                    const key = attr.name.replace('data-', '').replace(/-/g, '-');
                    options[key] = attr.value;
                }
            });
            
            new ProductComparisonCinematic(container, options);
        });
    });
    
    window.ProductComparisonCinematic = ProductComparisonCinematic;
})();

