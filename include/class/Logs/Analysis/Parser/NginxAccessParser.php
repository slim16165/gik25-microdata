<?php
namespace gik25microdata\Logs\Analysis\Parser;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parser per log access Nginx
 */
final class NginxAccessParser extends AccessLogParserBase
{
    protected function getLogType(): string
    {
        return 'nginx_access';
    }
    
    protected function getContext(): string
    {
        return 'nginx';
    }
}

