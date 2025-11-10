<?php
namespace gik25microdata\Logs\Analysis\Parser;

use gik25microdata\Logs\Analysis\Parser\PhpErrorParser;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parser per log errori PHP-FPM (stesso formato di PHP error)
 */
final class PhpFpmErrorParser extends PhpErrorParser
{
    public function supports(string $type): bool
    {
        return $type === 'php_fpm_error';
    }
}

