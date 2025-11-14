/**
 * Lighting Simulator Real-Time
 * Simulatore illuminazione con shader GLSL real-time
 */

(function() {
    'use strict';
    
    class LightingSimulator {
        constructor(container, options = {}) {
            this.container = container;
            this.room = options.room || 'soggiorno';
            this.time = parseFloat(options.time) || 12;
            this.scene = null;
            this.camera = null;
            this.renderer = null;
            this.lights = [];
            
            if (typeof THREE === 'undefined') {
                console.error('Three.js is required for Lighting Simulator');
                return;
            }
            
            this.init();
        }
        
        init() {
            this.setupScene();
            this.setupCamera();
            this.setupRenderer();
            this.setupRoom();
            this.setupLighting();
            this.setupControls();
            this.setupEventListeners();
            this.animate();
        }
        
        setupScene() {
            this.scene = new THREE.Scene();
            this.updateSceneColor();
        }
        
        updateSceneColor() {
            // Simula colore cielo basato su ora del giorno
            const hour = this.time;
            let skyColor;
            
            if (hour >= 6 && hour < 8) {
                // Alba
                skyColor = new THREE.Color().setHSL(0.1, 0.5, 0.4);
            } else if (hour >= 8 && hour < 18) {
                // Giorno
                const progress = (hour - 8) / 10;
                skyColor = new THREE.Color().setHSL(0.55 - progress * 0.1, 0.3, 0.5 + progress * 0.3);
            } else if (hour >= 18 && hour < 20) {
                // Tramonto
                skyColor = new THREE.Color().setHSL(0.05, 0.7, 0.3);
            } else {
                // Notte
                skyColor = new THREE.Color().setHSL(0.6, 0.8, 0.1);
            }
            
            this.scene.background = skyColor;
        }
        
        setupCamera() {
            const width = this.container.clientWidth;
            const height = this.container.clientHeight;
            
            this.camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 1000);
            this.camera.position.set(10, 8, 10);
            this.camera.lookAt(0, 0, 0);
        }
        
        setupRenderer() {
            this.renderer = new THREE.WebGLRenderer({ antialias: true });
            this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
            this.renderer.shadowMap.enabled = true;
            this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            this.container.appendChild(this.renderer.domElement);
        }
        
        setupRoom() {
            // Floor
            const floorGeometry = new THREE.PlaneGeometry(20, 20);
            const floorMaterial = new THREE.MeshStandardMaterial({ 
                color: 0xcccccc,
                roughness: 0.8
            });
            const floor = new THREE.Mesh(floorGeometry, floorMaterial);
            floor.rotation.x = -Math.PI / 2;
            floor.receiveShadow = true;
            this.scene.add(floor);
            
            // Walls
            const wallMaterial = new THREE.MeshStandardMaterial({ 
                color: 0xffffff,
                roughness: 0.7
            });
            
            const backWall = new THREE.Mesh(
                new THREE.PlaneGeometry(20, 10),
                wallMaterial
            );
            backWall.position.set(0, 5, -10);
            backWall.receiveShadow = true;
            this.scene.add(backWall);
        }
        
        setupLighting() {
            // Sun light (directional, changes with time)
            const sunLight = new THREE.DirectionalLight(0xffffff, 1);
            sunLight.castShadow = true;
            sunLight.shadow.mapSize.width = 2048;
            sunLight.shadow.mapSize.height = 2048;
            this.updateSunPosition(sunLight);
            this.scene.add(sunLight);
            this.lights.push({ type: 'sun', light: sunLight });
            
            // Ambient light (changes with time)
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.3);
            this.scene.add(ambientLight);
            this.lights.push({ type: 'ambient', light: ambientLight });
        }
        
        updateSunPosition(sunLight) {
            const hour = this.time;
            const sunAngle = ((hour - 6) / 12) * Math.PI; // 0 at 6am, PI at 6pm
            
            sunLight.position.set(
                Math.sin(sunAngle) * 20,
                Math.max(0, Math.cos(sunAngle)) * 20,
                Math.cos(sunAngle) * 20
            );
            
            // Update intensity based on time
            if (hour >= 6 && hour <= 18) {
                const intensity = Math.sin((hour - 6) / 12 * Math.PI);
                sunLight.intensity = intensity;
            } else {
                sunLight.intensity = 0;
            }
            
            // Update color (warmer at sunrise/sunset)
            if (hour >= 6 && hour < 8) {
                sunLight.color.setHSL(0.1, 0.8, 0.6);
            } else if (hour >= 18 && hour < 20) {
                sunLight.color.setHSL(0.05, 0.9, 0.5);
            } else {
                sunLight.color.setHSL(0.1, 0.1, 1);
            }
        }
        
        setupControls() {
            const timeSlider = this.container.closest('.lighting-simulator')?.querySelector('#time-slider');
            const timeDisplay = this.container.closest('.lighting-simulator')?.querySelector('#time-display');
            
            if (timeSlider) {
                timeSlider.value = this.time;
                timeSlider.addEventListener('input', (e) => {
                    this.time = parseFloat(e.target.value);
                    this.updateSunPosition(this.lights.find(l => l.type === 'sun').light);
                    this.updateSceneColor();
                    
                    if (timeDisplay) {
                        const hours = Math.floor(this.time);
                        const minutes = Math.round((this.time - hours) * 60);
                        timeDisplay.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                    }
                });
            }
        }
        
        setupEventListeners() {
            const addLightBtn = this.container.closest('.lighting-simulator')?.querySelector('[data-action="add-light"]');
            const changeColorBtn = this.container.closest('.lighting-simulator')?.querySelector('[data-action="change-color"]');
            
            if (addLightBtn) {
                addLightBtn.addEventListener('click', () => this.addLight());
            }
            
            if (changeColorBtn) {
                changeColorBtn.addEventListener('click', () => this.changeLightColor());
            }
        }
        
        addLight() {
            const pointLight = new THREE.PointLight(0xffffff, 1, 100);
            pointLight.position.set(
                (Math.random() - 0.5) * 10,
                3,
                (Math.random() - 0.5) * 10
            );
            pointLight.castShadow = true;
            this.scene.add(pointLight);
            this.lights.push({ type: 'point', light: pointLight });
        }
        
        changeLightColor() {
            this.lights.forEach(({ light, type }) => {
                if (type === 'point') {
                    light.color.setHSL(Math.random(), 0.7, 0.6);
                }
            });
        }
        
        animate() {
            requestAnimationFrame(() => this.animate());
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
        const containers = document.querySelectorAll('.lighting-container');
        containers.forEach(container => {
            const simulator = container.closest('.lighting-simulator');
            if (!simulator) return;
            
            const options = {
                room: simulator.dataset.room || 'soggiorno',
                time: parseFloat(simulator.dataset.time) || 12
            };
            
            const instance = new LightingSimulator(container, options);
            window.lightingSimulator = instance;
            
            const resizeObserver = new ResizeObserver(() => {
                if (window.lightingSimulator) {
                    window.lightingSimulator.onResize();
                }
            });
            resizeObserver.observe(container);
        });
    });
    
    window.LightingSimulator = LightingSimulator;
})();

