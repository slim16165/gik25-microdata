/**
 * Pantone Hub Dinamico
 * Visualizzazione avanzata colori Pantone con timeline
 */

(function() {
    'use strict';
    
    class PantoneHubDynamic {
        constructor(container, options = {}) {
            this.container = container;
            this.year = options.year || '';
            this.limit = parseInt(options.limit) || 20;
            this.pantoneColors = [];
            
            this.init();
        }
        
        init() {
            this.loadPantoneColors();
            this.render();
        }
        
        loadPantoneColors() {
            // Simula colori Pantone (in produzione verrebbero da WordPress)
            const years = [2024, 2023, 2022, 2021, 2020, 2019, 2018];
            const colorNames = [
                'Peach Fuzz', 'Viva Magenta', 'Very Peri', 'Ultimate Gray', 'Classic Blue',
                'Living Coral', 'Ultra Violet', 'Greenery', 'Rose Quartz', 'Marsala'
            ];
            
            years.forEach((year, index) => {
                this.pantoneColors.push({
                    year: year,
                    name: colorNames[index % colorNames.length],
                    hex: this.generateColorForYear(year),
                    hsl: this.hexToHsl(this.generateColorForYear(year))
                });
            });
        }
        
        generateColorForYear(year) {
            // Genera colore basato su anno (per demo)
            const hue = (year - 2018) * 30 % 360;
            return this.hslToHex(hue, 70, 50);
        }
        
        render() {
            const timeline = this.container.querySelector('.pantone-timeline');
            const colors = this.container.querySelector('.pantone-colors');
            
            if (!timeline || !colors) return;
            
            // Render timeline with D3.js
            if (typeof d3 !== 'undefined') {
                this.renderTimeline(timeline);
            }
            
            // Render colors
            colors.innerHTML = this.pantoneColors.map((color, index) => {
                return `
                    <div class="pantone-color-card" data-year="${color.year}">
                        <div class="pantone-swatch" style="background: ${color.hex}"></div>
                        <div class="pantone-info">
                            <h4>${color.name}</h4>
                            <p class="pantone-year">${color.year}</p>
                            <p class="pantone-hex">${color.hex}</p>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Animate with GSAP
            if (typeof gsap !== 'undefined') {
                const cards = colors.querySelectorAll('.pantone-color-card');
                cards.forEach((card, index) => {
                    gsap.fromTo(card,
                        { opacity: 0, scale: 0.8, y: 30 },
                        { opacity: 1, scale: 1, y: 0, duration: 0.6, delay: index * 0.1, ease: 'back.out(1.7)' }
                    );
                });
            }
        }
        
        renderTimeline(container) {
            const width = container.clientWidth || 800;
            const height = 200;
            
            const svg = d3.select(container)
                .append('svg')
                .attr('width', width)
                .attr('height', height);
            
            const xScale = d3.scaleLinear()
                .domain(d3.extent(this.pantoneColors, d => d.year))
                .range([50, width - 50]);
            
            const line = d3.line()
                .x(d => xScale(d.year))
                .y((d, i) => height / 2 + Math.sin(i) * 30)
                .curve(d3.curveCardinal);
            
            svg.append('path')
                .datum(this.pantoneColors)
                .attr('fill', 'none')
                .attr('stroke', '#fff')
                .attr('stroke-width', 2)
                .attr('d', line);
            
            this.pantoneColors.forEach((color, index) => {
                const g = svg.append('g')
                    .attr('transform', `translate(${xScale(color.year)}, ${height / 2})`);
                
                g.append('circle')
                    .attr('r', 8)
                    .attr('fill', color.hex)
                    .attr('stroke', '#fff')
                    .attr('stroke-width', 2)
                    .on('mouseenter', function() {
                        d3.select(this).attr('r', 12);
                    })
                    .on('mouseleave', function() {
                        d3.select(this).attr('r', 8);
                    });
                
                g.append('text')
                    .attr('y', -15)
                    .attr('text-anchor', 'middle')
                    .attr('fill', '#fff')
                    .attr('font-size', '12px')
                    .text(color.year);
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
        
        hexToHsl(hex) {
            const r = parseInt(hex.slice(1, 3), 16) / 255;
            const g = parseInt(hex.slice(3, 5), 16) / 255;
            const b = parseInt(hex.slice(5, 7), 16) / 255;
            
            const max = Math.max(r, g, b);
            const min = Math.min(r, g, b);
            let h, s, l = (max + min) / 2;
            
            if (max === min) {
                h = s = 0;
            } else {
                const d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                
                switch(max) {
                    case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
                    case g: h = ((b - r) / d + 2) / 6; break;
                    case b: h = ((r - g) / d + 4) / 6; break;
                }
            }
            
            return { h: h * 360, s: s * 100, l: l * 100 };
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.pantone-hub');
        containers.forEach(container => {
            const options = {
                year: container.dataset.year || '',
                limit: parseInt(container.dataset.limit) || 20
            };
            
            new PantoneHubDynamic(container, options);
        });
    });
    
    window.PantoneHubDynamic = PantoneHubDynamic;
})();

