<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget Registry
 * 
 * Registry centralizzato per gestire tutti i widget avanzati
 * Fornisce metodi per listing, stats, e gestione widget
 */
class WidgetRegistry
{
    /**
     * @var array<string, array> Lista di tutti i widget registrati
     */
    private static array $widgets = [
        'color-harmony-visualizer' => [
            'class' => ColorHarmonyVisualizer::class,
            'name' => 'Color Harmony Visualizer',
            'shortcode' => 'color_harmony',
            'category' => 'colors',
            'complexity' => 'medium',
            'dependencies' => ['gsap', 'd3'],
        ],
        'palette-generator-particles' => [
            'class' => PaletteGeneratorParticles::class,
            'name' => 'Palette Generator con Particelle',
            'shortcode' => 'palette_generator',
            'category' => 'colors',
            'complexity' => 'medium',
            'dependencies' => ['gsap'],
        ],
        'product-comparison-cinematic' => [
            'class' => ProductComparisonCinematic::class,
            'name' => 'Product Comparison Cinematic',
            'shortcode' => 'product_comparison',
            'category' => 'products',
            'complexity' => 'medium',
            'dependencies' => ['gsap'],
        ],
        'room-simulator-isometric' => [
            'class' => RoomSimulatorIsometric::class,
            'name' => 'Room Simulator Isometrico',
            'shortcode' => 'room_simulator',
            'category' => 'rooms',
            'complexity' => 'high',
            'dependencies' => ['three', 'gsap', 'matter', 'hammer'],
        ],
        'ikea-hack-explorer-3d' => [
            'class' => IKEAHackExplorer3D::class,
            'name' => 'IKEA Hack Explorer 3D',
            'shortcode' => 'ikea_hack_explorer',
            'category' => 'ikea',
            'complexity' => 'high',
            'dependencies' => ['three', 'gsap', 'hammer'],
        ],
        'lighting-simulator' => [
            'class' => LightingSimulator::class,
            'name' => 'Lighting Simulator Real-Time',
            'shortcode' => 'lighting_simulator',
            'category' => 'rooms',
            'complexity' => 'very-high',
            'dependencies' => ['three'],
        ],
        'color-picker-3d' => [
            'class' => ColorPicker3D::class,
            'name' => 'Color Picker 3D Interattivo',
            'shortcode' => 'color_picker_3d',
            'category' => 'colors',
            'complexity' => 'high',
            'dependencies' => ['three', 'gsap', 'hammer'],
        ],
        'architectural-visualization-3d' => [
            'class' => ArchitecturalVisualization3D::class,
            'name' => 'Architectural Visualization 3D',
            'shortcode' => 'architectural_viz',
            'category' => 'architecture',
            'complexity' => 'high',
            'dependencies' => ['three', 'gsap', 'hammer'],
        ],
        'fluid-color-mixer' => [
            'class' => FluidColorMixer::class,
            'name' => 'Fluid Color Mixer',
            'shortcode' => 'fluid_color_mixer',
            'category' => 'colors',
            'complexity' => 'very-high',
            'dependencies' => [],
        ],
        'interactive-design-game' => [
            'class' => InteractiveDesignGame::class,
            'name' => 'Interactive Design Game',
            'shortcode' => 'design_game',
            'category' => 'engagement',
            'complexity' => 'high',
            'dependencies' => ['three', 'gsap', 'matter'],
        ],
        'color-room-recommender' => [
            'class' => ColorRoomRecommender::class,
            'name' => 'Color Room Recommender',
            'shortcode' => 'color_room_recommender',
            'category' => 'recommendations',
            'complexity' => 'medium',
            'dependencies' => ['gsap', 'd3'],
        ],
        'pantone-hub-dynamic' => [
            'class' => PantoneHubDynamic::class,
            'name' => 'Pantone Hub Dinamico',
            'shortcode' => 'pantone_hub',
            'category' => 'colors',
            'complexity' => 'medium',
            'dependencies' => ['gsap', 'd3'],
        ],
        'isometric-ikea-configurator' => [
            'class' => IsometricIKEAConfigurator::class,
            'name' => 'Isometric IKEA Configurator',
            'shortcode' => 'ikea_configurator',
            'category' => 'ikea',
            'complexity' => 'high',
            'dependencies' => ['three', 'gsap', 'matter', 'hammer'],
        ],
        'color-explosion-effect' => [
            'class' => ColorExplosionEffect::class,
            'name' => 'Color Explosion Effect',
            'shortcode' => 'color_explosion',
            'category' => 'effects',
            'complexity' => 'medium',
            'dependencies' => ['gsap'],
        ],
        'advanced-color-picker' => [
            'class' => AdvancedColorPicker::class,
            'name' => 'Advanced Color Picker',
            'shortcode' => 'advanced_color_picker',
            'category' => 'colors',
            'complexity' => 'medium',
            'dependencies' => ['gsap'],
        ],
    ];
    
    /**
     * Ottiene tutti i widget registrati
     * 
     * @return array<string, array> Array di widget
     */
    public static function getAllWidgets(): array
    {
        return self::$widgets;
    }
    
    /**
     * Ottiene widget per categoria
     * 
     * @param string $category Categoria widget
     * @return array<string, array> Array di widget filtrati
     */
    public static function getWidgetsByCategory(string $category): array
    {
        return array_filter(self::$widgets, function($widget) use ($category) {
            return $widget['category'] === $category;
        });
    }
    
    /**
     * Ottiene widget per complessità
     * 
     * @param string $complexity Livello complessità
     * @return array<string, array> Array di widget filtrati
     */
    public static function getWidgetsByComplexity(string $complexity): array
    {
        return array_filter(self::$widgets, function($widget) use ($complexity) {
            return $widget['complexity'] === $complexity;
        });
    }
    
    /**
     * Ottiene statistiche widget
     * 
     * @return array<string, mixed> Statistiche
     */
    public static function getStats(): array
    {
        $stats = [
            'total' => count(self::$widgets),
            'by_category' => [],
            'by_complexity' => [],
            'by_dependencies' => [],
        ];
        
        foreach (self::$widgets as $widget) {
            // Count by category
            $category = $widget['category'];
            $stats['by_category'][$category] = ($stats['by_category'][$category] ?? 0) + 1;
            
            // Count by complexity
            $complexity = $widget['complexity'];
            $stats['by_complexity'][$complexity] = ($stats['by_complexity'][$complexity] ?? 0) + 1;
            
            // Count by dependencies
            foreach ($widget['dependencies'] as $dep) {
                $stats['by_dependencies'][$dep] = ($stats['by_dependencies'][$dep] ?? 0) + 1;
            }
        }
        
        return $stats;
    }
    
    /**
     * Verifica se un widget è disponibile
     * 
     * @param string $widgetId ID widget
     * @return bool True se disponibile
     */
    public static function isWidgetAvailable(string $widgetId): bool
    {
        if (!isset(self::$widgets[$widgetId])) {
            return false;
        }
        
        $widget = self::$widgets[$widgetId];
        return class_exists($widget['class']);
    }
    
    /**
     * Ottiene informazioni widget
     * 
     * @param string $widgetId ID widget
     * @return array|null Informazioni widget o null
     */
    public static function getWidgetInfo(string $widgetId): ?array
    {
        return self::$widgets[$widgetId] ?? null;
    }
}

