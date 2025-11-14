/**
 * Room Simulator Isometrico
 * Simulatore stanze in vista isometrica con Three.js
 */

(function() {
    'use strict';
    
    class RoomSimulatorIsometric {
        constructor(container, options = {}) {
            this.container = container;
            this.room = options.room || 'cucina';
            this.scene = null;
            this.camera = null;
            this.renderer = null;
            this.controls = null;
            this.furniture = [];
            this.selectedFurniture = null;
            
            if (typeof THREE === 'undefined') {
                console.error('Three.js is required for Room Simulator');
                return;
            }
            
            this.init();
        }
        
        init() {
            this.setupScene();
            this.setupCamera();
            this.setupRenderer();
            this.setupLighting();
            this.setupRoom();
            this.setupControls();
            this.setupEventListeners();
            this.animate();
        }
        
        setupScene() {
            this.scene = new THREE.Scene();
            this.scene.background = new THREE.Color(0xf0f0f0);
        }
        
        setupCamera() {
            const width = this.container.clientWidth;
            const height = this.container.clientHeight;
            
            this.camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
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
            // Ambient light
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
            this.scene.add(ambientLight);
            
            // Directional light (sun)
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(10, 10, 5);
            directionalLight.castShadow = true;
            this.scene.add(directionalLight);
            
            // Point light (lamp)
            const pointLight = new THREE.PointLight(0xffffff, 0.5);
            pointLight.position.set(0, 5, 0);
            this.scene.add(pointLight);
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
            
            // Back wall
            const backWall = new THREE.Mesh(
                new THREE.PlaneGeometry(20, 10),
                wallMaterial
            );
            backWall.position.set(0, 5, -10);
            backWall.receiveShadow = true;
            this.scene.add(backWall);
            
            // Side walls
            const leftWall = new THREE.Mesh(
                new THREE.PlaneGeometry(20, 10),
                wallMaterial
            );
            leftWall.rotation.y = Math.PI / 2;
            leftWall.position.set(-10, 5, 0);
            leftWall.receiveShadow = true;
            this.scene.add(leftWall);
            
            const rightWall = new THREE.Mesh(
                new THREE.PlaneGeometry(20, 10),
                wallMaterial
            );
            rightWall.rotation.y = -Math.PI / 2;
            rightWall.position.set(10, 5, 0);
            rightWall.receiveShadow = true;
            this.scene.add(rightWall);
        }
        
        setupControls() {
            // Simple orbit controls
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
                
                // Rotate camera around room
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
            
            // Zoom with wheel
            this.renderer.domElement.addEventListener('wheel', (e) => {
                e.preventDefault();
                const distance = this.camera.position.length();
                const newDistance = distance + e.deltaY * 0.1;
                if (newDistance > 5 && newDistance < 50) {
                    this.camera.position.normalize().multiplyScalar(newDistance);
                }
            });
        }
        
        setupEventListeners() {
            const toolbar = this.container.closest('.room-simulator')?.querySelector('.room-toolbar');
            if (!toolbar) return;
            
            toolbar.addEventListener('click', (e) => {
                const action = e.target.closest('.toolbar-btn')?.dataset.action;
                if (!action) return;
                
                switch(action) {
                    case 'furniture':
                        this.addFurniture();
                        break;
                    case 'colors':
                        this.changeWallColor();
                        break;
                    case 'lighting':
                        this.adjustLighting();
                        break;
                    case 'reset':
                        this.resetRoom();
                        break;
                }
            });
        }
        
        addFurniture() {
            // Add a simple furniture piece (cube for demo)
            const geometry = new THREE.BoxGeometry(2, 2, 2);
            const material = new THREE.MeshStandardMaterial({ 
                color: 0x8b4513,
                roughness: 0.7
            });
            const furniture = new THREE.Mesh(geometry, material);
            furniture.position.set(
                (Math.random() - 0.5) * 10,
                1,
                (Math.random() - 0.5) * 10
            );
            furniture.castShadow = true;
            furniture.receiveShadow = true;
            
            this.scene.add(furniture);
            this.furniture.push(furniture);
        }
        
        changeWallColor() {
            const walls = this.scene.children.filter(child => 
                child instanceof THREE.Mesh && 
                child.material.color.getHex() === 0xffffff
            );
            
            const newColor = new THREE.Color().setHSL(Math.random(), 0.5, 0.7);
            walls.forEach(wall => {
                wall.material.color.copy(newColor);
            });
        }
        
        adjustLighting() {
            const lights = this.scene.children.filter(child => 
                child instanceof THREE.Light
            );
            
            lights.forEach(light => {
                if (light instanceof THREE.DirectionalLight || light instanceof THREE.PointLight) {
                    light.intensity = Math.random() * 0.5 + 0.5;
                }
            });
        }
        
        resetRoom() {
            // Remove all furniture
            this.furniture.forEach(f => this.scene.remove(f));
            this.furniture = [];
            
            // Reset camera
            this.camera.position.set(10, 10, 10);
            this.camera.lookAt(0, 0, 0);
            
            // Reset walls
            this.changeWallColor();
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
        const containers = document.querySelectorAll('.room-container');
        containers.forEach(container => {
            const simulator = container.closest('.room-simulator');
            if (!simulator) return;
            
            const options = {
                room: simulator.dataset.room || 'cucina'
            };
            
            new RoomSimulatorIsometric(container, options);
            
            // Handle resize
            const resizeObserver = new ResizeObserver(() => {
                if (window.roomSimulator) {
                    window.roomSimulator.onResize();
                }
            });
            resizeObserver.observe(container);
        });
    });
    
    window.RoomSimulatorIsometric = RoomSimulatorIsometric;
})();

