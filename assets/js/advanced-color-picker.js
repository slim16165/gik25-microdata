/**
 * Advanced Color Picker
 * Color picker avanzato con multiple features
 */

(function() {
    'use strict';
    
    class AdvancedColorPicker {
        constructor(container, options = {}) {
            this.container = container;
            this.canvas = container.querySelector('.picker-canvas');
            this.ctx = this.canvas.getContext('2d');
            this.color = { h: 0, s: 50, l: 50 };
            
            this.init();
        }
        
        init() {
            this.setupCanvas();
            this.setupEventListeners();
            this.draw();
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
            const hueSlider = this.container.querySelector('#hue-slider');
            const satSlider = this.container.querySelector('#saturation-slider');
            const lightSlider = this.container.querySelector('#lightness-slider');
            
            if (hueSlider) {
                hueSlider.addEventListener('input', (e) => {
                    this.color.h = parseFloat(e.target.value);
                    this.update();
                });
            }
            
            if (satSlider) {
                satSlider.addEventListener('input', (e) => {
                    this.color.s = parseFloat(e.target.value);
                    this.update();
                });
            }
            
            if (lightSlider) {
                lightSlider.addEventListener('input', (e) => {
                    this.color.l = parseFloat(e.target.value);
                    this.update();
                });
            }
            
            // Canvas click
            this.canvas.addEventListener('click', (e) => {
                const rect = this.canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                this.selectColorAt(x, y);
            });
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
                this.color.h = angle;
                this.color.s = (distance / radius) * 100;
                this.update();
            }
        }
        
        draw() {
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
        
        update() {
            this.updateSliders();
            this.updateDisplay();
            this.draw();
        }
        
        updateSliders() {
            const hueSlider = this.container.querySelector('#hue-slider');
            const satSlider = this.container.querySelector('#saturation-slider');
            const lightSlider = this.container.querySelector('#lightness-slider');
            
            if (hueSlider) hueSlider.value = this.color.h;
            if (satSlider) satSlider.value = this.color.s;
            if (lightSlider) lightSlider.value = this.color.l;
        }
        
        updateDisplay() {
            const hex = this.hslToHex(this.color.h, this.color.s, this.color.l);
            const rgb = this.hslToRgb(this.color.h, this.color.s, this.color.l);
            
            const preview = this.container.querySelector('#color-preview');
            const hexInput = this.container.querySelector('#hex-input');
            const rgbInput = this.container.querySelector('#rgb-input');
            const hslInput = this.container.querySelector('#hsl-input');
            
            if (preview) {
                preview.style.background = `hsl(${this.color.h}, ${this.color.s}%, ${this.color.l}%)`;
            }
            
            if (hexInput) hexInput.value = hex;
            if (rgbInput) rgbInput.value = `RGB(${rgb.r}, ${rgb.g}, ${rgb.b})`;
            if (hslInput) hslInput.value = `HSL(${Math.round(this.color.h)}, ${Math.round(this.color.s)}%, ${Math.round(this.color.l)}%)`;
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
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.advanced-color-picker');
        containers.forEach(container => {
            new AdvancedColorPicker(container);
        });
    });
    
    window.AdvancedColorPicker = AdvancedColorPicker;
})();

