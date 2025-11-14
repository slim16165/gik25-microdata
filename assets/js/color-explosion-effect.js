/**
 * Color Explosion Effect
 * Effetto esplosione colori con particelle avanzate
 */

(function() {
    'use strict';
    
    class ColorExplosionEffect {
        constructor(container, options = {}) {
            this.container = container;
            this.canvas = container.querySelector('.explosion-canvas');
            this.ctx = this.canvas.getContext('2d');
            this.color = options.color || '#FF0000';
            this.particleCount = parseInt(options.particles) || 500;
            this.particles = [];
            this.animationId = null;
            
            this.init();
        }
        
        init() {
            this.setupCanvas();
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
        
        setupEventListeners() {
            const trigger = this.container.querySelector('.explosion-trigger');
            if (trigger) {
                trigger.addEventListener('click', () => this.explode());
            }
            
            // Auto-explode on load
            setTimeout(() => this.explode(), 500);
        }
        
        explode() {
            const centerX = this.canvas.width / 2;
            const centerY = this.canvas.height / 2;
            
            this.particles = [];
            const rgb = this.hexToRgb(this.color);
            
            for (let i = 0; i < this.particleCount; i++) {
                const angle = (Math.PI * 2 / this.particleCount) * i;
                const speed = Math.random() * 10 + 5;
                
                this.particles.push(new ExplosionParticle(
                    centerX,
                    centerY,
                    angle,
                    speed,
                    rgb
                ));
            }
            
            // Audio feedback
            this.playExplosionSound();
        }
        
        playExplosionSound() {
            if (typeof AudioContext === 'undefined') return;
            
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 200;
                oscillator.type = 'sawtooth';
                
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            } catch (e) {
                // Audio not supported
            }
        }
        
        animate() {
            this.update();
            this.draw();
            this.animationId = requestAnimationFrame(() => this.animate());
        }
        
        update() {
            this.particles = this.particles.filter(particle => {
                particle.update();
                return particle.life > 0;
            });
        }
        
        draw() {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            
            this.particles.forEach(particle => {
                particle.draw(this.ctx);
            });
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
    
    class ExplosionParticle {
        constructor(x, y, angle, speed, color) {
            this.x = x;
            this.y = y;
            this.vx = Math.cos(angle) * speed;
            this.vy = Math.sin(angle) * speed;
            this.color = { ...color };
            this.size = Math.random() * 5 + 2;
            this.life = 1.0;
            this.decay = Math.random() * 0.02 + 0.01;
        }
        
        update() {
            this.x += this.vx;
            this.y += this.vy;
            this.vx *= 0.98;
            this.vy *= 0.98;
            this.life -= this.decay;
            this.size *= 0.99;
        }
        
        draw(ctx) {
            const alpha = this.life;
            ctx.fillStyle = `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, ${alpha})`;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
            
            // Glow effect
            const gradient = ctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, this.size * 2);
            gradient.addColorStop(0, `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, ${alpha * 0.5})`);
            gradient.addColorStop(1, 'transparent');
            ctx.fillStyle = gradient;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size * 2, 0, Math.PI * 2);
            ctx.fill();
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.color-explosion');
        containers.forEach(container => {
            const options = {
                color: container.dataset.color || '#FF0000',
                particles: parseInt(container.dataset.particles) || 500
            };
            
            new ColorExplosionEffect(container, options);
        });
    });
    
    window.ColorExplosionEffect = ColorExplosionEffect;
})();

