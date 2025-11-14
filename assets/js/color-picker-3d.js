/**
 * Color Picker 3D Interattivo
 * Navigazione 3D nello spazio colori con particelle
 */

(function() {
    'use strict';
    
    class ColorPicker3D {
        constructor(container, options = {}) {
            this.container = container;
            this.audioEnabled = options.audio !== 'false';
            this.particlesEnabled = options.particles !== 'false';
            this.scene = null;
            this.camera = null;
            this.renderer = null;
            this.colorSpheres = [];
            this.selectedColor = null;
            this.audioContext = null;
            
            if (typeof THREE === 'undefined') {
                console.error('Three.js is required for Color Picker 3D');
                return;
            }
            
            this.init();
        }
        
        init() {
            this.setupScene();
            this.setupCamera();
            this.setupRenderer();
            this.setupLighting();
            this.setupAudio();
            this.createColorSpheres();
            this.setupControls();
            this.setupEventListeners();
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
            this.camera.position.set(0, 0, 20);
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
            
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(10, 10, 5);
            this.scene.add(directionalLight);
            
            const pointLight = new THREE.PointLight(0xffffff, 0.6);
            pointLight.position.set(-10, -10, 10);
            this.scene.add(pointLight);
        }
        
        setupAudio() {
            if (this.audioEnabled && (window.AudioContext || window.webkitAudioContext)) {
                try {
                    this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                } catch (e) {
                    console.warn('Web Audio API not supported');
                }
            }
        }
        
        createColorSpheres() {
            // Crea sfere colori in uno spazio 3D
            const colors = [
                { h: 0, s: 100, l: 50 },   // Rosso
                { h: 30, s: 100, l: 50 },  // Arancione
                { h: 60, s: 100, l: 50 },  // Giallo
                { h: 120, s: 100, l: 50 }, // Verde
                { h: 180, s: 100, l: 50 }, // Ciano
                { h: 240, s: 100, l: 50 }, // Blu
                { h: 270, s: 100, l: 50 }, // Viola
                { h: 300, s: 100, l: 50 }, // Magenta
            ];
            
            const radius = 8;
            colors.forEach((color, index) => {
                const angle = (index / colors.length) * Math.PI * 2;
                const x = Math.cos(angle) * radius;
                const y = Math.sin(angle) * radius;
                const z = (Math.random() - 0.5) * 5;
                
                const geometry = new THREE.SphereGeometry(1, 32, 32);
                const material = new THREE.MeshStandardMaterial({
                    color: new THREE.Color().setHSL(color.h / 360, color.s / 100, color.l / 100),
                    emissive: new THREE.Color().setHSL(color.h / 360, color.s / 100, color.l / 100).multiplyScalar(0.2),
                    roughness: 0.3,
                    metalness: 0.1
                });
                
                const sphere = new THREE.Mesh(geometry, material);
                sphere.position.set(x, y, z);
                sphere.userData.color = color;
                sphere.castShadow = true;
                
                this.scene.add(sphere);
                this.colorSpheres.push(sphere);
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
                
                // Rotate camera around scene
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
            
            // Zoom
            this.renderer.domElement.addEventListener('wheel', (e) => {
                e.preventDefault();
                const distance = this.camera.position.length();
                const newDistance = distance + e.deltaY * 0.1;
                if (newDistance > 10 && newDistance < 50) {
                    this.camera.position.normalize().multiplyScalar(newDistance);
                }
            });
            
            // Click to select
            this.renderer.domElement.addEventListener('click', (e) => {
                const mouse = new THREE.Vector2();
                mouse.x = (e.clientX / this.renderer.domElement.clientWidth) * 2 - 1;
                mouse.y = -(e.clientY / this.renderer.domElement.clientHeight) * 2 + 1;
                
                const raycaster = new THREE.Raycaster();
                raycaster.setFromCamera(mouse, this.camera);
                
                const intersects = raycaster.intersectObjects(this.colorSpheres);
                if (intersects.length > 0) {
                    this.selectColor(intersects[0].object);
                }
            });
        }
        
        setupEventListeners() {
            // Touch gestures
            if (typeof Hammer !== 'undefined') {
                const hammer = new Hammer(this.renderer.domElement);
                hammer.get('pinch').set({ enable: true });
                hammer.get('rotate').set({ enable: true });
                
                hammer.on('pinch', (e) => {
                    const distance = this.camera.position.length();
                    const newDistance = distance / e.scale;
                    if (newDistance > 10 && newDistance < 50) {
                        this.camera.position.normalize().multiplyScalar(newDistance);
                    }
                });
            }
        }
        
        selectColor(sphere) {
            // Reset previous selection
            this.colorSpheres.forEach(s => {
                s.scale.set(1, 1, 1);
                s.material.emissive.multiplyScalar(0.5);
            });
            
            // Highlight selected
            sphere.scale.set(1.5, 1.5, 1.5);
            sphere.material.emissive.multiplyScalar(2);
            
            this.selectedColor = sphere.userData.color;
            this.updateColorDisplay();
            
            if (this.audioContext) {
                this.playColorSound(this.selectedColor);
            }
            
            // Animate with GSAP
            if (typeof gsap !== 'undefined') {
                gsap.to(sphere.scale, {
                    x: 1.5,
                    y: 1.5,
                    z: 1.5,
                    duration: 0.5,
                    ease: 'back.out(1.7)'
                });
            }
        }
        
        updateColorDisplay() {
            if (!this.selectedColor) return;
            
            const display = this.container.closest('.color-picker-3d')?.querySelector('#selected-color');
            if (!display) return;
            
            const hex = this.hslToHex(this.selectedColor.h, this.selectedColor.s, this.selectedColor.l);
            const rgb = this.hslToRgb(this.selectedColor.h, this.selectedColor.s, this.selectedColor.l);
            
            const hexEl = display.querySelector('.color-hex');
            const rgbEl = display.querySelector('.color-rgb');
            
            if (hexEl) hexEl.textContent = hex;
            if (rgbEl) rgbEl.textContent = `RGB(${rgb.r}, ${rgb.g}, ${rgb.b})`;
            
            display.style.background = `hsl(${this.selectedColor.h}, ${this.selectedColor.s}%, ${this.selectedColor.l}%)`;
        }
        
        playColorSound(color) {
            if (!this.audioContext) return;
            
            const frequency = 200 + (color.h / 360) * 1800;
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);
            
            oscillator.frequency.value = frequency;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.5);
            
            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.5);
        }
        
        animate() {
            requestAnimationFrame(() => this.animate());
            
            // Rotate spheres slowly
            this.colorSpheres.forEach((sphere, index) => {
                sphere.rotation.y += 0.01;
                sphere.rotation.x += 0.005;
                
                // Float animation
                sphere.position.y += Math.sin(Date.now() * 0.001 + index) * 0.01;
            });
            
            this.renderer.render(this.scene, this.camera);
        }
        
        hslToHex(h, s, l) {
            h /= 360;
            s /= 100;
            l /= 100;
            
            let r, g, b;
            if (s === 0) {
                r = g = b = l;
            } else {
                const hue2rgb = (p, q, t) => {
                    if (t < 0) t += 1;
                    if (t > 1) t -= 1;
                    if (t < 1/6) return p + (q - p) * 6 * t;
                    if (t < 1/2) return q;
                    if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                    return p;
                };
                
                const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                const p = 2 * l - q;
                r = hue2rgb(p, q, h + 1/3);
                g = hue2rgb(p, q, h);
                b = hue2rgb(p, q, h - 1/3);
            }
            
            const toHex = (c) => {
                const hex = Math.round(c * 255).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            };
            
            return `#${toHex(r)}${toHex(g)}${toHex(b)}`.toUpperCase();
        }
        
        hslToRgb(h, s, l) {
            h /= 360;
            s /= 100;
            l /= 100;
            
            let r, g, b;
            if (s === 0) {
                r = g = b = l;
            } else {
                const hue2rgb = (p, q, t) => {
                    if (t < 0) t += 1;
                    if (t > 1) t -= 1;
                    if (t < 1/6) return p + (q - p) * 6 * t;
                    if (t < 1/2) return q;
                    if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                    return p;
                };
                
                const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                const p = 2 * l - q;
                r = hue2rgb(p, q, h + 1/3);
                g = hue2rgb(p, q, h);
                b = hue2rgb(p, q, h - 1/3);
            }
            
            return {
                r: Math.round(r * 255),
                g: Math.round(g * 255),
                b: Math.round(b * 255)
            };
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
        const containers = document.querySelectorAll('.picker-container');
        containers.forEach(container => {
            const picker = container.closest('.color-picker-3d');
            if (!picker) return;
            
            const options = {
                audio: picker.dataset.audio || 'true',
                particles: picker.dataset.particles || 'true'
            };
            
            const instance = new ColorPicker3D(container, options);
            window.colorPicker3D = instance;
            
            const resizeObserver = new ResizeObserver(() => {
                if (window.colorPicker3D) {
                    window.colorPicker3D.onResize();
                }
            });
            resizeObserver.observe(container);
        });
    });
    
    window.ColorPicker3D = ColorPicker3D;
})();

