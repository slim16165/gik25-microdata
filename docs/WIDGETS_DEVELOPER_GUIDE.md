# Developer Guide - Widget Avanzatissimi

**Per sviluppatori che vogliono estendere o creare nuovi widget**

---

## 🏗️ Architettura

### Base Class

Tutti i widget estendono `AdvancedWidgetsBase`:

```php
class MyNewWidget extends AdvancedWidgetsBase
{
    protected static string $widget_name = 'my-new-widget';
    
    protected static array $js_dependencies = ['gsap'];
    
    protected static function needs_threejs(): bool { return true; }
    
    public static function init(): void
    {
        add_shortcode('my_widget', [self::class, 'render']);
    }
    
    public static function render(array $atts = []): string
    {
        // Implementation
    }
}
```

---

## 📝 Pattern Comuni

### 1. Widget Canvas 2D

```javascript
class MyCanvasWidget {
    constructor(container, options) {
        this.canvas = container.querySelector('canvas');
        this.ctx = this.canvas.getContext('2d');
        this.setupCanvas();
        this.animate();
    }
    
    setupCanvas() {
        const dpr = window.devicePixelRatio || 1;
        const rect = this.canvas.getBoundingClientRect();
        this.canvas.width = rect.width * dpr;
        this.canvas.height = rect.height * dpr;
        this.ctx.scale(dpr, dpr);
    }
    
    animate() {
        this.update();
        this.draw();
        requestAnimationFrame(() => this.animate());
    }
}
```

### 2. Widget Three.js 3D

```javascript
class My3DWidget {
    constructor(container, options) {
        this.setupScene();
        this.setupCamera();
        this.setupRenderer();
        this.setupLighting();
        this.animate();
    }
    
    setupScene() {
        this.scene = new THREE.Scene();
    }
    
    setupCamera() {
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;
        this.camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 1000);
    }
    
    setupRenderer() {
        this.renderer = new THREE.WebGLRenderer({ antialias: true });
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
        this.container.appendChild(this.renderer.domElement);
    }
    
    animate() {
        requestAnimationFrame(() => this.animate());
        this.renderer.render(this.scene, this.camera);
    }
}
```

---

## 🎨 Best Practices

### Performance
- Usa `requestAnimationFrame` per animazioni
- Limita numero particelle/oggetti
- Implementa object pooling
- Usa `will-change` CSS con cautela

### Accessibilità
- Supporta keyboard navigation
- Aggiungi ARIA labels
- Rispetta `prefers-reduced-motion`
- Fornisci fallback

### Mobile
- Testa su dispositivi reali
- Ottimizza per touch
- Riduci complessità su mobile
- Gestisci orientamento

---

## 🔌 Estendere Widget Esistenti

### Aggiungere Features

```php
// In classe PHP
public static function render(array $atts = []): string
{
    $atts = shortcode_atts([
        'new-feature' => 'default',
        // ... altri parametri
    ], $atts);
    
    // Implementation
}
```

```javascript
// In JavaScript
class MyWidget {
    constructor(container, options) {
        this.newFeature = options['new-feature'] || 'default';
        // Implementation
    }
}
```

---

**Vedi esempi completi nei widget implementati**

