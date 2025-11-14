<?php
namespace gik25microdata\SiteSpecific;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configurazione per un sito specifico
 */
class SiteConfig
{
    private string $domain;
    private string $specificFile;
    private string $name;
    private array $shortcodes;
    private array $filters;
    private array $actions;
    
    public function __construct(
        string $domain,
        string $specificFile,
        string $name = ''
    ) {
        $this->domain = $domain;
        $this->specificFile = $specificFile;
        $this->name = $name ?: $domain;
        $this->shortcodes = [];
        $this->filters = [];
        $this->actions = [];
    }
    
    /**
     * Aggiunge uno shortcode registrato da questo sito
     * 
     * @param string $tag Tag dello shortcode
     * @param callable $handler Handler
     * @return self
     */
    public function addShortcode(string $tag, callable $handler): self
    {
        $this->shortcodes[$tag] = $handler;
        return $this;
    }
    
    /**
     * Aggiunge un filtro WordPress
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback
     * @param int $priority Priorità
     * @return self
     */
    public function addFilter(string $hook, callable $callback, int $priority = 10): self
    {
        $this->filters[] = [
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority
        ];
        return $this;
    }
    
    /**
     * Aggiunge un'azione WordPress
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback
     * @param int $priority Priorità
     * @return self
     */
    public function addAction(string $hook, callable $callback, int $priority = 10): self
    {
        $this->actions[] = [
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority
        ];
        return $this;
    }
    
    public function getDomain(): string
    {
        return $this->domain;
    }
    
    public function getSpecificFile(): string
    {
        return $this->specificFile;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getShortcodes(): array
    {
        return $this->shortcodes;
    }
    
    public function getFilters(): array
    {
        return $this->filters;
    }
    
    public function getActions(): array
    {
        return $this->actions;
    }
}
