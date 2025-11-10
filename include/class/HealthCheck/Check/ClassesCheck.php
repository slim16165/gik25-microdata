<?php
namespace gik25microdata\HealthCheck\Check;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check per verificare classi PHP
 */
class ClassesCheck
{
    /**
     * Esegue il check
     * 
     * @return array Risultato del check
     */
    public static function run(): array
    {
        // Classi sempre richieste
        $required_classes = [
            'gik25microdata\PluginBootstrap',
        ];
        
        // Classi opzionali (dipendono da configurazione)
        $optional_classes = [
            'gik25microdata\Shortcodes\KitchenFinder', // Solo se kitchen_finder è attivo
            'gik25microdata\Shortcodes\AppNav', // Solo se app_nav è attivo
            'gik25microdata\REST\MCPApi', // Solo se MCP è attivo
            'gik25microdata\Widgets\ContextualWidgets', // Solo se attivo
            'gik25microdata\Shortcodes\GenericCarousel', // Solo se caroselli sono usati
        ];

        $existing_required = [];
        $missing_required = [];
        $existing_optional = [];
        $missing_optional = [];

        // Controlla classi richieste
        foreach ($required_classes as $class) {
            if (class_exists($class)) {
                $existing_required[] = $class;
            } else {
                $missing_required[] = $class;
            }
        }
        
        // Controlla classi opzionali
        foreach ($optional_classes as $class) {
            if (class_exists($class)) {
                $existing_optional[] = $class;
            } else {
                $missing_optional[] = $class;
            }
        }

        // Se mancano solo classi opzionali, è un warning
        if (empty($missing_required) && !empty($missing_optional)) {
            $status = 'success'; // Non è un errore se le classi opzionali mancano
            $message = sprintf('Classi base caricate (%d), opzionali: %d/%d', 
                count($existing_required),
                count($existing_optional),
                count($optional_classes)
            );
        } elseif (!empty($missing_required)) {
            $status = 'error';
            $message = sprintf('Classi base mancanti: %s', implode(', ', $missing_required));
        } else {
            $status = 'success';
            $message = sprintf('Tutte le classi caricate (%d base + %d opzionali)', 
                count($existing_required),
                count($existing_optional)
            );
        }

        $all_existing = array_merge($existing_required, $existing_optional);
        $all_missing = array_merge($missing_required, $missing_optional);

        return [
            'name' => 'Classi PHP',
            'status' => $status,
            'message' => $message,
            'details' => 'Caricate: ' . implode(', ', $all_existing) . "\n" .
                        (empty($missing_required) ? '' : 'Mancanti (richieste): ' . implode(', ', $missing_required) . "\n") .
                        (!empty($missing_optional) ? 'Mancanti (opzionali): ' . implode(', ', $missing_optional) : ''),
        ];
    }
}

