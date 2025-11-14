<?php
namespace gik25microdata\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Widget Performance Monitor
 * 
 * Monitora performance dei widget avanzati
 * Fornisce metriche e ottimizzazioni automatiche
 */
class WidgetPerformanceMonitor
{
    /**
     * @var array<string, array> Metriche performance
     */
    private static array $metrics = [];
    
    /**
     * Inizializza il monitor
     */
    public static function init(): void
    {
        add_action('wp_footer', [self::class, 'outputPerformanceScript']);
    }
    
    /**
     * Registra metrica performance
     * 
     * @param string $widgetId ID widget
     * @param string $metric Nome metrica
     * @param float $value Valore
     */
    public static function recordMetric(string $widgetId, string $metric, float $value): void
    {
        if (!isset(self::$metrics[$widgetId])) {
            self::$metrics[$widgetId] = [];
        }
        
        if (!isset(self::$metrics[$widgetId][$metric])) {
            self::$metrics[$widgetId][$metric] = [];
        }
        
        self::$metrics[$widgetId][$metric][] = $value;
    }
    
    /**
     * Ottiene metriche aggregate
     * 
     * @param string $widgetId ID widget
     * @return array<string, float> Metriche aggregate
     */
    public static function getAggregatedMetrics(string $widgetId): array
    {
        if (!isset(self::$metrics[$widgetId])) {
            return [];
        }
        
        $aggregated = [];
        
        foreach (self::$metrics[$widgetId] as $metric => $values) {
            $aggregated[$metric] = [
                'avg' => array_sum($values) / count($values),
                'min' => min($values),
                'max' => max($values),
                'count' => count($values),
            ];
        }
        
        return $aggregated;
    }
    
    /**
     * Output script per monitoraggio performance client-side
     */
    public static function outputPerformanceScript(): void
    {
        ?>
        <script>
        (function() {
            'use strict';
            
            if (typeof PerformanceObserver === 'undefined') return;
            
            // Monitor FPS
            let lastTime = performance.now();
            let frameCount = 0;
            let fps = 0;
            
            function measureFPS() {
                frameCount++;
                const currentTime = performance.now();
                
                if (currentTime >= lastTime + 1000) {
                    fps = Math.round((frameCount * 1000) / (currentTime - lastTime));
                    frameCount = 0;
                    lastTime = currentTime;
                    
                    // Log if FPS drops below 30
                    if (fps < 30) {
                        console.warn('Widget Performance: FPS dropped to', fps);
                    }
                }
                
                requestAnimationFrame(measureFPS);
            }
            
            measureFPS();
            
            // Monitor memory (if available)
            if (performance.memory) {
                setInterval(() => {
                    const memory = performance.memory;
                    const usedMB = memory.usedJSHeapSize / 1048576;
                    const totalMB = memory.totalJSHeapSize / 1048576;
                    
                    if (usedMB > 100) {
                        console.warn('Widget Performance: High memory usage', usedMB.toFixed(2), 'MB');
                    }
                }, 5000);
            }
        })();
        </script>
        <?php
    }
}

