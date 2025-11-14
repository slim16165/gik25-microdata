/**
 * Color Therapy Visualizer
 * Visualizzatore terapia colori per psicocultura.it
 */

(function() {
    'use strict';
    
    class ColorTherapyVisualizer {
        constructor(container, options = {}) {
            this.container = container;
            this.mode = options.mode || 'breathing';
            this.scene = null;
            this.camera = null;
            this.renderer = null;
            this.particles = [];
            this.isActive = false;
            
            if (typeof THREE === 'undefined') {
                console.error('Three.js required');
                return;
            }
            
            this.init();
        }
        
        init() {
            this.setupScene();
            this.setupCamera();
            this.setupRenderer();
            this.setupLighting();
            this.setupEventListeners();
            this.setMode(this.mode);
            this.animate();
        }
        
        setupScene() {
            this.scene = new THREE.Scene();
            this.scene.background = new THREE.Color(0x0a0a0a);
        }
        
        setupCamera() {
            const width = this.container.clientWidth;
            const height = this.container.clientHeight;
            
            this.camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 1000);
            this.camera.position.set(0, 0, 10);
        }
        
        setupRenderer() {
            this.renderer = new THREE.WebGLRenderer({ antialias: true });
            this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
            this.renderer.shadowMap.enabled = true;
            this.container.appendChild(this.renderer.domElement);
        }
        
        setupLighting() {
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
            this.scene.add(ambientLight);
            
            const pointLight = new THREE.PointLight(0xffffff, 1);
            pointLight.position.set(0, 0, 10);
            this.scene.add(pointLight);
        }
        
        setupEventListeners() {
            const buttons = this.container.querySelectorAll('.therapy-btn');
            buttons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const mode = e.target.dataset.mode;
                    this.setMode(mode);
                    buttons.forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');
                });
            });
        }
        
        setMode(mode) {
            this.mode = mode;
            this.clearParticles();
            
            const modes = {
                breathing: {
                    color: 0x4CAF50,
                    pattern: 'pulse',
                    speed: 0.02,
                    title: 'Respirazione Consapevole',
                    description: 'Segui il ritmo del respiro con il colore verde'
                },
                meditation: {
                    color: 0x9C27B0,
                    pattern: 'spiral',
                    speed: 0.01,
                    title: 'Meditazione Guidata',
                    description: 'Lascia che il viola ti guidi nella meditazione'
                },
                focus: {
                    color: 0x2196F3,
                    pattern: 'concentric',
                    speed: 0.03,
                    title: 'Focus e Concentrazione',
                    description: 'Il blu aiuta la concentrazione'
                },
                relax: {
                    color: 0x00BCD4,
                    pattern: 'wave',
                    speed: 0.015,
                    title: 'Rilassamento Profondo',
                    description: 'Il ciano favorisce il rilassamento'
                }
            };
            
            const config = modes[mode] || modes.breathing;
            this.createParticles(config);
            
            // Update UI
            const title = this.container.querySelector('#therapy-title');
            const desc = this.container.querySelector('#therapy-description');
            if (title) title.textContent = config.title;
            if (desc) desc.textContent = config.description;
            
            this.isActive = true;
        }
        
        createParticles(config) {
            const count = 100;
            
            for (let i = 0; i < count; i++) {
                const geometry = new THREE.SphereGeometry(0.1, 16, 16);
                const material = new THREE.MeshStandardMaterial({
                    color: config.color,
                    emissive: config.color,
                    emissiveIntensity: 0.5
                });
                
                const particle = new THREE.Mesh(geometry, material);
                
                // Position based on pattern
                switch(config.pattern) {
                    case 'pulse':
                        const angle = (i / count) * Math.PI * 2;
                        const radius = 3;
                        particle.position.set(
                            Math.cos(angle) * radius,
                            Math.sin(angle) * radius,
                            (Math.random() - 0.5) * 2
                        );
                        break;
                    case 'spiral':
                        const spiralAngle = (i / count) * Math.PI * 4;
                        const spiralRadius = (i / count) * 5;
                        particle.position.set(
                            Math.cos(spiralAngle) * spiralRadius,
                            Math.sin(spiralAngle) * spiralRadius,
                            (i / count - 0.5) * 3
                        );
                        break;
                    case 'concentric':
                        const ring = Math.floor(i / 10);
                        const ringAngle = (i % 10) / 10 * Math.PI * 2;
                        particle.position.set(
                            Math.cos(ringAngle) * (ring + 1),
                            Math.sin(ringAngle) * (ring + 1),
                            (Math.random() - 0.5) * 2
                        );
                        break;
                    case 'wave':
                        particle.position.set(
                            (i / count - 0.5) * 10,
                            Math.sin(i * 0.5) * 2,
                            (Math.random() - 0.5) * 3
                        );
                        break;
                }
                
                particle.userData.speed = config.speed;
                particle.userData.originalY = particle.position.y;
                
                this.scene.add(particle);
                this.particles.push(particle);
            }
        }
        
        clearParticles() {
            this.particles.forEach(particle => {
                this.scene.remove(particle);
            });
            this.particles = [];
        }
        
        animate() {
            requestAnimationFrame(() => this.animate());
            
            if (this.isActive) {
                this.particles.forEach((particle, index) => {
                    // Animate based on mode
                    if (this.mode === 'breathing') {
                        const time = Date.now() * 0.001;
                        particle.scale.setScalar(1 + Math.sin(time + index * 0.1) * 0.3);
                    } else if (this.mode === 'meditation') {
                        particle.rotation.y += 0.01;
                        particle.rotation.x += 0.005;
                    } else if (this.mode === 'focus') {
                        particle.position.y = particle.userData.originalY + Math.sin(Date.now() * 0.001 + index) * 0.5;
                    } else if (this.mode === 'relax') {
                        particle.position.y += Math.sin(Date.now() * 0.001 + index) * 0.01;
                    }
                });
            }
            
            this.renderer.render(this.scene, this.camera);
        }
        
        onResize() {
            const width = this.container.clientWidth;
            const height = this.container.clientHeight;
            
            this.camera.aspect = width / height;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(width, height);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.therapy-container');
        containers.forEach(container => {
            const visualizer = container.closest('.color-therapy-visualizer');
            if (!visualizer) return;
            
            const options = {
                mode: visualizer.dataset.mode || 'breathing'
            };
            
            const instance = new ColorTherapyVisualizer(container, options);
            window.colorTherapy = instance;
            
            const resizeObserver = new ResizeObserver(() => {
                if (window.colorTherapy) {
                    window.colorTherapy.onResize();
                }
            });
            resizeObserver.observe(container);
        });
    });
    
    window.ColorTherapyVisualizer = ColorTherapyVisualizer;
})();

