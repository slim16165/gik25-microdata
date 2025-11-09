<?php
/**
 * Test di integrazione per ShortcodeRegistry
 * 
 * @package ReviousMicrodata
 * @group Integration
 * @group Shortcodes
 */

namespace Tests\Integration\Shortcodes;

use PHPUnit\Framework\TestCase;

/**
 * Test integrazione ShortcodeRegistry con WordPress
 * 
 * Nota: Questi test richiedono WordPress caricato.
 * Per ora sono placeholder per test futuri.
 */
class ShortcodeRegistryIntegrationTest extends TestCase
{
    /**
     * Test che gli shortcode sono registrati correttamente
     * 
     * @group Integration
     */
    public function test_shortcodes_are_registered(): void
    {
        if (!function_exists('shortcode_exists')) {
            $this->markTestSkipped('WordPress not loaded - integration test requires WordPress');
            return;
        }

        // Test che shortcode comuni esistono
        $this->assertTrue(
            shortcode_exists('quote') || shortcode_exists('boxinfo'),
            'At least one shortcode should be registered'
        );
    }

    /**
     * Test che getItemsForAdmin restituisce shortcode abilitati
     */
    public function test_get_items_returns_enabled_shortcodes(): void
    {
        if (!class_exists('\gik25microdata\Shortcodes\ShortcodeRegistry')) {
            $this->markTestSkipped('ShortcodeRegistry not available');
            return;
        }

        $items = \gik25microdata\Shortcodes\ShortcodeRegistry::getItemsForAdmin();
        
        $this->assertIsArray($items);
        $this->assertGreaterThan(0, count($items), 'Should have at least one shortcode');
    }
}

