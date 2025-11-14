/**
 * Color Harmony Visualizer
 * 
 * Visualizzatore interattivo di armonie colori con:
 * - Grafici interattivi D3.js
 * - Animazioni fluide GSAP
 * - Audio reattivo Web Audio
 * - Effetti particellari Canvas
 */

(function() {
    'use strict';
    
    class ColorHarmonyVisualizer {
        constructor(container, options = {}) {
            this.container = container;
            this.canvas = container.querySelector('.harmony-canvas');
            this.ctx = this.canvas.getContext('2d');
            
            this.options = {
                width: options.width || 800,
                height: options.height || 600,
                particleCount: parseInt(options.particles) || 100,
                audioEnabled: options.audio !== 'false',
                harmonyType: options.harmony || 'complementary',
                reducedMotion: options['reduced-motion'] === 'true',
                ...options
            };
            
            this.selectedColor = { h: 0, s: 50, l: 50 };
            this.harmonyType = this.options.harmonyType;
            this.particles = [];
            this.audioContext = null;
            this.animationId = null;
            this.isPlaying = false;
            
            this.init();
        }
        
        init() {
            this.setupCanvas();
            this.setupAudio();
            this.setupEventListeners();
            this.setupParticles();
            this.drawColorWheel();
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
            this.ctx.imageSmoothingEnabled = true;
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
            // Harmony type buttons
            const buttons = this.container.querySelectorAll('.harmony-btn');
            buttons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const type = e.currentTarget.dataset.type;
                    this.setHarmonyType(type);
                    buttons.forEach(b => b.classList.remove('active'));
                    e.currentTarget.classList.add('active');
                });
            });
            
            // Set initial active button
            const initialBtn = this.container.querySelector(`[data-type="${this.harmonyType}"]`);
            if (initialBtn) initialBtn.classList.add('active');
            
            // Random harmony
            const randomBtn = this.container.querySelector('#random-harmony');
            if (randomBtn) {
                randomBtn.addEventListener('click', () => this.generateRandomHarmony());
            }
            
            // Export palette
            const exportBtn = this.container.querySelector('#export-palette');
            if (exportBtn) {
                exportBtn.addEventListener('click', () => this.exportPalette());
            }
            
            // Save palette
            const saveBtn = this.container.querySelector('#save-palette');
            if (saveBtn) {
                saveBtn.addEventListener('click', () => this.savePalette());
            }
            
            // Canvas click for color selection
            this.canvas.addEventListener('click', (e) => {
                const rect = this.canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                this.selectColorAt(x, y);
            });
            
            // Canvas hover for preview
            this.canvas.addEventListener('mousemove', (e) => {
                const rect = this.canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                this.previewColorAt(x, y);
            });
            
            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (this.container.contains(document.activeElement) || 
                    document.activeElement === document.body) {
                    this.handleKeyboard(e);
                }
            });
            
            // Resize handler
            window.addEventListener('resize', () => {
                this.setupCanvas();
                this.drawColorWheel();
            });
        }
        
        setupParticles() {
            this.particles = [];
            const harmony = this.getHarmony(this.selectedColor, this.harmonyType);
            const particlesPerColor = Math.floor(this.options.particleCount / harmony.length);
            
            harmony.forEach((color, index) => {
                for (let i = 0; i < particlesPerColor; i++) {
                    this.particles.push(new Particle(
                        color,
                        index,
                        this.canvas.width,
                        this.canvas.height
                    ));
                }
            });
        }
        
        setHarmonyType(type) {
            this.harmonyType = type;
            this.setupParticles();
            this.updatePaletteDisplay();
            
            // Play harmony sound
            if (this.audioContext) {
                this.playHarmonySound(this.getHarmony(this.selectedColor, type));
            }
        }
        
        getHarmony(color, type) {
            const harmonies = {
                complementary: () => [
                    color,
                    this.complementary(color)
                ],
                analogous: () => this.analogous(color, 5),
                triadic: () => this.triadic(color),
                'split-complementary': () => this.splitComplementary(color),
                tetradic: () => this.tetradic(color),
                monochromatic: () => this.monochromatic(color, 5)
            };
            
            return harmonies[type] ? harmonies[type]() : harmonies.complementary();
        }
        
        complementary(color) {
            return {
                h: (color.h + 180) % 360,
                s: color.s,
                l: color.l
            };
        }
        
        analogous(color, count = 5) {
            const step = 30;
            const start = color.h - (step * Math.floor(count / 2));
            const colors = [];
            
            for (let i = 0; i < count; i++) {
                colors.push({
                    h: (start + (step * i) + 360) % 360,
                    s: color.s,
                    l: color.l
                });
            }
            
            return colors;
        }
        
        triadic(color) {
            return [
                color,
                { h: (color.h + 120) % 360, s: color.s, l: color.l },
                { h: (color.h + 240) % 360, s: color.s, l: color.l }
            ];
        }
        
        splitComplementary(color) {
            return [
                color,
                { h: (color.h + 150) % 360, s: color.s, l: color.l },
                { h: (color.h + 210) % 360, s: color.s, l: color.l }
            ];
        }
        
        tetradic(color) {
            return [
                color,
                { h: (color.h + 90) % 360, s: color.s, l: color.l },
                { h: (color.h + 180) % 360, s: color.s, l: color.l },
                { h: (color.h + 270) % 360, s: color.s, l: color.l }
            ];
        }
        
        monochromatic(color, count = 5) {
            const colors = [];
            const lightnessStep = 80 / count;
            
            for (let i = 0; i < count; i++) {
                colors.push({
                    h: color.h,
                    s: color.s,
                    l: 10 + (lightnessStep * i)
                });
            }
            
            return colors;
        }
        
        selectColorAt(x, y) {
            const centerX = this.canvas.width / 2;
            const centerY = this.canvas.height / 2;
            const radius = Math.min(this.canvas.width, this.canvas.height) / 3;
            
            const dx = x - centerX;
            const dy = y - centerY;
            const distance = Math.sqrt(dx * dx + dy * dy);
            
            if (distance <= radius) {
                const angle = (Math.atan2(dy, dx) * 180 / Math.PI + 360) % 360;
                const h = angle;
                const s = (distance / radius) * 100;
                const l = 50;
                
                this.selectedColor = { h, s, l };
                this.setupParticles();
                this.updatePaletteDisplay();
                
                if (this.audioContext) {
                    this.playColorSound(this.selectedColor);
                }
            }
        }
        
        previewColorAt(x, y) {
            // Preview color on hover (optional)
        }
        
        generateRandomHarmony() {
            this.selectedColor = {
                h: Math.random() * 360,
                s: 30 + Math.random() * 70,
                l: 30 + Math.random() * 40
            };
            
            const types = ['complementary', 'analogous', 'triadic', 'split-complementary', 'tetradic', 'monochromatic'];
            const randomType = types[Math.floor(Math.random() * types.length)];
            
            this.setHarmonyType(randomType);
            
            // Update UI
            const buttons = this.container.querySelectorAll('.harmony-btn');
            buttons.forEach(b => {
                b.classList.toggle('active', b.dataset.type === randomType);
            });
        }
        
        exportPalette() {
            const harmony = this.getHarmony(this.selectedColor, this.harmonyType);
            const palette = harmony.map(c => this.hslToHex(c.h, c.s, c.l));
            
            // Export as JSON
            const data = {
                harmony: this.harmonyType,
                colors: palette,
                hsl: harmony
            };
            
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `palette-${this.harmonyType}-${Date.now()}.json`;
            a.click();
            URL.revokeObjectURL(url);
        }
        
        savePalette() {
            const harmony = this.getHarmony(this.selectedColor, this.harmonyType);
            const palette = harmony.map(c => this.hslToHex(c.h, c.s, c.l));
            
            try {
                const saved = JSON.parse(localStorage.getItem('savedPalettes') || '[]');
                saved.push({
                    id: Date.now(),
                    harmony: this.harmonyType,
                    colors: palette,
                    hsl: harmony,
                    date: new Date().toISOString()
                });
                localStorage.setItem('savedPalettes', JSON.stringify(saved));
                
                // Show feedback
                const btn = this.container.querySelector('#save-palette');
                if (btn) {
                    const original = btn.innerHTML;
                    btn.innerHTML = '<span>✅</span> Salvato!';
                    setTimeout(() => {
                        btn.innerHTML = original;
                    }, 2000);
                }
            } catch (e) {
                console.error('Failed to save palette:', e);
            }
        }
        
        updatePaletteDisplay() {
            const display = this.container.querySelector('#palette-display');
            if (!display) return;
            
            const harmony = this.getHarmony(this.selectedColor, this.harmonyType);
            display.innerHTML = harmony.map((color, index) => {
                const hex = this.hslToHex(color.h, color.s, color.l);
                return `
                    <div class="palette-color" style="background: hsl(${color.h}, ${color.s}%, ${color.l}%)" 
                         data-hex="${hex}" data-index="${index}">
                        <span class="color-hex">${hex}</span>
                        <span class="color-hsl">HSL(${Math.round(color.h)}, ${Math.round(color.s)}%, ${Math.round(color.l)}%)</span>
                    </div>
                `;
            }).join('');
        }
        
        drawColorWheel() {
            const centerX = this.canvas.width / 2;
            const centerY = this.canvas.height / 2;
            const radius = Math.min(this.canvas.width, this.canvas.height) / 3;
            
            // Draw color wheel
            for (let angle = 0; angle < 360; angle += 1) {
                for (let r = 0; r < radius; r += 2) {
                    const h = angle;
                    const s = (r / radius) * 100;
                    const l = 50;
                    
                    this.ctx.fillStyle = `hsl(${h}, ${s}%, ${l}%)`;
                    this.ctx.beginPath();
                    this.ctx.arc(
                        centerX + Math.cos(angle * Math.PI / 180) * r,
                        centerY + Math.sin(angle * Math.PI / 180) * r,
                        2, 0, Math.PI * 2
                    );
                    this.ctx.fill();
                }
            }
        }
        
        drawHarmony() {
            const harmony = this.getHarmony(this.selectedColor, this.harmonyType);
            const centerX = this.canvas.width / 2;
            const centerY = this.canvas.height / 2;
            const radius = Math.min(this.canvas.width, this.canvas.height) / 3;
            
            // Draw harmony lines
            this.ctx.strokeStyle = 'rgba(255, 255, 255, 0.5)';
            this.ctx.lineWidth = 2;
            
            harmony.forEach((color, index) => {
                const angle = (color.h * Math.PI / 180);
                const distance = (color.s / 100) * radius;
                const x = centerX + Math.cos(angle) * distance;
                const y = centerY + Math.sin(angle) * distance;
                
                // Draw line from center
                this.ctx.beginPath();
                this.ctx.moveTo(centerX, centerY);
                this.ctx.lineTo(x, y);
                this.ctx.stroke();
                
                // Draw color circle
                this.ctx.fillStyle = `hsl(${color.h}, ${color.s}%, ${color.l}%)`;
                this.ctx.beginPath();
                this.ctx.arc(x, y, 15, 0, Math.PI * 2);
                this.ctx.fill();
                this.ctx.strokeStyle = 'white';
                this.ctx.lineWidth = 2;
                this.ctx.stroke();
            });
        }
        
        animate() {
            if (this.options.reducedMotion) {
                // Reduced motion: update less frequently
                if (!this.isPlaying) {
                    this.isPlaying = true;
                    setTimeout(() => {
                        this.update();
                        this.isPlaying = false;
                    }, 100);
                }
            } else {
                this.update();
            }
            
            this.animationId = requestAnimationFrame(() => this.animate());
        }
        
        // Enhanced particle interactions
        enhanceParticleInteractions() {
            this.particles.forEach((particle, i) => {
                this.particles.slice(i + 1).forEach(other => {
                    const dx = other.x - particle.x;
                    const dy = other.y - particle.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);
                    
                    if (distance > 0 && distance < 30) {
                        // Attraction for same color group
                        if (particle.groupIndex === other.groupIndex) {
                            const force = (30 - distance) / 30 * 0.01;
                            particle.vx += (dx / distance) * force;
                            particle.vy += (dy / distance) * force;
                        } else {
                            // Repulsion for different colors
                            const force = (30 - distance) / 30 * 0.02;
                            particle.vx -= (dx / distance) * force;
                            particle.vy -= (dy / distance) * force;
                        }
                    }
                });
            });
        }
        
        update() {
            // Clear canvas
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            
            // Redraw color wheel
            this.drawColorWheel();
            
            // Enhanced particle interactions
            this.enhanceParticleInteractions();
            
            // Update particles
            this.particles.forEach(particle => {
                particle.update(this.canvas.width, this.canvas.height);
                particle.draw(this.ctx);
            });
            
            // Draw harmony
            this.drawHarmony();
        }
        
        playColorSound(color) {
            if (!this.audioContext) return;
            
            const frequency = 200 + (color.h / 360) * 1800;
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);
            
            oscillator.frequency.value = frequency;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.5);
            
            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.5);
        }
        
        playHarmonySound(harmony) {
            if (!this.audioContext) return;
            
            harmony.forEach((color, index) => {
                setTimeout(() => {
                    this.playColorSound(color);
                }, index * 100);
            });
        }
        
        handleKeyboard(e) {
            switch(e.key) {
                case 'ArrowLeft':
                    this.selectedColor.h = (this.selectedColor.h - 10 + 360) % 360;
                    this.setupParticles();
                    this.updatePaletteDisplay();
                    break;
                case 'ArrowRight':
                    this.selectedColor.h = (this.selectedColor.h + 10) % 360;
                    this.setupParticles();
                    this.updatePaletteDisplay();
                    break;
                case ' ':
                    e.preventDefault();
                    this.generateRandomHarmony();
                    break;
                case 'Enter':
                    this.exportPalette();
                    break;
            }
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
        
        destroy() {
            if (this.animationId) {
                cancelAnimationFrame(this.animationId);
            }
            if (this.audioContext) {
                this.audioContext.close();
            }
        }
    }
    
    // Particle class
    class Particle {
        constructor(color, groupIndex, canvasWidth, canvasHeight) {
            this.color = color;
            this.groupIndex = groupIndex;
            this.x = Math.random() * canvasWidth;
            this.y = Math.random() * canvasHeight;
            this.vx = (Math.random() - 0.5) * 2;
            this.vy = (Math.random() - 0.5) * 2;
            this.size = Math.random() * 3 + 1;
            this.life = 1.0;
        }
        
        update(canvasWidth, canvasHeight) {
            this.x += this.vx;
            this.y += this.vy;
            
            // Bounce boundaries
            if (this.x < 0 || this.x > canvasWidth) this.vx *= -1;
            if (this.y < 0 || this.y > canvasHeight) this.vy *= -1;
            
            // Keep in bounds
            this.x = Math.max(0, Math.min(canvasWidth, this.x));
            this.y = Math.max(0, Math.min(canvasHeight, this.y));
        }
        
        draw(ctx) {
            ctx.fillStyle = `hsla(${this.color.h}, ${this.color.s}%, ${this.color.l}%, 0.6)`;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
        }
    }
    
    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.color-harmony-visualizer');
        containers.forEach(container => {
            const options = {};
            Array.from(container.attributes).forEach(attr => {
                if (attr.name.startsWith('data-')) {
                    const key = attr.name.replace('data-', '').replace(/-/g, '-');
                    options[key] = attr.value;
                }
            });
            
            new ColorHarmonyVisualizer(container, options);
        });
    });
    
    // Export for global access
    window.ColorHarmonyVisualizer = ColorHarmonyVisualizer;
})();

