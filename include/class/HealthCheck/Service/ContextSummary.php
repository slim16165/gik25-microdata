<?php
namespace gik25microdata\HealthCheck\Service;

use gik25microdata\HealthCheck\HealthCheckConstants;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Service per generare riepiloghi dei contesti di esecuzione
 */
class ContextSummary
{
    /**
     * Genera riepilogo dei contesti di esecuzione
     * 
     * @param array $issues Array di issue con campo 'contexts'
     * @return string Riepilogo formattato o stringa vuota
     */
    public static function build(array $issues): string
    {
        $contexts_count = array_fill_keys(array_keys(HealthCheckConstants::CONTEXT_LABELS), 0);
        
        foreach ($issues as $issue) {
            if (!empty($issue['contexts'])) {
                foreach ($issue['contexts'] as $context) {
                    if (isset($contexts_count[$context])) {
                        $contexts_count[$context]++;
                    }
                }
            }
        }
        
        $summary_parts = [];
        foreach ($contexts_count as $context => $count) {
            if ($count > 0) {
                $summary_parts[] = HealthCheckConstants::getContextLabel($context) . ': ' . $count;
            }
        }
        
        if (empty($summary_parts)) {
            return '';
        }
        
        return "\nRiepilogo per contesto di esecuzione:\n" . implode(', ', $summary_parts);
    }
}

