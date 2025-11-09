<?php
/**
 * Test unitari per SafeExecution
 * 
 * @package ReviousMicrodata
 * @group Unit
 * @group Utility
 */

namespace Tests\Unit\Utility;

use PHPUnit\Framework\TestCase;
use gik25microdata\Utility\SafeExecution;

/**
 * Test SafeExecution
 */
class SafeExecutionTest extends TestCase
{
    /**
     * Test che safe_execute esegue correttamente una funzione valida
     */
    public function test_safe_execute_executes_valid_function(): void
    {
        $result = SafeExecution::safe_execute(function() {
            return 'test result';
        });

        $this->assertEquals('test result', $result);
    }

    /**
     * Test che safe_execute gestisce eccezioni senza crashare
     */
    public function test_safe_execute_handles_exceptions(): void
    {
        $result = SafeExecution::safe_execute(function() {
            throw new \Exception('Test exception');
        }, 'default value');

        $this->assertEquals('default value', $result);
    }

    /**
     * Test che safe_execute restituisce default value su errore
     */
    public function test_safe_execute_returns_default_on_error(): void
    {
        $result = SafeExecution::safe_execute(function() {
            throw new \RuntimeException('Error');
        }, ['error' => true], true);

        $this->assertEquals(['error' => true], $result);
    }

    /**
     * Test che safe_execute gestisce errori fatali
     */
    public function test_safe_execute_handles_fatal_errors(): void
    {
        $result = SafeExecution::safe_execute(function() {
            // Simula errore fatale
            $obj = null;
            $obj->method();
        }, 'error handled');

        $this->assertEquals('error handled', $result);
    }

    /**
     * Test che safe_add_action restituisce true
     */
    public function test_safe_add_action_returns_true(): void
    {
        $result = SafeExecution::safe_add_action('test_hook', function() {
            return 'test';
        });

        $this->assertTrue($result);
    }

    /**
     * Test che safe_add_filter restituisce true
     */
    public function test_safe_add_filter_returns_true(): void
    {
        $result = SafeExecution::safe_add_filter('test_filter', function($value) {
            return $value;
        });

        $this->assertTrue($result);
    }

    /**
     * Test che safe_add_action gestisce callback null
     */
    public function test_safe_add_action_handles_null_callback(): void
    {
        $result = SafeExecution::safe_add_action('test_hook', null);
        
        // Dovrebbe restituire false o gestire gracefully
        $this->assertIsBool($result);
    }
}

