# Guida Utilizzo Widget Avanzatissimi

**Versione**: 1.0.0  
**Data**: 2025-01-30

---

## 📋 Elenco Completo Widget (15)

### 🎨 Widget Colori (6)

1. **Color Harmony Visualizer**
   - Shortcode: `[color_harmony]`
   - Parametri: `particles`, `audio`, `harmony`
   - Esempio: `[color_harmony particles="150" harmony="triadic"]`

2. **Palette Generator con Particelle**
   - Shortcode: `[palette_generator]`
   - Parametri: `particles`, `audio`
   - Esempio: `[palette_generator particles="200"]`

3. **Color Picker 3D Interattivo**
   - Shortcode: `[color_picker_3d]`
   - Parametri: `audio`, `particles`
   - Esempio: `[color_picker_3d audio="true"]`

4. **Fluid Color Mixer**
   - Shortcode: `[fluid_color_mixer]`
   - Parametri: `viscosity`
   - Esempio: `[fluid_color_mixer viscosity="medium"]`

5. **Pantone Hub Dinamico**
   - Shortcode: `[pantone_hub]`
   - Parametri: `year`, `limit`
   - Esempio: `[pantone_hub year="2024"]`

6. **Advanced Color Picker**
   - Shortcode: `[advanced_color_picker]`
   - Parametri: `mode`, `show-palette`
   - Esempio: `[advanced_color_picker mode="hsl"]`

### 🏠 Widget Stanze (2)

7. **Room Simulator Isometrico**
   - Shortcode: `[room_simulator]`
   - Parametri: `room`, `width`, `height`
   - Esempio: `[room_simulator room="cucina"]`

8. **Lighting Simulator Real-Time**
   - Shortcode: `[lighting_simulator]`
   - Parametri: `room`, `time`
   - Esempio: `[lighting_simulator room="soggiorno" time="14"]`

### 🏪 Widget IKEA (2)

9. **IKEA Hack Explorer 3D**
   - Shortcode: `[ikea_hack_explorer]`
   - Parametri: `line`, `limit`
   - Esempio: `[ikea_hack_explorer line="billy"]`

10. **Isometric IKEA Configurator**
    - Shortcode: `[ikea_configurator]`
    - Parametri: `line`, `room`
    - Esempio: `[ikea_configurator line="billy" room="soggiorno"]`

### 🏛️ Widget Architettura (1)

11. **Architectural Visualization 3D**
    - Shortcode: `[architectural_viz]`
    - Parametri: `architect`, `flythrough`
    - Esempio: `[architectural_viz architect="renzo-piano"]`

### 🎮 Widget Engagement (2)

12. **Interactive Design Game**
    - Shortcode: `[design_game]`
    - Parametri: `difficulty`
    - Esempio: `[design_game difficulty="hard"]`

13. **Product Comparison Cinematic**
    - Shortcode: `[product_comparison]`
    - Parametri: `products`, `animation`
    - Esempio: `[product_comparison products="billy,kallax"]`

### ✨ Widget Effetti (1)

14. **Color Explosion Effect**
    - Shortcode: `[color_explosion]`
    - Parametri: `color`, `particles`
    - Esempio: `[color_explosion color="#FF0000" particles="500"]`

### 🎯 Widget Raccomandazioni (1)

15. **Color Room Recommender**
    - Shortcode: `[color_room_recommender]`
    - Parametri: `color`, `room`
    - Esempio: `[color_room_recommender color="verde-salvia" room="cucina"]`

---

## 🎨 Esempi Utilizzo Avanzati

### Combinazione Widget

```php
// Hub colori completo
[color_harmony]
[pantone_hub year="2024"]
[color_room_recommender]

// Hub IKEA completo
[ikea_hack_explorer line="billy"]
[ikea_configurator line="billy" room="soggiorno"]
[product_comparison products="billy,kallax,besta"]

// Hub stanze completo
[room_simulator room="cucina"]
[lighting_simulator room="cucina" time="12"]
[color_room_recommender room="cucina"]
```

---

## ⚡ Performance Tips

1. **Lazy Loading**: I widget caricano assets solo quando presenti
2. **Code Splitting**: Bundle separati per ogni widget
3. **Caching**: Utilizza cache WordPress per query
4. **Reduced Motion**: Rispetta preferenze utente

---

## 🔧 Troubleshooting

### Widget non si carica
- Verifica che la classe PHP esista
- Controlla console browser per errori
- Verifica che dipendenze siano caricate

### Performance lenta
- Riduci numero particelle
- Attiva reduced motion
- Verifica memoria disponibile

---

**Documentazione completa**: Vedi `docs/ADVANCED_WIDGETS_COMPLETE.md`

