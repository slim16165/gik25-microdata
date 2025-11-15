<?php
/**
 * Stemmer - Multi-language stemming support
 *
 * @package gik25microdata\InternalLinks\Utils
 */

namespace gik25microdata\InternalLinks\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stemmer class using wamania/php-stemmer
 */
class Stemmer
{
    /**
     * Stemmer instances cache
     *
     * @var array
     */
    private static $stemmers = [];

    /**
     * Stem cache
     *
     * @var array
     */
    private static $stem_cache = [];

    /**
     * Max cache size
     */
    const MAX_CACHE_SIZE = 25000;

    /**
     * Stem a word
     *
     * @param string $word Word to stem
     * @param string $language Language code (it, en, es, fr, de, etc.)
     * @return string Stemmed word
     */
    public function stem($word, $language = 'it')
    {
        if (empty($word) || !is_string($word)) {
            return $word;
        }

        // Check cache first
        $cache_key = $language . ':' . strtolower($word);
        if (isset(self::$stem_cache[$cache_key])) {
            return self::$stem_cache[$cache_key];
        }

        // Load stemmer for language
        $stemmer = $this->loadStemmer($language);
        if (!$stemmer) {
            // Fallback: return lowercase word
            return strtolower(trim($word));
        }

        // Convert to lowercase
        $word_lower = $this->strtolower($word);

        // Check UTF-8
        if (!$this->isUtf8($word_lower)) {
            $converted = $this->codesToChars($word_lower);
            if ($this->isUtf8($converted)) {
                $word_lower = $converted;
            } else {
                // Can't process, return original
                return $word;
            }
        }

        // Stem the word
        try {
            $stemmed = $stemmer->stem($word_lower);
        } catch (\Exception $e) {
            // On error, return lowercase word
            $stemmed = $word_lower;
        }

        // Update cache
        $this->updateCache($cache_key, $stemmed);

        return $stemmed;
    }

    /**
     * Load stemmer for language
     *
     * @param string $language Language code
     * @return object|null Stemmer instance or null
     */
    public function loadStemmer($language)
    {
        // Check if already loaded
        if (isset(self::$stemmers[$language])) {
            return self::$stemmers[$language];
        }

        // Check if library is available
        if (!class_exists('\Wamania\Snowball\Utf8')) {
            return null;
        }

        // Map language codes to stemmer classes
        $stemmer_classes = [
            'it' => '\Wamania\Snowball\Italian',
            'en' => '\Wamania\Snowball\English',
            'es' => '\Wamania\Snowball\Spanish',
            'fr' => '\Wamania\Snowball\French',
            'de' => '\Wamania\Snowball\German',
            'pt' => '\Wamania\Snowball\Portuguese',
            'nl' => '\Wamania\Snowball\Dutch',
            'ru' => '\Wamania\Snowball\Russian',
            'da' => '\Wamania\Snowball\Danish',
            'no' => '\Wamania\Snowball\Norwegian',
            'sv' => '\Wamania\Snowball\Swedish',
            'ro' => '\Wamania\Snowball\Romanian',
            'fi' => '\Wamania\Snowball\Finnish',
        ];

        $language = strtolower($language);
        if (!isset($stemmer_classes[$language])) {
            // Default to English
            $language = 'en';
        }

        $class = $stemmer_classes[$language];
        if (!class_exists($class)) {
            return null;
        }

        try {
            self::$stemmers[$language] = new $class();
            return self::$stemmers[$language];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get supported languages
     *
     * @return array Supported languages
     */
    public function getSupportedLanguages()
    {
        return [
            'it' => 'Italian',
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'pt' => 'Portuguese',
            'nl' => 'Dutch',
            'ru' => 'Russian',
            'da' => 'Danish',
            'no' => 'Norwegian',
            'sv' => 'Swedish',
            'ro' => 'Romanian',
            'fi' => 'Finnish',
        ];
    }

    /**
     * Check if string is UTF-8
     *
     * @param string $string String to check
     * @return bool Is UTF-8
     */
    private function isUtf8($string)
    {
        if (class_exists('\Wamania\Snowball\Utf8')) {
            return \Wamania\Snowball\Utf8::check($string);
        }
        return mb_check_encoding($string, 'UTF-8');
    }

    /**
     * Convert to lowercase (UTF-8 safe)
     *
     * @param string $string String to convert
     * @return string Lowercase string
     */
    private function strtolower($string)
    {
        if (class_exists('\Wamania\Snowball\Utf8')) {
            return \Wamania\Snowball\Utf8::strtolower($string);
        }
        return mb_strtolower($string, 'UTF-8');
    }

    /**
     * Convert HTML entities to characters
     *
     * @param string $string String to convert
     * @return string Converted string
     */
    private function codesToChars($string)
    {
        $conversion_table = [
            "&#2013266112;" => "À",
            "&#2013266113;" => "Á",
            "&#2013266114;" => "Â",
            "&#2013266115;" => "Ã",
            "&#2013266116;" => "Ä",
            "&#2013266117;" => "Å",
            "&#2013266118;" => "Æ",
            "&#2013266119;" => "Ç",
            "&#2013266120;" => "È",
            "&#2013266121;" => "É",
            "&#2013266122;" => "Ê",
            "&#2013266123;" => "Ë",
            "&#2013266140;" => "Ì",
            "&#2013266141;" => "Í",
            "&#2013266142;" => "Î",
            "&#2013266143;" => "Ï",
            "&#2013266129;" => "Ñ",
            "&#2013266130;" => "Ò",
            "&#2013266131;" => "Ó",
            "&#2013266132;" => "Ô",
            "&#2013266133;" => "Õ",
            "&#2013266134;" => "Ö",
            "&#2013266136;" => "Ø",
            "&#2013266137;" => "Ù",
            "&#2013266138;" => "Ú",
            "&#2013266139;" => "Û",
            "&#2013266140;" => "Ü",
            "&#2013265923;" => "Ý",
            "\u00df" => "ß",
            "&#2013266144;" => "à",
            "&#2013266145;" => "á",
            "&#2013266146;" => "â",
            "&#2013266147;" => "ã",
            "&#2013266148;" => "ä",
            "&#2013266149;" => "å",
            "&#2013266150;" => "æ",
            "&#2013266151;" => "ç",
            "&#2013266152;" => "è",
            "&#2013266153;" => "é",
            "&#2013266154;" => "ê",
            "&#2013266155;" => "ë",
            "&#2013266156;" => "ì",
            "&#2013266157;" => "í",
            "&#2013266158;" => "î",
            "&#2013266159;" => "ï",
            "&#2013266160;" => "ð",
            "&#2013266161;" => "ñ",
            "&#2013266162;" => "ò",
            "&#2013266163;" => "ó",
            "&#2013266164;" => "ô",
            "&#2013266165;" => "õ",
            "&#2013266166;" => "ö",
            "&#2013266168;" => "ø",
            "&#2013266169;" => "ù",
            "&#2013266170;" => "ú",
            "&#2013266171;" => "û",
            "&#2013266172;" => "ü",
            "&#2013266173;" => "ý",
            "&#2013266175;" => "ÿ",
            "\u2019" => "'"
        ];

        return str_replace(
            array_keys($conversion_table),
            array_values($conversion_table),
            html_entity_decode(mb_convert_encoding($string, "HTML-ENTITIES"), ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Update stem cache
     *
     * @param string $key Cache key
     * @param string $value Stemmed word
     * @return void
     */
    private function updateCache($key, $value)
    {
        if (count(self::$stem_cache) >= self::MAX_CACHE_SIZE) {
            // Remove oldest entry
            array_shift(self::$stem_cache);
        }
        self::$stem_cache[$key] = $value;
    }
}

