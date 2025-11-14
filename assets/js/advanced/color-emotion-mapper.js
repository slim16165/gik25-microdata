(function() {
    'use strict';
    
    class ColorEmotionMapper {
        constructor(container) {
            this.container = container;
            this.emotions = {
                happy: { color: '#FFD700', x: 0.7, y: 0.3 },
                sad: { color: '#2196F3', x: 0.3, y: 0.7 },
                angry: { color: '#F44336', x: 0.2, y: 0.2 },
                calm: { color: '#4CAF50', x: 0.8, y: 0.8 },
                excited: { color: '#FF5722', x: 0.9, y: 0.1 },
                peaceful: { color: '#00BCD4', x: 0.5, y: 0.9 }
            };
            this.init();
        }
        
        init() {
            if (typeof d3 === 'undefined') return;
            this.render();
        }
        
        render() {
            const canvas = this.container.querySelector('#mapper-canvas');
            if (!canvas) return;
            
            const width = canvas.clientWidth || 600;
            const height = 400;
            
            const svg = d3.select(canvas)
                .append('svg')
                .attr('width', width)
                .attr('height', height);
            
            Object.entries(this.emotions).forEach(([emotion, data]) => {
                const g = svg.append('g');
                g.append('circle')
                    .attr('cx', data.x * width)
                    .attr('cy', data.y * height)
                    .attr('r', 30)
                    .attr('fill', data.color)
                    .attr('opacity', 0.7);
                g.append('text')
                    .attr('x', data.x * width)
                    .attr('y', data.y * height + 5)
                    .attr('text-anchor', 'middle')
                    .attr('fill', 'white')
                    .text(emotion);
            });
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.color-emotion-mapper').forEach(container => {
            new ColorEmotionMapper(container);
        });
    });
    
    window.ColorEmotionMapper = ColorEmotionMapper;
})();

