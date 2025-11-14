/**
 * Stress Relief Colors
 * Widget per ridurre stress con colori
 */

(function() {
    'use strict';
    
    class StressReliefColors {
        constructor(container) {
            this.container = container;
            this.currentLevel = null;
            this.canvas = null;
            this.ctx = null;
            this.particles = [];
            
            this.init();
        }
        
        init() {
            this.setupCanvas();
            this.setupEventListeners();
        }
        
        setupCanvas() {
            const viz = this.container.querySelector('#relief-viz');
            if (!viz) return;
            
            this.canvas = document.createElement('canvas');
            this.canvas.width = viz.clientWidth || 600;
            this.canvas.height = 400;
            this.ctx = this.canvas.getContext('2d');
            viz.appendChild(this.canvas);
        }
        
        setupEventListeners() {
            const buttons = this.container.querySelectorAll('.stress-btn');
            buttons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const level = e.target.dataset.level;
                    this.selectStressLevel(level);
                    buttons.forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');
                });
            });
        }
        
        selectStressLevel(level) {
            this.currentLevel = level;
            
            const colors = {
                'low': { primary: '#4CAF50', secondary: '#81C784' },
                'medium': { primary: '#FF9800', secondary: '#FFB74D' },
                'high': { primary: '#F44336', secondary: '#E57373' },
                'very-high': { primary: '#9C27B0', secondary: '#BA68C8' }
            };
            
            const reliefColors = {
                'low': '#E8F5E9',
                'medium': '#FFF3E0',
                'high': '#FFEBEE',
                'very-high': '#F3E5F5'
            };
            
            const color = colors[level];
            const relief = reliefColors[level];
            
            this.startReliefAnimation(color, relief);
            this.showTips(level);
        }
        
        startReliefAnimation(color, relief) {
            // Create particles
            this.particles = [];
            for (let i = 0; i < 50; i++) {
                this.particles.push({
                    x: Math.random() * this.canvas.width,
                    y: Math.random() * this.canvas.height,
                    vx: (Math.random() - 0.5) * 2,
                    vy: (Math.random() - 0.5) * 2,
                    size: Math.random() * 5 + 2,
                    color: Math.random() > 0.5 ? color.primary : color.secondary,
                    life: 1.0
                });
            }
            
            // Animate
            this.animate();
        }
        
        animate() {
            if (!this.ctx) return;
            
            // Fade out previous frame
            this.ctx.fillStyle = 'rgba(10, 10, 10, 0.1)';
            this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
            
            // Update and draw particles
            this.particles.forEach(particle => {
                particle.x += particle.vx;
                particle.y += particle.vy;
                particle.life -= 0.01;
                
                if (particle.x < 0 || particle.x > this.canvas.width) particle.vx *= -1;
                if (particle.y < 0 || particle.y > this.canvas.height) particle.vy *= -1;
                
                this.ctx.fillStyle = particle.color;
                this.ctx.globalAlpha = particle.life;
                this.ctx.beginPath();
                this.ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
                this.ctx.fill();
            });
            
            this.ctx.globalAlpha = 1.0;
            
            if (this.particles.some(p => p.life > 0)) {
                requestAnimationFrame(() => this.animate());
            }
        }
        
        showTips(level) {
            const tipsContainer = this.container.querySelector('#relief-tips');
            if (!tipsContainer) return;
            
            const tips = {
                'low': [
                    'Mantieni questo livello di calma',
                    'Usa colori verdi per mantenere l\'equilibrio',
                    'Pratica respirazione profonda regolarmente'
                ],
                'medium': [
                    'Pratica esercizi di respirazione',
                    'Usa colori blu e verde per rilassarti',
                    'Fai una pausa di 5 minuti'
                ],
                'high': [
                    'Respira profondamente per 2 minuti',
                    'Usa colori blu scuro e viola per calmarti',
                    'Pratica meditazione guidata',
                    'Fai stretching leggero'
                ],
                'very-high': [
                    'Ferma tutto e respira profondamente',
                    'Usa colori viola e blu per rilassamento profondo',
                    'Pratica tecniche di grounding',
                    'Considera una pausa più lunga',
                    'Ascolta musica rilassante'
                ]
            };
            
            const levelTips = tips[level] || [];
            
            tipsContainer.innerHTML = `
                <h4>Suggerimenti per ridurre lo stress:</h4>
                <ul>
                    ${levelTips.map(tip => `<li>${tip}</li>`).join('')}
                </ul>
            `;
            
            if (typeof gsap !== 'undefined') {
                gsap.fromTo(tipsContainer,
                    { opacity: 0, y: 20 },
                    { opacity: 1, y: 0, duration: 0.5 }
                );
            }
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.stress-relief-colors');
        containers.forEach(container => {
            new StressReliefColors(container);
        });
    });
    
    window.StressReliefColors = StressReliefColors;
})();

