(function() {
    'use strict';
    
    class MeditationTimerColors {
        constructor(container) {
            this.container = container;
            this.timeLeft = 300; // 5 min default
            this.isRunning = false;
            this.interval = null;
            this.canvas = container.querySelector('#timer-viz');
            this.ctx = this.canvas?.getContext('2d');
            
            this.init();
        }
        
        init() {
            this.setupCanvas();
            this.setupEventListeners();
            this.draw();
        }
        
        setupCanvas() {
            if (!this.canvas) return;
            const dpr = window.devicePixelRatio || 1;
            const rect = this.canvas.getBoundingClientRect();
            this.canvas.width = rect.width * dpr;
            this.canvas.height = rect.height * dpr;
            this.canvas.style.width = rect.width + 'px';
            this.canvas.style.height = rect.height + 'px';
            this.ctx.scale(dpr, dpr);
        }
        
        setupEventListeners() {
            this.container.querySelectorAll('.timer-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    this.timeLeft = parseInt(e.target.dataset.time) * 60;
                    this.updateDisplay();
                });
            });
            
            this.container.querySelector('#start-btn')?.addEventListener('click', () => this.start());
            this.container.querySelector('#pause-btn')?.addEventListener('click', () => this.pause());
            this.container.querySelector('#reset-btn')?.addEventListener('click', () => this.reset());
        }
        
        start() {
            if (this.isRunning) return;
            this.isRunning = true;
            this.interval = setInterval(() => {
                this.timeLeft--;
                this.updateDisplay();
                this.draw();
                if (this.timeLeft <= 0) {
                    this.complete();
                }
            }, 1000);
            
            this.container.querySelector('#start-btn').style.display = 'none';
            this.container.querySelector('#pause-btn').style.display = 'inline-block';
        }
        
        pause() {
            this.isRunning = false;
            if (this.interval) clearInterval(this.interval);
            this.container.querySelector('#start-btn').style.display = 'inline-block';
            this.container.querySelector('#pause-btn').style.display = 'none';
        }
        
        reset() {
            this.pause();
            this.timeLeft = 300;
            this.updateDisplay();
            this.draw();
        }
        
        complete() {
            this.pause();
            alert('Meditazione completata! 🧘');
        }
        
        updateDisplay() {
            const mins = Math.floor(this.timeLeft / 60);
            const secs = this.timeLeft % 60;
            const display = this.container.querySelector('#timer-display');
            if (display) {
                display.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            }
        }
        
        draw() {
            if (!this.ctx) return;
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            
            const centerX = this.canvas.width / 2;
            const centerY = this.canvas.height / 2;
            const radius = Math.min(this.canvas.width, this.canvas.height) / 3;
            
            const progress = this.timeLeft / 300;
            const hue = progress * 120; // Green to red
            
            this.ctx.strokeStyle = `hsl(${hue}, 70%, 50%)`;
            this.ctx.lineWidth = 10;
            this.ctx.beginPath();
            this.ctx.arc(centerX, centerY, radius, -Math.PI/2, -Math.PI/2 + (1-progress) * Math.PI * 2);
            this.ctx.stroke();
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.meditation-timer-colors').forEach(container => {
            new MeditationTimerColors(container);
        });
    });
    
    window.MeditationTimerColors = MeditationTimerColors;
})();

