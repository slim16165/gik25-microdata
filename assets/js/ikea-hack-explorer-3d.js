/**
 * IKEA Hack Explorer 3D
 * Navigatore 3D per hack IKEA con modelli interattivi
 */

(function() {
    'use strict';
    
    class IKEAHackExplorer3D {
        constructor(container, options = {}) {
            this.container = container;
            this.line = options.line || '';
            this.limit = parseInt(options.limit) || 12;
            this.scene = null;
            this.camera = null;
            this.renderer = null;
            this.hacks = [];
            this.currentHackIndex = 0;
            
            if (typeof THREE === 'undefined') {
                console.error('Three.js is required for IKEA Hack Explorer');
                return;
            }
            
            this.init();
        }
        
        init() {
            this.setupScene();
            this.setupCamera();
            this.setupRenderer();
            this.setupLighting();
            this.loadHacks();
            this.setupControls();
            this.setupEventListeners();
            this.animate();
        }
        
        setupScene() {
            this.scene = new THREE.Scene();
            this.scene.background = new THREE.Color(0x1a1a2e);
        }
        
        setupCamera() {
            const width = this.container.clientWidth;
            const height = this.container.clientHeight;
            
            this.camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 1000);
            this.camera.position.set(0, 5, 15);
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
            
            const pointLight = new THREE.PointLight(0xffffff, 0.5);
            pointLight.position.set(-10, 5, -10);
            this.scene.add(pointLight);
        }
        
        loadHacks() {
            // Simula caricamento hack IKEA
            const hackNames = ['BILLY Hack', 'KALLAX Hack', 'BESTA Hack', 'PAX Hack', 'METOD Hack', 'ENHET Hack'];
            
            hackNames.forEach((name, index) => {
                const geometry = new THREE.BoxGeometry(2, 2, 2);
                const material = new THREE.MeshStandardMaterial({ 
                    color: new THREE.Color().setHSL(index / hackNames.length, 0.7, 0.5),
                    roughness: 0.7
                });
                const hack = new THREE.Mesh(geometry, material);
                hack.position.set(
                    (index % 3 - 1) * 5,
                    0,
                    Math.floor(index / 3) * 5
                );
                hack.userData.name = name;
                hack.castShadow = true;
                hack.receiveShadow = true;
                
                this.scene.add(hack);
                this.hacks.push(hack);
            });
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
            
            // Click to select hack
            this.renderer.domElement.addEventListener('click', (e) => {
                const mouse = new THREE.Vector2();
                mouse.x = (e.clientX / this.renderer.domElement.clientWidth) * 2 - 1;
                mouse.y = -(e.clientY / this.renderer.domElement.clientHeight) * 2 + 1;
                
                const raycaster = new THREE.Raycaster();
                raycaster.setFromCamera(mouse, this.camera);
                
                const intersects = raycaster.intersectObjects(this.hacks);
                if (intersects.length > 0) {
                    this.selectHack(intersects[0].object);
                }
            });
        }
        
        setupEventListeners() {
            const filters = this.container.closest('.ikea-hack-explorer')?.querySelectorAll('.filter-btn');
            if (filters) {
                filters.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const line = e.target.dataset.line;
                        this.filterByLine(line);
                        filters.forEach(b => b.classList.remove('active'));
                        e.target.classList.add('active');
                    });
                });
            }
        }
        
        selectHack(hack) {
            // Highlight selected hack
            this.hacks.forEach(h => {
                h.material.emissive.setHex(0x000000);
            });
            
            hack.material.emissive.setHex(0x444444);
            
            // Animate camera to hack
            if (typeof gsap !== 'undefined') {
                const targetPosition = hack.position.clone().add(new THREE.Vector3(0, 3, 5));
                gsap.to(this.camera.position, {
                    x: targetPosition.x,
                    y: targetPosition.y,
                    z: targetPosition.z,
                    duration: 1,
                    ease: 'power2.inOut',
                    onUpdate: () => {
                        this.camera.lookAt(hack.position);
                    }
                });
            }
        }
        
        filterByLine(line) {
            // Filter hacks by IKEA line
            this.hacks.forEach((hack, index) => {
                if (line && !hack.userData.name.toLowerCase().includes(line.toLowerCase())) {
                    gsap.to(hack.scale, { x: 0, y: 0, z: 0, duration: 0.5 });
                } else {
                    gsap.to(hack.scale, { x: 1, y: 1, z: 1, duration: 0.5 });
                }
            });
        }
        
        animate() {
            requestAnimationFrame(() => this.animate());
            
            // Rotate hacks slowly
            this.hacks.forEach(hack => {
                hack.rotation.y += 0.005;
            });
            
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
        const containers = document.querySelectorAll('.explorer-container');
        containers.forEach(container => {
            const explorer = container.closest('.ikea-hack-explorer');
            if (!explorer) return;
            
            const options = {
                line: explorer.dataset.line || '',
                limit: parseInt(explorer.dataset.limit) || 12
            };
            
            const instance = new IKEAHackExplorer3D(container, options);
            window.ikeaHackExplorer = instance;
            
            const resizeObserver = new ResizeObserver(() => {
                if (window.ikeaHackExplorer) {
                    window.ikeaHackExplorer.onResize();
                }
            });
            resizeObserver.observe(container);
        });
    });
    
    window.IKEAHackExplorer3D = IKEAHackExplorer3D;
})();

