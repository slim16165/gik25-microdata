<?php
namespace gik25microdata\ListOfPosts\Renderer;

use gik25microdata\ListOfPosts\Types\LinkBase;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interfaccia per i renderer di link
 * Definisce il contratto per tutti i renderer che generano HTML per link
 */
interface LinkRendererInterface
{
    /**
     * Renderizza un singolo link
     * 
     * @param LinkBase $link Dati del link da renderizzare
     * @param array $options Opzioni aggiuntive (es. ['removeIfSelf' => true, 'withImage' => true])
     * @return string HTML del link renderizzato
     */
    public function render(LinkBase $link, array $options = []): string;
    
    /**
     * Renderizza una collezione di link
     * 
     * @param \Illuminate\Support\Collection $links Collezione di LinkBase
     * @param array $options Opzioni aggiuntive
     * @return string HTML di tutti i link renderizzati
     */
    public function renderCollection(\Illuminate\Support\Collection $links, array $options = []): string;
    
    /**
     * Verifica se il renderer supporta una specifica opzione
     * 
     * @param string $option Nome dell'opzione
     * @return bool True se supportata
     */
    public function supports(string $option): bool;
}
