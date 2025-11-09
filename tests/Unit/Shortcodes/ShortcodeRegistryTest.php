<?php
/**
 * Test unitari per ShortcodeRegistry
 * 
 * @package ReviousMicrodata
 * @group Unit
 * @group Shortcodes
 */

namespace Tests\Unit\Shortcodes;

use PHPUnit\Framework\TestCase;

/**
 * Test ShortcodeRegistry
 * 
 * Nota: Questi sono test base. Per test completi serve WordPress caricato.
 */
class ShortcodeRegistryTest extends TestCase
{
    /**
     * Test che la classe esiste
     */
    public function test_shortcode_registry_class_exists(): void
    {
        $this->assertTrue(
            class_exists('\gik25microdata\Shortcodes\ShortcodeRegistry'),
            'ShortcodeRegistry class should exist'
        );
    }

    /**
     * Test che getItemsForAdmin restituisce un array
     */
    public function test_get_items_for_admin_returns_array(): void
    {
        if (class_exists('\gik25microdata\Shortcodes\ShortcodeRegistry')) {
            $items = \gik25microdata\Shortcodes\ShortcodeRegistry::getItemsForAdmin();
            
            $this->assertIsArray($items, 'getItemsForAdmin should return an array');
        } else {
            $this->markTestSkipped('ShortcodeRegistry class not available');
        }
    }

    /**
     * Test che gli items hanno la struttura corretta
     */
    public function test_items_have_correct_structure(): void
    {
        if (class_exists('\gik25microdata\Shortcodes\ShortcodeRegistry')) {
            $items = \gik25microdata\Shortcodes\ShortcodeRegistry::getItemsForAdmin();
            
            if (!empty($items)) {
                $firstItem = reset($items);
                
                $this->assertIsArray($firstItem, 'Item should be an array');
                $this->assertArrayHasKey('slug', $firstItem, 'Item should have slug key');
                $this->assertArrayHasKey('label', $firstItem, 'Item should have label key');
            }
        } else {
            $this->markTestSkipped('ShortcodeRegistry class not available');
        }
    }
}

