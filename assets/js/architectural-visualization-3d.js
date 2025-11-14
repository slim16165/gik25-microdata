/**
 * Architectural Visualization 3D
 * Visualizzatore 3D per architetture famose
 */

(function() {
    'use strict';
    
    class ArchitecturalVisualization3D {
        constructor(container, options = {}) {
            this.container = container;
            this.architect = options.architect || '';
            this.flythroughEnabled = options.flythrough !== 'false';
            this.scene = null;
            this.camera = null;
            this.renderer = null;
            this.model = null;
            this.isPlaying = false;
            this.cameraPath = [];
            
            if (typeof THREE === 'undefined') {
                console.error('Three.js is required for Architectural Visualization');
                return;
            }
            
            this.init();
        }
        
        init() {
            this.setupScene();
            this.setupCamera();
            this.setupRenderer();
            this.setupLighting();
            this.loadModel();
            this.setupControls();
            this.setupEventListeners();
            this.animate();
        }
        
        setupScene() {
            this.scene = new THREE.Scene();
            this.scene.background = new THREE.Color(0x87ceeb);
            this.scene.fog = new THREE.Fog(0x87ceeb, 50, 200);
        }
        
        setupCamera() {
            const width = this.container.clientWidth;
            const height = this.container.clientHeight;
            
            this.camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 1000);
            this.camera.position.set(0, 10, 30);
            this.camera.lookAt(0, 0, 0);
        }
        
        setupRenderer() {
            this.renderer = new THREE.WebGLRenderer({ antialias: true });
            this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
            this.renderer.shadowMap.enabled = true;
            this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            this.container.appendChild(this.renderer.domElement);
        }
        
        setupLighting() {
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
            this.scene.add(ambientLight);
            
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(20, 20, 10);
            directionalLight.castShadow = true;
            directionalLight.shadow.mapSize.width = 2048;
            directionalLight.shadow.mapSize.height = 2048;
            this.scene.add(directionalLight);
        }
        
        loadModel() {
            // Crea modello architetturale semplificato
            const group = new THREE.Group();
            
            // Base building
            const baseGeometry = new THREE.BoxGeometry(10, 2, 10);
            const baseMaterial = new THREE.MeshStandardMaterial({ 
                color: 0xcccccc,
                roughness: 0.7
            });
            const base = new THREE.Mesh(baseGeometry, baseMaterial);
            base.position.y = 1;
            base.castShadow = true;
            base.receiveShadow = true;
            group.add(base);
            
            // Main structure
            const mainGeometry = new THREE.BoxGeometry(8, 15, 8);
            const mainMaterial = new THREE.MeshStandardMaterial({ 
                color: 0xffffff,
                roughness: 0.6,
                metalness: 0.1
            });
            const main = new THREE.Mesh(mainGeometry, mainMaterial);
            main.position.y = 9.5;
            main.castShadow = true;
            main.receiveShadow = true;
            group.add(main);
            
            // Windows
            for (let i = 0; i < 3; i++) {
                for (let j = 0; j < 3; j++) {
                    const windowGeometry = new THREE.PlaneGeometry(1.5, 1.5);
                    const windowMaterial = new THREE.MeshStandardMaterial({ 
                        color: 0x4a90e2,
                        emissive: 0x1a3a5a,
                        emissiveIntensity: 0.3
                    });
                    const window = new THREE.Mesh(windowGeometry, windowMaterial);
                    window.position.set(
                        -2.5 + j * 2.5,
                        5 + i * 4,
                        4.01
                    );
                    group.add(window);
                }
            }
            
            this.scene.add(group);
            this.model = group;
            
            // Create camera path for flythrough
            this.createCameraPath();
        }
        
        createCameraPath() {
            const radius = 40;
            const height = 15;
            const points = 20;
            
            for (let i = 0; i <= points; i++) {
                const angle = (i / points) * Math.PI * 2;
                this.cameraPath.push({
                    x: Math.cos(angle) * radius,
                    y: height + Math.sin(angle * 2) * 5,
                    z: Math.sin(angle) * radius
                });
            }
        }
        
        setupControls() {
            let isDragging = false;
            let previousMousePosition = { x: 0, y: 0 };
            
            this.renderer.domElement.addEventListener('mousedown', (e) => {
                isDragging = true;
                previousMousePosition = { x: e.clientX, y: e.clientY };
            });
            
            this.renderer.domElement.addEventListener('mousemove', (e) => {
                if (!isDragging || this.isPlaying) return;
                
                const deltaX = e.clientX - previousMousePosition.x;
                const deltaY = e.clientY - previousMousePosition.y;
                
                const spherical = new THREE.Spherical();
                spherical.setFromVector3(this.camera.position);
                spherical.theta -= deltaX * 0.01;
                spherical.phi += deltaY * 0.01;
                spherical.phi = Math.max(0.1, Math.min(Math.PI - 0.1, spherical.phi));
                
                this.camera.position.setFromSpherical(spherical);
                this.camera.lookAt(0, 0, 0);
                
                previousMousePosition = { x: e.clientX, y: e.clientY };
            });
            
            this.renderer.domElement.addEventListener('mouseup', () => {
                isDragging = false;
            });
        }
        
        setupEventListeners() {
            const playBtn = this.container.closest('.architectural-viz')?.querySelector('[data-action="play"]');
            const pauseBtn = this.container.closest('.architectural-viz')?.querySelector('[data-action="pause"]');
            const resetBtn = this.container.closest('.architectural-viz')?.querySelector('[data-action="reset"]');
            
            if (playBtn) {
                playBtn.addEventListener('click', () => this.playFlythrough());
            }
            
            if (pauseBtn) {
                pauseBtn.addEventListener('click', () => this.pauseFlythrough());
            }
            
            if (resetBtn) {
                resetBtn.addEventListener('click', () => this.resetCamera());
            }
        }
        
        playFlythrough() {
            if (!this.flythroughEnabled) return;
            
            this.isPlaying = true;
            let currentIndex = 0;
            
            const animate = () => {
                if (!this.isPlaying) return;
                
                const point = this.cameraPath[currentIndex];
                const nextPoint = this.cameraPath[(currentIndex + 1) % this.cameraPath.length];
                
                if (typeof gsap !== 'undefined') {
                    gsap.to(this.camera.position, {
                        x: nextPoint.x,
                        y: nextPoint.y,
                        z: nextPoint.z,
                        duration: 2,
                        ease: 'power1.inOut',
                        onComplete: () => {
                            currentIndex = (currentIndex + 1) % this.cameraPath.length;
                            if (this.isPlaying) {
                                animate();
                            }
                        }
                    });
                    
                    this.camera.lookAt(0, 5, 0);
                } else {
                    // Fallback senza GSAP
                    this.camera.position.set(nextPoint.x, nextPoint.y, nextPoint.z);
                    this.camera.lookAt(0, 5, 0);
                    currentIndex = (currentIndex + 1) % this.cameraPath.length;
                    setTimeout(animate, 2000);
                }
            };
            
            animate();
        }
        
        pauseFlythrough() {
            this.isPlaying = false;
        }
        
        resetCamera() {
            this.isPlaying = false;
            this.camera.position.set(0, 10, 30);
            this.camera.lookAt(0, 0, 0);
        }
        
        animate() {
            requestAnimationFrame(() => this.animate());
            
            // Rotate model slowly
            if (this.model && !this.isPlaying) {
                this.model.rotation.y += 0.005;
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
        const containers = document.querySelectorAll('.viz-container');
        containers.forEach(container => {
            const viz = container.closest('.architectural-viz');
            if (!viz) return;
            
            const options = {
                architect: viz.dataset.architect || '',
                flythrough: viz.dataset.flythrough || 'true'
            };
            
            const instance = new ArchitecturalVisualization3D(container, options);
            window.architecturalViz = instance;
            
            const resizeObserver = new ResizeObserver(() => {
                if (window.architecturalViz) {
                    window.architecturalViz.onResize();
                }
            });
            resizeObserver.observe(container);
        });
    });
    
    window.ArchitecturalVisualization3D = ArchitecturalVisualization3D;
})();

