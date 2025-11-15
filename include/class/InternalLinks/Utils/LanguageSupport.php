<?php
/**
 * Language Support - Language detection and support
 *
 * @package gik25microdata\InternalLinks\Utils
 */

namespace gik25microdata\InternalLinks\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Language Support class
 */
class LanguageSupport
{
    /**
     * Detect language of text
     *
     * @param string $text Text
     * @return string Language code
     */
    public function detectLanguage($text)
    {
        // Simple detection based on WordPress locale
        $locale = get_locale();
        $lang = substr($locale, 0, 2);
        return $lang ?: 'it';
    }

    /**
     * Check if language is supported
     *
     * @param string $language Language code
     * @return bool Is supported
     */
    public function isLanguageSupported($language)
    {
        $stemmer = new Stemmer();
        $supported = $stemmer->getSupportedLanguages();
        return in_array($language, $supported, true);
    }
}

