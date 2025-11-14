/**
 * Mood Color Tracker
 * Traccia umore e associa colori per psicocultura.it
 */

(function() {
    'use strict';
    
    class MoodColorTracker {
        constructor(container, options = {}) {
            this.container = container;
            this.days = parseInt(options.days) || 30;
            this.moods = this.loadMoods();
            this.chart = null;
            
            this.init();
        }
        
        init() {
            this.setupEventListeners();
            this.renderChart();
            this.updateInsights();
        }
        
        loadMoods() {
            const stored = localStorage.getItem('mood-tracker-data');
            if (stored) {
                return JSON.parse(stored);
            }
            return [];
        }
        
        saveMoods() {
            localStorage.setItem('mood-tracker-data', JSON.stringify(this.moods));
        }
        
        setupEventListeners() {
            const moodBtns = this.container.querySelectorAll('.mood-btn');
            moodBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const mood = e.target.dataset.mood;
                    const color = e.target.dataset.color;
                    this.recordMood(mood, color);
                });
            });
        }
        
        recordMood(mood, color) {
            const entry = {
                date: new Date().toISOString(),
                mood: mood,
                color: color
            };
            
            this.moods.push(entry);
            
            // Keep only last N days
            const cutoff = new Date();
            cutoff.setDate(cutoff.getDate() - this.days);
            this.moods = this.moods.filter(m => new Date(m.date) >= cutoff);
            
            this.saveMoods();
            this.renderChart();
            this.updateInsights();
            
            // Visual feedback
            if (typeof gsap !== 'undefined') {
                gsap.fromTo(this.container.querySelector('.mood-chart'),
                    { scale: 0.95 },
                    { scale: 1, duration: 0.3, ease: 'back.out(1.7)' }
                );
            }
        }
        
        renderChart() {
            const chartContainer = this.container.querySelector('#mood-chart');
            if (!chartContainer) return;
            
            if (typeof d3 === 'undefined') {
                chartContainer.innerHTML = '<p>D3.js required for chart</p>';
                return;
            }
            
            // Clear previous chart
            d3.select(chartContainer).selectAll('*').remove();
            
            const width = chartContainer.clientWidth || 600;
            const height = 300;
            const margin = { top: 20, right: 20, bottom: 40, left: 40 };
            
            const svg = d3.select(chartContainer)
                .append('svg')
                .attr('width', width)
                .attr('height', height);
            
            const xScale = d3.scaleTime()
                .domain(d3.extent(this.moods, d => new Date(d.date)))
                .range([margin.left, width - margin.right]);
            
            const yScale = d3.scaleLinear()
                .domain([0, 100])
                .range([height - margin.bottom, margin.top]);
            
            // Line chart
            const line = d3.line()
                .x(d => xScale(new Date(d.date)))
                .y((d, i) => yScale(i * 10))
                .curve(d3.curveCardinal);
            
            svg.append('path')
                .datum(this.moods)
                .attr('fill', 'none')
                .attr('stroke', '#fff')
                .attr('stroke-width', 2)
                .attr('d', line);
            
            // Points
            svg.selectAll('circle')
                .data(this.moods)
                .enter()
                .append('circle')
                .attr('cx', d => xScale(new Date(d.date)))
                .attr('cy', (d, i) => yScale(i * 10))
                .attr('r', 5)
                .attr('fill', d => d.color)
                .attr('stroke', '#fff')
                .attr('stroke-width', 2);
        }
        
        updateInsights() {
            const insightsContainer = this.container.querySelector('#mood-insights');
            if (!insightsContainer) return;
            
            if (this.moods.length === 0) {
                insightsContainer.innerHTML = '<p>Inizia a tracciare il tuo umore per vedere le insights!</p>';
                return;
            }
            
            // Calculate insights
            const moodCounts = {};
            this.moods.forEach(m => {
                moodCounts[m.mood] = (moodCounts[m.mood] || 0) + 1;
            });
            
            const mostCommon = Object.entries(moodCounts)
                .sort((a, b) => b[1] - a[1])[0];
            
            const avgMood = this.calculateAverageMood();
            
            insightsContainer.innerHTML = `
                <div class="insight-card">
                    <h4>Umore più frequente</h4>
                    <p>${this.getMoodLabel(mostCommon[0])}: ${mostCommon[1]} giorni</p>
                </div>
                <div class="insight-card">
                    <h4>Media umore</h4>
                    <p>${avgMood.toFixed(1)}/10</p>
                </div>
                <div class="insight-card">
                    <h4>Giorni tracciati</h4>
                    <p>${this.moods.length} giorni</p>
                </div>
            `;
        }
        
        calculateAverageMood() {
            const moodValues = {
                happy: 9,
                calm: 7,
                energetic: 8,
                sad: 3,
                anxious: 4,
                peaceful: 8
            };
            
            const sum = this.moods.reduce((acc, m) => acc + (moodValues[m.mood] || 5), 0);
            return sum / this.moods.length;
        }
        
        getMoodLabel(mood) {
            const labels = {
                happy: 'Felice',
                calm: 'Calmo',
                energetic: 'Energico',
                sad: 'Triste',
                anxious: 'Ansioso',
                peaceful: 'Sereno'
            };
            return labels[mood] || mood;
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.mood-color-tracker');
        containers.forEach(container => {
            const options = {
                days: parseInt(container.dataset.days) || 30,
                showChart: container.dataset.showChart !== 'false'
            };
            
            new MoodColorTracker(container, options);
        });
    });
    
    window.MoodColorTracker = MoodColorTracker;
})();

