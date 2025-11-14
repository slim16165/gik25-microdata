/**
 * Fluid Color Mixer
 * Simulazione fluida realistica per mescolare colori
 */

(function() {
    'use strict';
    
    class FluidColorMixer {
        constructor(container, options = {}) {
            this.container = container;
            this.canvas = container.querySelector('.fluid-canvas');
            this.ctx = this.canvas.getContext('2d');
            this.viscosity = options.viscosity || 'medium';
            
            this.particles = [];
            this.colors = [
                { r: 255, g: 0, b: 0 },   // Rosso
                { r: 0, g: 0, b: 255 }    // Blu
            ];
            this.animationId = null;
            
            this.init();
        }
        
        init() {
            this.setupCanvas();
            this.setupParticles();
            this.setupEventListeners();
            this.animate();
        }
        
        setupCanvas() {
            const dpr = window.devicePixelRatio || 1;
            const rect = this.canvas.getBoundingClientRect();
            
            this.canvas.width = rect.width * dpr;
            this.canvas.height = rect.height * dpr;
            this.canvas.style.width = rect.width + 'px';
            this.canvas.style.height = rect.height + 'px';
            
            this.ctx.scale(dpr, dpr);
        }
        
        setupParticles() {
            const particleCount = 500;
            const colorsPerSide = particleCount / 2;
            
            // Red particles on left
            for (let i = 0; i < colorsPerSide; i++) {
                this.particles.push(new FluidParticle(
                    Math.random() * this.canvas.width / 2,
                    Math.random() * this.canvas.height,
                    this.colors[0],
                    0
                ));
            }
            
            // Blue particles on right
            for (let i = 0; i < colorsPerSide; i++) {
                this.particles.push(new FluidParticle(
                    this.canvas.width / 2 + Math.random() * this.canvas.width / 2,
                    Math.random() * this.canvas.height,
                    this.colors[1],
                    1
                ));
            }
        }
        
        setupEventListeners() {
            const color1Input = this.container.querySelector('#color-1');
            const color2Input = this.container.querySelector('#color-2');
            const mixBtn = this.container.querySelector('#mix-colors');
            const resetBtn = this.container.querySelector('#reset-fluid');
            
            if (color1Input) {
                color1Input.addEventListener('change', (e) => {
                    const hex = e.target.value;
                    this.colors[0] = this.hexToRgb(hex);
                    this.updateParticleColors(0);
                });
            }
            
            if (color2Input) {
                color2Input.addEventListener('change', (e) => {
                    const hex = e.target.value;
                    this.colors[1] = this.hexToRgb(hex);
                    this.updateParticleColors(1);
                });
            }
            
            if (mixBtn) {
                mixBtn.addEventListener('click', () => this.mixColors());
            }
            
            if (resetBtn) {
                resetBtn.addEventListener('click', () => this.reset());
            }
            
            window.addEventListener('resize', () => {
                this.setupCanvas();
            });
        }
        
        updateParticleColors(colorIndex) {
            this.particles.forEach(particle => {
                if (particle.colorIndex === colorIndex) {
                    particle.color = { ...this.colors[colorIndex] };
                }
            });
        }
        
        mixColors() {
            // Animate particles to mix
            this.particles.forEach(particle => {
                const targetX = this.canvas.width / 2 + (Math.random() - 0.5) * 100;
                const targetY = this.canvas.height / 2 + (Math.random() - 0.5) * 100;
                
                if (typeof gsap !== 'undefined') {
                    gsap.to(particle, {
                        targetX: targetX,
                        targetY: targetY,
                        duration: 2,
                        ease: 'power2.inOut'
                    });
                } else {
                    particle.targetX = targetX;
                    particle.targetY = targetY;
                }
            });
        }
        
        reset() {
            this.setupParticles();
        }
        
        animate() {
            this.update();
            this.draw();
            this.animationId = requestAnimationFrame(() => this.animate());
        }
        
        update() {
            const viscosity = this.viscosity === 'high' ? 0.95 : this.viscosity === 'low' ? 0.98 : 0.97;
            
            this.particles.forEach((particle, i) => {
                // Update position towards target
                if (particle.targetX !== undefined) {
                    particle.x += (particle.targetX - particle.x) * 0.1;
                    particle.y += (particle.targetY - particle.y) * 0.1;
                }
                
                // Fluid dynamics
                particle.vx *= viscosity;
                particle.vy *= viscosity;
                
                // Interaction with nearby particles
                this.particles.forEach((other, j) => {
                    if (i === j) return;
                    
                    const dx = other.x - particle.x;
                    const dy = other.y - particle.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);
                    
                    if (distance > 0 && distance < 20) {
                        const force = (20 - distance) / 20;
                        const angle = Math.atan2(dy, dx);
                        
                        // Repulsion
                        particle.vx -= Math.cos(angle) * force * 0.1;
                        particle.vy -= Math.sin(angle) * force * 0.1;
                        
                        // Color mixing when close
                        if (distance < 5) {
                            particle.color = this.mixColors(particle.color, other.color);
                        }
                    }
                });
                
                // Update position
                particle.x += particle.vx;
                particle.y += particle.vy;
                
                // Boundaries with bounce
                if (particle.x < 0 || particle.x > this.canvas.width) {
                    particle.vx *= -0.8;
                    particle.x = Math.max(0, Math.min(this.canvas.width, particle.x));
                }
                if (particle.y < 0 || particle.y > this.canvas.height) {
                    particle.vy *= -0.8;
                    particle.y = Math.max(0, Math.min(this.canvas.height, particle.y));
                }
            });
        }
        
        draw() {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            
            // Draw particles with blending
            this.ctx.globalCompositeOperation = 'screen';
            this.particles.forEach(particle => {
                particle.draw(this.ctx);
            });
            this.ctx.globalCompositeOperation = 'source-over';
        }
        
        mixColors(color1, color2) {
            return {
                r: Math.min(255, (color1.r + color2.r) / 2),
                g: Math.min(255, (color1.g + color2.g) / 2),
                b: Math.min(255, (color1.b + color2.b) / 2)
            };
        }
        
        hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : { r: 255, g: 0, b: 0 };
        }
    }
    
    class FluidParticle {
        constructor(x, y, color, colorIndex) {
            this.x = x;
            this.y = y;
            this.color = { ...color };
            this.colorIndex = colorIndex;
            this.vx = (Math.random() - 0.5) * 2;
            this.vy = (Math.random() - 0.5) * 2;
            this.size = Math.random() * 4 + 2;
            this.targetX = undefined;
            this.targetY = undefined;
        }
        
        draw(ctx) {
            const gradient = ctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, this.size);
            gradient.addColorStop(0, `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, 0.8)`);
            gradient.addColorStop(1, `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, 0)`);
            
            ctx.fillStyle = gradient;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.fluid-color-mixer');
        containers.forEach(container => {
            const options = {
                viscosity: container.dataset.viscosity || 'medium'
            };
            
            new FluidColorMixer(container, options);
        });
    });
    
    window.FluidColorMixer = FluidColorMixer;
})();

