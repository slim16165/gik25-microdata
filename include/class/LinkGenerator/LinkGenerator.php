<?php
namespace gik25microdata\LinkGenerator;

use gik25microdata\ListOfPosts\WPPostsHelper;
use gik25microdata\ListOfPosts\HtmlTemplate;
use gik25microdata\Widgets\ColorWidget;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generatore unificato di link per astrarre la logica duplicata
 * Supporta diversi stili: standard, thumbnail, carousel
 */
class LinkGenerator
{
    /**
     * Genera un link standard (senza immagine)
     * 
     * @param string $target_url URL di destinazione
     * @param string $nome Testo del link
     * @param string $commento Commento opzionale
     * @param bool $removeIfSelf Se true, rimuove il link se punta al post corrente
     * @return string HTML del link
     */
    public static function generateStandardLink(
        string $target_url,
        string $nome,
        string $commento = '',
        bool $removeIfSelf = true
    ): string {
        list($target_post, $noLink, $debugMsg) = WPPostsHelper::GetPostData($target_url, $removeIfSelf);
        
        if ($noLink && $removeIfSelf) {
            return ''; // Link rimosso perché punta al post corrente
        }
        
        $commento = self::parseComment($commento);
        return HtmlTemplate::GetTemplateNoThumbnail($target_url, $nome, $commento, $noLink);
    }
    
    /**
     * Genera un link con thumbnail
     * 
     * @param string $target_url URL di destinazione
     * @param string $nome Testo del link
     * @param string $commento Commento opzionale
     * @param bool $removeIfSelf Se true, rimuove il link se punta al post corrente
     * @return string HTML del link
     */
    public static function generateLinkWithThumbnail(
        string $target_url,
        string $nome,
        string $commento = '',
        bool $removeIfSelf = true
    ): string {
        list($target_post, $noLink, $debugMsg) = WPPostsHelper::GetPostData($target_url, $removeIfSelf);
        
        if ($noLink && $removeIfSelf) {
            return ''; // Link rimosso perché punta al post corrente
        }
        
        $commento = self::parseComment($commento);
        return HtmlTemplate::GetTemplateWithThumbnail($target_url, $nome, $commento, $target_post, $noLink);
    }
    
    /**
     * Genera un link per carousel (stile TotalDesign)
     * 
     * @param string $target_url URL di destinazione
     * @param string $nome Testo del link
     * @return string HTML del link
     */
    public static function generateCarouselLink(
        string $target_url,
        string $nome
    ): string {
        return ColorWidget::GetLinkWithImageCarousel($target_url, $nome);
    }
    
    /**
     * Genera un link semplice (solo testo, senza controllo post corrente)
     * Utile per link esterni o quando non serve il controllo
     * 
     * @param string $target_url URL di destinazione
     * @param string $nome Testo del link
     * @return string HTML del link
     */
    public static function generateSimpleLink(
        string $target_url,
        string $nome
    ): string {
        $safe_url = esc_url($target_url);
        $safe_nome = esc_html($nome);
        return "<a href=\"$safe_url\">$safe_nome</a>";
    }
    
    /**
     * Genera un link con controllo post corrente ma senza rimozione
     * Mostra il testo senza link se punta al post corrente
     * 
     * @param string $target_url URL di destinazione
     * @param string $nome Testo del link
     * @return string HTML del link o testo
     */
    public static function generateLinkIfNotSelf(
        string $target_url,
        string $nome
    ): string {
        global $post;
        
        if (!$post || !is_a($post, 'WP_Post') || !isset($post->ID)) {
            return self::generateSimpleLink($target_url, $nome);
        }
        
        $permalink = get_permalink($post->ID);
        $target_url = WPPostsHelper::ReplaceTargetUrlIfStaging($target_url);
        
        if ($permalink === $target_url) {
            return esc_html($nome); // Solo testo, senza link
        }
        
        return self::generateSimpleLink($target_url, $nome);
    }
    
    /**
     * Parsa il commento aggiungendo parentesi se necessario
     * 
     * @param string $commento
     * @return string
     */
    private static function parseComment(string $commento): string
    {
        if (empty($commento)) {
            return '';
        }
        
        if (strpos($commento, '(') === false) {
            return " ($commento)";
        }
        
        return $commento;
    }
}
