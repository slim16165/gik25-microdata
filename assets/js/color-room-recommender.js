/**
 * Color Room Recommender
 * Sistema di raccomandazioni colore-stanza con visualizzazione avanzata
 */

(function() {
    'use strict';
    
    class ColorRoomRecommender {
        constructor(container, options = {}) {
            this.container = container;
            this.color = options.color || '';
            this.room = options.room || '';
            this.recommendations = [];
            
            this.init();
        }
        
        init() {
            this.loadRecommendations();
            this.render();
        }
        
        loadRecommendations() {
            // Popular combinations
            const popular = [
                { color: 'verde-salvia', room: 'cucina', score: 95 },
                { color: 'tortora', room: 'soggiorno', score: 92 },
                { color: 'bianco', room: 'camera', score: 90 },
                { color: 'grigio', room: 'studio', score: 88 },
                { color: 'beige', room: 'soggiorno', score: 85 },
            ];
            
            if (this.color && this.room) {
                // Filter by color and room
                this.recommendations = popular.filter(r => 
                    r.color === this.color || r.room === this.room
                );
            } else {
                this.recommendations = popular;
            }
        }
        
        render() {
            const viz = this.container.querySelector('.recommender-visualization');
            const suggestions = this.container.querySelector('.recommender-suggestions');
            
            if (!viz || !suggestions) return;
            
            // Render visualization with D3.js
            if (typeof d3 !== 'undefined') {
                this.renderD3Visualization(viz);
            }
            
            // Render suggestions
            suggestions.innerHTML = this.recommendations.map((rec, index) => {
                return `
                    <div class="recommendation-item" data-score="${rec.score}">
                        <div class="recommendation-color" style="background: var(--color-${rec.color})"></div>
                        <div class="recommendation-info">
                            <h4>${rec.color.charAt(0).toUpperCase() + rec.color.slice(1)} in ${rec.room.charAt(0).toUpperCase() + rec.room.slice(1)}</h4>
                            <div class="recommendation-score">
                                <span class="score-bar" style="width: ${rec.score}%"></span>
                                <span class="score-text">${rec.score}% Match</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Animate with GSAP
            if (typeof gsap !== 'undefined') {
                const items = suggestions.querySelectorAll('.recommendation-item');
                items.forEach((item, index) => {
                    gsap.fromTo(item,
                        { opacity: 0, y: 20 },
                        { opacity: 1, y: 0, duration: 0.5, delay: index * 0.1 }
                    );
                });
            }
        }
        
        renderD3Visualization(container) {
            const width = container.clientWidth || 600;
            const height = 300;
            
            const svg = d3.select(container)
                .append('svg')
                .attr('width', width)
                .attr('height', height);
            
            // Create force simulation
            const simulation = d3.forceSimulation(this.recommendations)
                .force('charge', d3.forceManyBody().strength(-100))
                .force('center', d3.forceCenter(width / 2, height / 2))
                .force('collision', d3.forceCollide().radius(30));
            
            const nodes = svg.selectAll('circle')
                .data(this.recommendations)
                .enter()
                .append('circle')
                .attr('r', d => d.score / 2)
                .attr('fill', (d, i) => d3.schemeCategory10[i % 10])
                .call(this.drag(simulation));
            
            simulation.on('tick', () => {
                nodes
                    .attr('cx', d => d.x)
                    .attr('cy', d => d.y);
            });
        }
        
        drag(simulation) {
            function dragstarted(event) {
                if (!event.active) simulation.alphaTarget(0.3).restart();
                event.subject.fx = event.subject.x;
                event.subject.fy = event.subject.y;
            }
            
            function dragged(event) {
                event.subject.fx = event.x;
                event.subject.fy = event.y;
            }
            
            function dragended(event) {
                if (!event.active) simulation.alphaTarget(0);
                event.subject.fx = null;
                event.subject.fy = null;
            }
            
            return d3.drag()
                .on('start', dragstarted)
                .on('drag', dragged)
                .on('end', dragended);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.color-room-recommender');
        containers.forEach(container => {
            const options = {
                color: container.dataset.color || '',
                room: container.dataset.room || ''
            };
            
            new ColorRoomRecommender(container, options);
        });
    });
    
    window.ColorRoomRecommender = ColorRoomRecommender;
})();

