/**
 * Palette Generator con Effetti Particellari
 * Sistema particelle avanzato con animazioni fluide
 */

(function() {
    'use strict';
    
    class PaletteGeneratorParticles {
        constructor(container, options = {}) {
            this.container = container;
            this.canvas = container.querySelector('.palette-canvas');
            this.ctx = this.canvas.getContext('2d');
            
            this.options = {
                particleCount: parseInt(options.particles) || 200,
                audioEnabled: options.audio !== 'false',
                ...options
            };
            
            this.particles = [];
            this.colors = [];
            this.palette = [];
            this.animationId = null;
            this.audioContext = null;
            this.isMixing = false;
            
            this.init();
        }
        
        init() {
            this.setupCanvas();
            this.setupAudio();
            this.setupEventListeners();
            this.generateInitialPalette();
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
        
        setupAudio() {
            if (this.options.audioEnabled && (window.AudioContext || window.webkitAudioContext)) {
                try {
                    this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                } catch (e) {
                    console.warn('Web Audio API not supported');
                }
            }
        }
        
        setupEventListeners() {
            const generateBtn = this.container.querySelector('#generate-palette');
            const mixBtn = this.container.querySelector('#mix-colors');
            const exportBtn = this.container.querySelector('#export-palette');
            
            if (generateBtn) {
                generateBtn.addEventListener('click', () => this.generatePalette());
            }
            
            if (mixBtn) {
                mixBtn.addEventListener('click', () => this.mixColors());
            }
            
            if (exportBtn) {
                exportBtn.addEventListener('click', () => this.exportPalette());
            }
            
            window.addEventListener('resize', () => {
                this.setupCanvas();
            });
        }
        
        generateInitialPalette() {
            this.colors = [
                { h: Math.random() * 360, s: 50 + Math.random() * 50, l: 40 + Math.random() * 30 },
                { h: Math.random() * 360, s: 50 + Math.random() * 50, l: 40 + Math.random() * 30 },
                { h: Math.random() * 360, s: 50 + Math.random() * 50, l: 40 + Math.random() * 30 },
            ];
            this.createParticles();
            this.updatePaletteDisplay();
        }
        
        generatePalette() {
            this.colors = [];
            const baseHue = Math.random() * 360;
            const harmonyTypes = ['analogous', 'complementary', 'triadic'];
            const type = harmonyTypes[Math.floor(Math.random() * harmonyTypes.length)];
            
            switch(type) {
                case 'analogous':
                    for (let i = 0; i < 5; i++) {
                        this.colors.push({
                            h: (baseHue + i * 30) % 360,
                            s: 50 + Math.random() * 30,
                            l: 40 + Math.random() * 30
                        });
                    }
                    break;
                case 'complementary':
                    this.colors.push(
                        { h: baseHue, s: 50 + Math.random() * 30, l: 40 + Math.random() * 30 },
                        { h: (baseHue + 180) % 360, s: 50 + Math.random() * 30, l: 40 + Math.random() * 30 }
                    );
                    break;
                case 'triadic':
                    this.colors.push(
                        { h: baseHue, s: 50 + Math.random() * 30, l: 40 + Math.random() * 30 },
                        { h: (baseHue + 120) % 360, s: 50 + Math.random() * 30, l: 40 + Math.random() * 30 },
                        { h: (baseHue + 240) % 360, s: 50 + Math.random() * 30, l: 40 + Math.random() * 30 }
                    );
                    break;
            }
            
            this.createParticles();
            this.updatePaletteDisplay();
            this.playPaletteSound();
        }
        
        mixColors() {
            if (this.isMixing) return;
            this.isMixing = true;
            
            // Anima particelle verso il centro per mescolare
            const centerX = this.canvas.width / 2;
            const centerY = this.canvas.height / 2;
            
            this.particles.forEach(particle => {
                const dx = centerX - particle.x;
                const dy = centerY - particle.y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance > 10) {
                    particle.vx += dx * 0.01;
                    particle.vy += dy * 0.01;
                } else {
                    // Mescola colori quando si incontrano
                    const nearby = this.particles.filter(p => {
                        const d = Math.sqrt(Math.pow(p.x - particle.x, 2) + Math.pow(p.y - particle.y, 2));
                        return d < 20 && p !== particle;
                    });
                    
                    if (nearby.length > 0) {
                        const avgH = nearby.reduce((sum, p) => sum + p.color.h, particle.color.h) / (nearby.length + 1);
                        const avgS = nearby.reduce((sum, p) => sum + p.color.s, particle.color.s) / (nearby.length + 1);
                        const avgL = nearby.reduce((sum, p) => sum + p.color.l, particle.color.l) / (nearby.length + 1);
                        
                        particle.color = { h: avgH % 360, s: Math.min(100, avgS), l: Math.min(100, avgL) };
                    }
                }
            });
            
            setTimeout(() => {
                this.isMixing = false;
                this.updatePaletteFromParticles();
            }, 3000);
        }
        
        createParticles() {
            this.particles = [];
            const particlesPerColor = Math.floor(this.options.particleCount / this.colors.length);
            
            this.colors.forEach((color, colorIndex) => {
                for (let i = 0; i < particlesPerColor; i++) {
                    const angle = (Math.PI * 2 / this.colors.length) * colorIndex + (Math.random() - 0.5) * 0.5;
                    const distance = Math.min(this.canvas.width, this.canvas.height) / 3;
                    const x = this.canvas.width / 2 + Math.cos(angle) * distance + (Math.random() - 0.5) * 100;
                    const y = this.canvas.height / 2 + Math.sin(angle) * distance + (Math.random() - 0.5) * 100;
                    
                    this.particles.push(new Particle(
                        x,
                        y,
                        color,
                        colorIndex,
                        this.canvas.width,
                        this.canvas.height
                    ));
                }
            });
        }
        
        updatePaletteFromParticles() {
            // Raggruppa particelle per colore dominante
            const colorGroups = {};
            this.particles.forEach(p => {
                const key = Math.round(p.color.h / 10) * 10;
                if (!colorGroups[key]) colorGroups[key] = [];
                colorGroups[key].push(p);
            });
            
            // Trova i colori più comuni
            const sorted = Object.entries(colorGroups)
                .sort((a, b) => b[1].length - a[1].length)
                .slice(0, 5);
            
            this.colors = sorted.map(([h, particles]) => {
                const avg = particles.reduce((sum, p) => ({
                    h: sum.h + p.color.h,
                    s: sum.s + p.color.s,
                    l: sum.l + p.color.l
                }), { h: 0, s: 0, l: 0 });
                
                return {
                    h: avg.h / particles.length,
                    s: avg.s / particles.length,
                    l: avg.l / particles.length
                };
            });
            
            this.updatePaletteDisplay();
        }
        
        updatePaletteDisplay() {
            const display = this.container.querySelector('#palette-colors');
            if (!display) return;
            
            display.innerHTML = this.colors.map((color, index) => {
                const hex = this.hslToHex(color.h, color.s, color.l);
                return `
                    <div class="palette-color-item" style="background: hsl(${color.h}, ${color.s}%, ${color.l}%)" 
                         data-hex="${hex}" data-index="${index}">
                        <span class="color-hex">${hex}</span>
                    </div>
                `;
            }).join('');
        }
        
        exportPalette() {
            const palette = this.colors.map(c => ({
                hsl: `hsl(${Math.round(c.h)}, ${Math.round(c.s)}%, ${Math.round(c.l)}%)`,
                hex: this.hslToHex(c.h, c.s, c.l),
                rgb: this.hslToRgb(c.h, c.s, c.l)
            }));
            
            const data = {
                palette: palette,
                timestamp: new Date().toISOString()
            };
            
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `palette-${Date.now()}.json`;
            a.click();
            URL.revokeObjectURL(url);
        }
        
        playPaletteSound() {
            if (!this.audioContext) return;
            
            this.colors.forEach((color, index) => {
                setTimeout(() => {
                    const frequency = 200 + (color.h / 360) * 1800;
                    const oscillator = this.audioContext.createOscillator();
                    const gainNode = this.audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(this.audioContext.destination);
                    
                    oscillator.frequency.value = frequency;
                    oscillator.type = 'sine';
                    
                    gainNode.gain.setValueAtTime(0.2, this.audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.3);
                    
                    oscillator.start(this.audioContext.currentTime);
                    oscillator.stop(this.audioContext.currentTime + 0.3);
                }, index * 100);
            });
        }
        
        animate() {
            this.update();
            this.animationId = requestAnimationFrame(() => this.animate());
        }
        
        update() {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            
            // Update and draw particles
            this.particles.forEach(particle => {
                particle.update(this.canvas.width, this.canvas.height, this.particles);
                particle.draw(this.ctx);
            });
        }
        
        hslToHex(h, s, l) {
            h /= 360;
            s /= 100;
            l /= 100;
            
            let r, g, b;
            if (s === 0) {
                r = g = b = l;
            } else {
                const hue2rgb = (p, q, t) => {
                    if (t < 0) t += 1;
                    if (t > 1) t -= 1;
                    if (t < 1/6) return p + (q - p) * 6 * t;
                    if (t < 1/2) return q;
                    if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                    return p;
                };
                
                const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                const p = 2 * l - q;
                r = hue2rgb(p, q, h + 1/3);
                g = hue2rgb(p, q, h);
                b = hue2rgb(p, q, h - 1/3);
            }
            
            const toHex = (c) => {
                const hex = Math.round(c * 255).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            };
            
            return `#${toHex(r)}${toHex(g)}${toHex(b)}`.toUpperCase();
        }
        
        hslToRgb(h, s, l) {
            h /= 360;
            s /= 100;
            l /= 100;
            
            let r, g, b;
            if (s === 0) {
                r = g = b = l;
            } else {
                const hue2rgb = (p, q, t) => {
                    if (t < 0) t += 1;
                    if (t > 1) t -= 1;
                    if (t < 1/6) return p + (q - p) * 6 * t;
                    if (t < 1/2) return q;
                    if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                    return p;
                };
                
                const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                const p = 2 * l - q;
                r = hue2rgb(p, q, h + 1/3);
                g = hue2rgb(p, q, h);
                b = hue2rgb(p, q, h - 1/3);
            }
            
            return {
                r: Math.round(r * 255),
                g: Math.round(g * 255),
                b: Math.round(b * 255)
            };
        }
        
        destroy() {
            if (this.animationId) {
                cancelAnimationFrame(this.animationId);
            }
            if (this.audioContext) {
                this.audioContext.close();
            }
        }
    }
    
    class Particle {
        constructor(x, y, color, groupIndex, canvasWidth, canvasHeight) {
            this.x = x;
            this.y = y;
            this.color = { ...color };
            this.groupIndex = groupIndex;
            this.vx = (Math.random() - 0.5) * 2;
            this.vy = (Math.random() - 0.5) * 2;
            this.size = Math.random() * 3 + 1;
            this.life = 1.0;
            this.maxSpeed = 2;
        }
        
        update(canvasWidth, canvasHeight, allParticles) {
            // Attrazione verso particelle dello stesso colore
            const sameColorParticles = allParticles.filter(p => 
                p.groupIndex === this.groupIndex && p !== this
            );
            
            if (sameColorParticles.length > 0) {
                const avgX = sameColorParticles.reduce((sum, p) => sum + p.x, 0) / sameColorParticles.length;
                const avgY = sameColorParticles.reduce((sum, p) => sum + p.y, 0) / sameColorParticles.length;
                
                const dx = avgX - this.x;
                const dy = avgY - this.y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance > 0) {
                    this.vx += (dx / distance) * 0.01;
                    this.vy += (dy / distance) * 0.01;
                }
            }
            
            // Repulsione da particelle di colori diversi
            const differentColorParticles = allParticles.filter(p => 
                p.groupIndex !== this.groupIndex
            );
            
            differentColorParticles.forEach(p => {
                const dx = this.x - p.x;
                const dy = this.y - p.y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance > 0 && distance < 50) {
                    const force = (50 - distance) / 50;
                    this.vx += (dx / distance) * force * 0.05;
                    this.vy += (dy / distance) * force * 0.05;
                }
            });
            
            // Limita velocità
            const speed = Math.sqrt(this.vx * this.vx + this.vy * this.vy);
            if (speed > this.maxSpeed) {
                this.vx = (this.vx / speed) * this.maxSpeed;
                this.vy = (this.vy / speed) * this.maxSpeed;
            }
            
            // Aggiorna posizione
            this.x += this.vx;
            this.y += this.vy;
            
            // Bounce boundaries
            if (this.x < 0 || this.x > canvasWidth) {
                this.vx *= -0.8;
                this.x = Math.max(0, Math.min(canvasWidth, this.x));
            }
            if (this.y < 0 || this.y > canvasHeight) {
                this.vy *= -0.8;
                this.y = Math.max(0, Math.min(canvasHeight, this.y));
            }
            
            // Attrito
            this.vx *= 0.98;
            this.vy *= 0.98;
        }
        
        draw(ctx) {
            ctx.fillStyle = `hsla(${this.color.h}, ${this.color.s}%, ${this.color.l}%, 0.7)`;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
            
            // Glow effect
            const gradient = ctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, this.size * 2);
            gradient.addColorStop(0, `hsla(${this.color.h}, ${this.color.s}%, ${this.color.l}%, 0.3)`);
            gradient.addColorStop(1, 'transparent');
            ctx.fillStyle = gradient;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size * 2, 0, Math.PI * 2);
            ctx.fill();
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.palette-generator');
        containers.forEach(container => {
            const options = {};
            Array.from(container.attributes).forEach(attr => {
                if (attr.name.startsWith('data-')) {
                    const key = attr.name.replace('data-', '').replace(/-/g, '-');
                    options[key] = attr.value;
                }
            });
            
            new PaletteGeneratorParticles(container, options);
        });
    });
    
    window.PaletteGeneratorParticles = PaletteGeneratorParticles;
})();

