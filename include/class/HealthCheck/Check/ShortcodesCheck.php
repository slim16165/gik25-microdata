<?php
namespace gik25microdata\HealthCheck\Check;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check per verificare shortcode registrati
 */
class ShortcodesCheck
{
    /**
     * Esegue il check
     * 
     * @return array Risultato del check
     */
    public static function run(): array
    {
        global $shortcode_tags;
        
        // Debug: verifica quanti shortcode sono registrati in totale
        $total_registered = is_array($shortcode_tags) ? count($shortcode_tags) : 0;
        
        // Shortcode base MINIMI che devono sempre esistere
        // Questi sono quelli che vengono istanziati direttamente nei file
        $required_shortcodes = [
            'md_boxinfo', 'boxinfo', 'boxinformativo', // Boxinfo
            'md_quote', 'quote', // Quote
            'youtube', // Youtube
            'telefono', // Telefono
            'md_progressbar', 'progressbar', // Progressbar
            'slidingbox', // Slidingbox
            'flipbox', 'md_flipbox', // Flipbox
            'blinkingbutton', 'md_blinkingbutton', // BlinkingButton
            'perfectpullquote', // Perfectpullquote
            'prezzo', // Prezzo
            'flexlist', // Flexlist
        ];
        
        // Shortcode opzionali (dipendono da configurazione sito o file site_specific)
        $optional_shortcodes = [
            'kitchen_finder', // Solo se KitchenFinder.php è caricato e istanziato
            'app_nav', // Solo se AppNav.php è caricato e istanziato
            'carousel', 'list', 'grid', // Solo se GenericCarousel è istanziato
            'link_colori', 'grafica3d', 'archistar', // Solo se totaldesign_specific.php è caricato
        ];

        $missing_required = [];
        $registered_required = [];
        $registered_optional = [];
        $missing_optional = [];

        // Controlla shortcode richiesti
        foreach ($required_shortcodes as $tag) {
            if (isset($shortcode_tags[$tag])) {
                $registered_required[] = $tag;
            } else {
                $missing_required[] = $tag;
            }
        }
        
        // Controlla shortcode opzionali
        foreach ($optional_shortcodes as $tag) {
            if (isset($shortcode_tags[$tag])) {
                $registered_optional[] = $tag;
            } else {
                $missing_optional[] = $tag;
            }
        }
        
        $all_registered = array_merge($registered_required, $registered_optional);

        // Determina status
        if (!empty($missing_required)) {
            $status = 'error';
            $message = sprintf('Shortcode base mancanti: %d/%d (%s)', 
                count($missing_required),
                count($required_shortcodes),
                implode(', ', array_slice($missing_required, 0, 5)) . (count($missing_required) > 5 ? '...' : '')
            );
        } elseif (!empty($registered_required)) {
            // Se almeno alcuni shortcode base sono registrati, è un successo
            // (potrebbero mancare alcuni opzionali, ma non è un errore)
            $status = 'success';
            $message = sprintf('Shortcode base OK (%d/%d)', 
                count($registered_required),
                count($required_shortcodes)
            );
            if (!empty($registered_optional)) {
                $message .= sprintf(', opzionali: %d', count($registered_optional));
            }
        } else {
            // Nessuno shortcode registrato - problema grave
            $status = 'error';
            $message = sprintf('Nessuno shortcode registrato (totale WordPress: %d)', $total_registered);
        }

        // Dettagli estesi
        $details = sprintf("Totale shortcode WordPress registrati: %d\n", $total_registered);
        $details .= sprintf("Shortcode plugin richiesti: %d/%d registrati\n", 
            count($registered_required), 
            count($required_shortcodes)
        );
        
        if (!empty($registered_required)) {
            $details .= "Registrati (richiesti): " . implode(', ', $registered_required) . "\n";
        }
        
        if (!empty($missing_required)) {
            $details .= "Mancanti (richiesti): " . implode(', ', $missing_required) . "\n";
        }
        
        if (!empty($registered_optional)) {
            $details .= "Registrati (opzionali): " . implode(', ', $registered_optional) . "\n";
        }
        
        if (!empty($missing_optional)) {
            $details .= "Mancanti (opzionali): " . implode(', ', $missing_optional) . "\n";
        }
        
        // Debug aggiuntivo: lista tutti gli shortcode WordPress registrati (primi 20)
        if ($total_registered > 0) {
            $all_wp_shortcodes = array_keys($shortcode_tags);
            $details .= "\nPrimi 20 shortcode WordPress registrati: " . implode(', ', array_slice($all_wp_shortcodes, 0, 20));
            if ($total_registered > 20) {
                $details .= sprintf(" ... (e altri %d)", $total_registered - 20);
            }
        }

        return [
            'name' => 'Shortcode Registrati',
            'status' => $status,
            'message' => $message,
            'details' => $details,
        ];
    }
}

