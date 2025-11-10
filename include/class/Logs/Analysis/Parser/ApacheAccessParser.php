<?php
namespace gik25microdata\Logs\Analysis\Parser;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parser per log access Apache
 */
final class ApacheAccessParser extends AccessLogParserBase
{
    protected function getLogType(): string
    {
        return 'apache_access';
    }
    
    protected function getContext(): string
    {
        return 'apache';
    }
}

