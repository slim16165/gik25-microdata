/**
 * Isometric IKEA Configurator
 * Configuratore IKEA avanzato con vista isometrica
 */

(function() {
    'use strict';
    
    class IsometricIKEAConfigurator {
        constructor(container, options = {}) {
            this.container = container;
            this.line = options.line || 'billy';
            this.room = options.room || 'soggiorno';
            this.scene = null;
            this.camera = null;
            this.renderer = null;
            this.furniture = null;
            this.selectedColor = null;
            
            if (typeof THREE === 'undefined') {
                console.error('Three.js is required');
                return;
            }
            
            this.init();
        }
        
        init() {
            this.setupScene();
            this.setupCamera();
            this.setupRenderer();
            this.setupLighting();
            this.loadFurniture();
            this.setupControls();
            this.setupEventListeners();
            this.animate();
        }
        
        setupScene() {
            this.scene = new THREE.Scene();
            this.scene.background = new THREE.Color(0xf5f5f5);
        }
        
        setupCamera() {
            const width = this.container.clientWidth;
            const height = this.container.clientHeight;
            
            this.camera = new THREE.OrthographicCamera(
                -10, 10, 10, -10, 0.1, 1000
            );
            this.camera.position.set(10, 10, 10);
            this.camera.lookAt(0, 0, 0);
        }
        
        setupRenderer() {
            this.renderer = new THREE.WebGLRenderer({ antialias: true });
            this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
            this.renderer.shadowMap.enabled = true;
            this.container.appendChild(this.renderer.domElement);
        }
        
        setupLighting() {
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
            this.scene.add(ambientLight);
            
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(10, 10, 5);
            directionalLight.castShadow = true;
            this.scene.add(directionalLight);
        }
        
        loadFurniture() {
            // Create furniture based on line
            const dimensions = this.getDimensionsForLine(this.line);
            
            const geometry = new THREE.BoxGeometry(dimensions.w, dimensions.h, dimensions.d);
            const material = new THREE.MeshStandardMaterial({ 
                color: 0x8b4513,
                roughness: 0.7
            });
            
            this.furniture = new THREE.Mesh(geometry, material);
            this.furniture.position.set(0, dimensions.h / 2, 0);
            this.furniture.castShadow = true;
            this.furniture.receiveShadow = true;
            
            this.scene.add(this.furniture);
        }
        
        getDimensionsForLine(line) {
            const dimensions = {
                billy: { w: 2, h: 7, d: 1 },
                kallax: { w: 4, h: 4, d: 1 },
                besta: { w: 3, h: 2, d: 1 },
                pax: { w: 2, h: 8, d: 2 },
                metod: { w: 6, h: 2, d: 2 },
                enhet: { w: 4, h: 2, d: 2 }
            };
            
            return dimensions[line] || dimensions.billy;
        }
        
        setupControls() {
            let isDragging = false;
            let previousMousePosition = { x: 0, y: 0 };
            
            this.renderer.domElement.addEventListener('mousedown', (e) => {
                isDragging = true;
                previousMousePosition = { x: e.clientX, y: e.clientY };
            });
            
            this.renderer.domElement.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                
                const deltaX = e.clientX - previousMousePosition.x;
                const deltaY = e.clientY - previousMousePosition.y;
                
                // Rotate camera around furniture
                const angle = Math.atan2(this.camera.position.z, this.camera.position.x);
                const newAngle = angle + deltaX * 0.01;
                const distance = Math.sqrt(
                    this.camera.position.x ** 2 + this.camera.position.z ** 2
                );
                
                this.camera.position.x = Math.cos(newAngle) * distance;
                this.camera.position.z = Math.sin(newAngle) * distance;
                this.camera.lookAt(0, 0, 0);
                
                previousMousePosition = { x: e.clientX, y: e.clientY };
            });
            
            this.renderer.domElement.addEventListener('mouseup', () => {
                isDragging = false;
            });
        }
        
        setupEventListeners() {
            const lineSelector = this.container.closest('.ikea-configurator')?.querySelector('#line-selector');
            if (lineSelector) {
                lineSelector.value = this.line;
                lineSelector.addEventListener('change', (e) => {
                    this.line = e.target.value;
                    this.updateFurniture();
                });
            }
        }
        
        updateFurniture() {
            if (!this.furniture) return;
            
            const dimensions = this.getDimensionsForLine(this.line);
            
            if (typeof gsap !== 'undefined') {
                gsap.to(this.furniture.scale, {
                    x: dimensions.w / 2,
                    y: dimensions.h / 2,
                    z: dimensions.d / 2,
                    duration: 0.8,
                    ease: 'power2.inOut'
                });
            } else {
                this.furniture.scale.set(
                    dimensions.w / 2,
                    dimensions.h / 2,
                    dimensions.d / 2
                );
            }
        }
        
        changeColor(color) {
            if (!this.furniture) return;
            
            if (typeof gsap !== 'undefined') {
                gsap.to(this.furniture.material.color, {
                    r: color.r / 255,
                    g: color.g / 255,
                    b: color.b / 255,
                    duration: 0.5
                });
            } else {
                this.furniture.material.color.setRGB(
                    color.r / 255,
                    color.g / 255,
                    color.b / 255
                );
            }
        }
        
        animate() {
            requestAnimationFrame(() => this.animate());
            this.renderer.render(this.scene, this.camera);
        }
        
        onResize() {
            const width = this.container.clientWidth;
            const height = this.container.clientHeight;
            
            this.camera.left = -10;
            this.camera.right = 10;
            this.camera.top = 10;
            this.camera.bottom = -10;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(width, height);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const containers = document.querySelectorAll('.configurator-container');
        containers.forEach(container => {
            const configurator = container.closest('.ikea-configurator');
            if (!configurator) return;
            
            const options = {
                line: configurator.dataset.line || 'billy',
                room: configurator.dataset.room || 'soggiorno'
            };
            
            const instance = new IsometricIKEAConfigurator(container, options);
            window.ikeaConfigurator = instance;
            
            const resizeObserver = new ResizeObserver(() => {
                if (window.ikeaConfigurator) {
                    window.ikeaConfigurator.onResize();
                }
            });
            resizeObserver.observe(container);
        });
    });
    
    window.IsometricIKEAConfigurator = IsometricIKEAConfigurator;
})();

