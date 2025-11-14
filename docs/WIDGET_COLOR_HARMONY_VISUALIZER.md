# Color Harmony Visualizer - Specifiche Tecniche

**Widget**: Color Harmony Visualizer  
**Livello**: Molto Avanzato  
**Priorità**: ⭐⭐⭐⭐ (Quick Win)  
**Tempo stimato**: 2-3 settimane

---

## 🎯 Concept

Visualizzatore interattivo di armonie colori con:
- Grafici interattivi D3.js
- Animazioni fluide GSAP
- Audio reattivo Web Audio
- Effetti particellari Canvas
- Touch gestures avanzati

---

## 🎨 Features Dettagliate

### 1. Visualizzazione Armonie

**Tipi di armonia**:
- **Complementari**: Colori opposti nel cerchio cromatico
- **Analoghi**: Colori adiacenti (3-5 colori)
- **Triadi**: 3 colori equidistanti
- **Split-Complementary**: Colore + 2 adiacenti al complementare
- **Tetradic**: 4 colori (2 coppie complementari)
- **Monocromatico**: Variazioni di luminosità/saturazione

**Visualizzazione**:
- Cerchio cromatico interattivo (HSL)
- Grafici D3.js per ogni tipo di armonia
- Animazioni fluide tra tipi
- Highlight colori selezionati

### 2. Audio Reattivo

**Mapping colore → frequenza**:
- **Hue (0-360)**: Frequenza base (200-2000 Hz)
- **Saturation (0-100)**: Modulazione ampiezza
- **Lightness (0-100)**: Modulazione frequenza

**Effetti**:
- Click su colore → suono
- Hover → suono leggero
- Transizione armonia → melodia
- Armonia completa → accordo

### 3. Effetti Particellari

**Sistema particelle**:
- Particelle per ogni colore
- Movimento fluido (simulazione fisica)
- Interazione tra particelle (attrazione/repulsione)
- Esplosione quando si seleziona colore
- Dissolvenza quando si cambia armonia

### 4. Interattività

**Mouse/Touch**:
- Click su colore → seleziona
- Drag su cerchio cromatico → cambia colore
- Hover → preview armonia
- Scroll → zoom cerchio cromatico

**Keyboard**:
- Arrow keys → naviga colori
- Space → genera armonia random
- Enter → applica armonia
- Esc → reset

### 5. Salvataggio/Esportazione

**Funzionalità**:
- Salva palette preferite (localStorage)
- Esporta come immagine (Canvas toDataURL)
- Esporta come CSS (variabili)
- Esporta come JSON
- Condividi link (URL params)

---

## 🛠️ Stack Tecnologico

### Core
- **Canvas API** - Rendering 2D
- **D3.js** - Grafici interattivi
- **GSAP** - Animazioni fluide
- **Web Audio API** - Audio reattivo

### Utilities
- **TinyColor** - Manipolazione colori
- **Hammer.js** - Gesture recognition (opzionale)

---

## 📁 Struttura File

```
assets/
├── js/
│   └── color-harmony-visualizer.js
├── css/
│   └── color-harmony-visualizer.css
└── images/
    └── (nessuna, tutto generato)

include/class/Widgets/
└── ColorHarmonyVisualizer.php
```

---

## 💻 Implementazione JavaScript

### Classe Principale

```javascript
class ColorHarmonyVisualizer {
    constructor(canvas, options = {}) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.options = {
            width: options.width || 800,
            height: options.height || 600,
            particleCount: options.particleCount || 100,
            audioEnabled: options.audioEnabled !== false,
            ...options
        };
        
        this.harmonyType = 'complementary';
        this.selectedColor = { h: 0, s: 50, l: 50 };
        this.particles = [];
        this.audioContext = null;
        
        this.init();
    }
    
    init() {
        this.setupCanvas();
        this.setupAudio();
        this.setupParticles();
        this.setupEventListeners();
        this.animate();
    }
    
    setupCanvas() {
        this.canvas.width = this.options.width;
        this.canvas.height = this.options.height;
        // DPI scaling
        const dpr = window.devicePixelRatio || 1;
        this.canvas.width *= dpr;
        this.canvas.height *= dpr;
        this.ctx.scale(dpr, dpr);
    }
    
    setupAudio() {
        if (this.options.audioEnabled) {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        }
    }
    
    setupParticles() {
        // Inizializza particelle per ogni colore nell'armonia
        const harmony = this.getHarmony(this.selectedColor, this.harmonyType);
        harmony.forEach((color, index) => {
            for (let i = 0; i < this.options.particleCount / harmony.length; i++) {
                this.particles.push(new Particle(color, index));
            }
        });
    }
    
    getHarmony(color, type) {
        // Algoritmi armonia colori
        switch(type) {
            case 'complementary':
                return [color, this.complementary(color)];
            case 'analogous':
                return this.analogous(color, 5);
            case 'triadic':
                return this.triadic(color);
            // ... altri tipi
        }
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
        
        // Clear canvas
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Update particles
        this.particles.forEach(particle => {
            particle.update();
            particle.draw(this.ctx);
        });
        
        // Draw color wheel
        this.drawColorWheel();
        
        // Draw harmony visualization
        this.drawHarmony();
    }
    
    drawColorWheel() {
        // Implementazione cerchio cromatico HSL
        const centerX = this.canvas.width / 2;
        const centerY = this.canvas.height / 2;
        const radius = Math.min(this.canvas.width, this.canvas.height) / 3;
        
        for (let angle = 0; angle < 360; angle += 1) {
            for (let r = 0; r < radius; r += 1) {
                const h = angle;
                const s = (r / radius) * 100;
                const l = 50;
                
                this.ctx.fillStyle = `hsl(${h}, ${s}%, ${l}%)`;
                this.ctx.beginPath();
                this.ctx.arc(
                    centerX + Math.cos(angle * Math.PI / 180) * r,
                    centerY + Math.sin(angle * Math.PI / 180) * r,
                    1, 0, Math.PI * 2
                );
                this.ctx.fill();
            }
        }
    }
    
    drawHarmony() {
        // Visualizza armonia corrente con D3.js o Canvas
        const harmony = this.getHarmony(this.selectedColor, this.harmonyType);
        // ... implementazione
    }
}

// Classe Particella
class Particle {
    constructor(color, groupIndex) {
        this.color = color;
        this.groupIndex = groupIndex;
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.vx = (Math.random() - 0.5) * 2;
        this.vy = (Math.random() - 0.5) * 2;
        this.size = Math.random() * 3 + 1;
    }
    
    update() {
        // Fisica particella (attrazione/repulsione)
        this.x += this.vx;
        this.y += this.vy;
        
        // Bounce boundaries
        if (this.x < 0 || this.x > canvas.width) this.vx *= -1;
        if (this.y < 0 || this.y > canvas.height) this.vy *= -1;
    }
    
    draw(ctx) {
        ctx.fillStyle = `hsl(${this.color.h}, ${this.color.s}%, ${this.color.l}%)`;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fill();
    }
}
```

---

## 🎨 CSS Styling

```css
.color-harmony-visualizer {
    position: relative;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.color-harmony-canvas {
    width: 100%;
    height: 600px;
    border-radius: 10px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
}

.harmony-controls {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.harmony-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 25px;
    background: rgba(255,255,255,0.2);
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.harmony-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-2px);
}

.harmony-btn.active {
    background: rgba(255,255,255,0.5);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
```

---

## 🔌 Integrazione WordPress

### Classe PHP

```php
namespace gik25microdata\Widgets;

class ColorHarmonyVisualizer {
    public static function init() {
        add_shortcode('color_harmony', [self::class, 'render']);
    }
    
    public static function render($atts) {
        wp_enqueue_script('color-harmony-visualizer', 
            plugins_url('assets/js/color-harmony-visualizer.js', __FILE__),
            ['gsap', 'd3'], '1.0.0', true);
        wp_enqueue_style('color-harmony-visualizer',
            plugins_url('assets/css/color-harmony-visualizer.css', __FILE__),
            [], '1.0.0');
        
        // Enqueue dependencies
        wp_enqueue_script('gsap', 'https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/gsap.min.js', [], '3.12.2', true);
        wp_enqueue_script('d3', 'https://d3js.org/d3.v7.min.js', [], '7.0.0', true);
        
        ob_start();
        ?>
        <div class="color-harmony-visualizer">
            <canvas class="color-harmony-canvas" id="harmony-canvas"></canvas>
            <div class="harmony-controls">
                <button class="harmony-btn" data-type="complementary">Complementari</button>
                <button class="harmony-btn" data-type="analogous">Analoghi</button>
                <button class="harmony-btn" data-type="triadic">Triadi</button>
                <!-- ... altri tipi -->
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const canvas = document.getElementById('harmony-canvas');
                const visualizer = new ColorHarmonyVisualizer(canvas, {
                    width: canvas.offsetWidth,
                    height: 600,
                    audioEnabled: true
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }
}
```

---

## 📊 Performance

### Ottimizzazioni
- **RequestAnimationFrame**: Animazioni ottimizzate
- **Object Pooling**: Riutilizzo particelle
- **Lazy Loading**: Carica solo quando necessario
- **Web Workers**: Calcoli pesanti in background (opzionale)

### Target
- **60 FPS**: Animazioni fluide
- **< 100ms**: Tempo inizializzazione
- **< 50MB**: Memoria utilizzata

---

## ♿ Accessibilità

- **Keyboard Navigation**: Supporto tastiera completo
- **Screen Reader**: ARIA labels
- **Reduced Motion**: Rispetta `prefers-reduced-motion`
- **Color Blind**: Supporto per daltonici (pattern/texture)

---

## 🚀 Prossimi Step

1. ✅ Implementare classe base JavaScript
2. ✅ Aggiungere sistema particelle
3. ✅ Integrare audio reattivo
4. ✅ Creare visualizzazioni D3.js
5. ✅ Aggiungere touch gestures
6. ✅ Testing e ottimizzazioni

---

**Ready to code?** 🎨🚀

