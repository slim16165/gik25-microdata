/**
 * Interactive Design Game
 * Mini-gioco interattivo per design challenge
 */

(function() {
    'use strict';
    
    class InteractiveDesignGame {
        constructor(container, options = {}) {
            this.container = container;
            this.difficulty = options.difficulty || 'medium';
            this.scene = null;
            this.camera = null;
            this.renderer = null;
            this.score = 0;
            this.level = 1;
            this.isPlaying = false;
            this.collectibles = [];
            this.player = null;
            
            if (typeof THREE === 'undefined') {
                console.error('Three.js is required for Interactive Design Game');
                return;
            }
            
            this.init();
        }
        
        init() {
            this.setupScene();
            this.setupCamera();
            this.setupRenderer();
            this.setupLighting();
            this.setupPlayer();
            this.setupCollectibles();
            this.setupControls();
            this.setupEventListeners();
            this.updateUI();
            this.animate();
        }
        
        setupScene() {
            this.scene = new THREE.Scene();
            this.scene.background = new THREE.Color(0x87ceeb);
        }
        
        setupCamera() {
            const width = this.container.clientWidth;
            const height = this.container.clientHeight;
            
            this.camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
            this.camera.position.set(0, 5, 10);
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
        
        setupPlayer() {
            const geometry = new THREE.BoxGeometry(1, 1, 1);
            const material = new THREE.MeshStandardMaterial({ color: 0x00ff00 });
            this.player = new THREE.Mesh(geometry, material);
            this.player.position.set(0, 0.5, 0);
            this.player.castShadow = true;
            this.scene.add(this.player);
        }
        
        setupCollectibles() {
            const colors = [
                { h: 0, s: 100, l: 50 },   // Rosso
                { h: 120, s: 100, l: 50 }, // Verde
                { h: 240, s: 100, l: 50 }, // Blu
            ];
            
            for (let i = 0; i < 10; i++) {
                const color = colors[Math.floor(Math.random() * colors.length)];
                const geometry = new THREE.SphereGeometry(0.5, 16, 16);
                const material = new THREE.MeshStandardMaterial({
                    color: new THREE.Color().setHSL(color.h / 360, color.s / 100, color.l / 100),
                    emissive: new THREE.Color().setHSL(color.h / 360, color.s / 100, color.l / 100).multiplyScalar(0.5)
                });
                
                const collectible = new THREE.Mesh(geometry, material);
                collectible.position.set(
                    (Math.random() - 0.5) * 20,
                    1,
                    (Math.random() - 0.5) * 20
                );
                collectible.userData.color = color;
                collectible.castShadow = true;
                
                this.scene.add(collectible);
                this.collectibles.push(collectible);
            }
        }
        
        setupControls() {
            const keys = {};
            
            document.addEventListener('keydown', (e) => {
                keys[e.key.toLowerCase()] = true;
            });
            
            document.addEventListener('keyup', (e) => {
                keys[e.key.toLowerCase()] = false;
            });
            
            const updatePlayer = () => {
                if (!this.isPlaying) return;
                
                const speed = 0.1;
                if (keys['w'] || keys['arrowup']) this.player.position.z -= speed;
                if (keys['s'] || keys['arrowdown']) this.player.position.z += speed;
                if (keys['a'] || keys['arrowleft']) this.player.position.x -= speed;
                if (keys['d'] || keys['arrowright']) this.player.position.x += speed;
                
                // Check collisions
                this.checkCollectibles();
                
                requestAnimationFrame(updatePlayer);
            };
            
            updatePlayer();
        }
        
        setupEventListeners() {
            const startBtn = this.container.closest('.design-game')?.querySelector('#start-game');
            const pauseBtn = this.container.closest('.design-game')?.querySelector('#pause-game');
            
            if (startBtn) {
                startBtn.addEventListener('click', () => this.startGame());
            }
            
            if (pauseBtn) {
                pauseBtn.addEventListener('click', () => this.pauseGame());
            }
        }
        
        checkCollectibles() {
            this.collectibles.forEach((collectible, index) => {
                if (!collectible.parent) return;
                
                const distance = this.player.position.distanceTo(collectible.position);
                if (distance < 1) {
                    // Collected!
                    this.score += 10;
                    this.scene.remove(collectible);
                    this.collectibles.splice(index, 1);
                    this.updateUI();
                    
                    // Create particle effect
                    this.createParticleEffect(collectible.position, collectible.userData.color);
                    
                    // Check level complete
                    if (this.collectibles.length === 0) {
                        this.nextLevel();
                    }
                }
            });
        }
        
        createParticleEffect(position, color) {
            if (typeof gsap === 'undefined') return;
            
            for (let i = 0; i < 10; i++) {
                const geometry = new THREE.SphereGeometry(0.1, 8, 8);
                const material = new THREE.MeshBasicMaterial({
                    color: new THREE.Color().setHSL(color.h / 360, color.s / 100, color.l / 100)
                });
                const particle = new THREE.Mesh(geometry, material);
                particle.position.copy(position);
                this.scene.add(particle);
                
                const angle = (i / 10) * Math.PI * 2;
                const targetX = position.x + Math.cos(angle) * 2;
                const targetY = position.y + Math.sin(angle) * 2;
                const targetZ = position.z + (Math.random() - 0.5) * 2;
                
                gsap.to(particle.position, {
                    x: targetX,
                    y: targetY,
                    z: targetZ,
                    duration: 1,
                    ease: 'power2.out',
                    onComplete: () => {
                        this.scene.remove(particle);
                    }
                });
                
                gsap.to(particle.scale, {
                    x: 0,
                    y: 0,
                    z: 0,
                    duration: 1,
                    ease: 'power2.out'
                });
            }
        }
        
        nextLevel() {
            this.level++;
            this.setupCollectibles();
            this.updateUI();
        }
        
        startGame() {
            this.isPlaying = true;
            this.score = 0;
            this.level = 1;
            this.setupCollectibles();
            this.updateUI();
        }
        
        pauseGame() {
            this.isPlaying = !this.isPlaying;
        }
        
        updateUI() {
            const scoreEl = this.container.closest('.design-game')?.querySelector('#score');
            const levelEl = this.container.closest('.design-game')?.querySelector('#level');
            
            if (scoreEl) scoreEl.textContent = this.score;
            if (levelEl) levelEl.textContent = this.level;
        }
        
        animate() {
            requestAnimationFrame(() => this.animate());
            
            if (this.isPlaying) {
                // Rotate collectibles
                this.collectibles.forEach(collectible => {
                    collectible.rotation.y += 0.02;
                    collectible.position.y = 1 + Math.sin(Date.now() * 0.002 + collectible.position.x) * 0.3;
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
        const containers = document.querySelectorAll('.game-container');
        containers.forEach(container => {
            const game = container.closest('.design-game');
            if (!game) return;
            
            const options = {
                difficulty: game.dataset.difficulty || 'medium'
            };
            
            const instance = new InteractiveDesignGame(container, options);
            window.designGame = instance;
            
            const resizeObserver = new ResizeObserver(() => {
                if (window.designGame) {
                    window.designGame.onResize();
                }
            });
            resizeObserver.observe(container);
        });
    });
    
    window.InteractiveDesignGame = InteractiveDesignGame;
})();

