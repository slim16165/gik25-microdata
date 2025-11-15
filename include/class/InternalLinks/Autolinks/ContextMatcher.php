<?php
/**
 * Context Matcher - Matches keywords with context requirements
 *
 * @package gik25microdata\InternalLinks\Autolinks
 */

namespace gik25microdata\InternalLinks\Autolinks;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Context Matcher class
 */
class ContextMatcher
{
    /**
     * Check context for a match
     *
     * @param array $match Match data
     * @param AutolinkRule $rule Rule to check
     * @param string $content Full content
     * @return bool Matches context
     */
    public function checkContext($match, $rule, $content)
    {
        $position = $match['position'];
        $before_text = substr($content, max(0, $position - 100), min(100, $position));
        $after_text = substr($content, $position + $match['length'], 100);

        // Check string before/after
        if (!$this->checkStringContext($before_text, $after_text, $rule)) {
            return false;
        }

        // Check keyword before/after
        if (!$this->checkKeywordContext($before_text, $after_text, $rule)) {
            return false;
        }

        return true;
    }

    /**
     * Filter matches by context
     *
     * @param array $matches Matches array
     * @param AutolinkRule $rule Rule
     * @param string $content Full content
     * @return array Filtered matches
     */
    public function filterByContext($matches, $rule, $content)
    {
        $filtered = [];

        foreach ($matches as $match) {
            if ($this->checkContext($match, $rule, $content)) {
                $filtered[] = $match;
            }
        }

        return $filtered;
    }

    /**
     * Check string context (before/after)
     *
     * @param string $before Text before
     * @param string $after Text after
     * @param AutolinkRule $rule Rule
     * @return bool Matches
     */
    private function checkStringContext($before, $after, $rule)
    {
        // String before check
        $string_before_pattern = $this->getStringPattern($rule->string_before);
        if ($string_before_pattern && !preg_match('/' . $string_before_pattern . '$/', $before)) {
            return false;
        }

        // String after check
        $string_after_pattern = $this->getStringPattern($rule->string_after);
        if ($string_after_pattern && !preg_match('/^' . $string_after_pattern . '/', $after)) {
            return false;
        }

        return true;
    }

    /**
     * Check keyword context (before/after)
     *
     * @param string $before Text before
     * @param string $after Text after
     * @param AutolinkRule $rule Rule
     * @return bool Matches
     */
    private function checkKeywordContext($before, $after, $rule)
    {
        // Keyword before check
        if (!empty($rule->keyword_before)) {
            $pattern = '/' . preg_quote($rule->keyword_before, '/') . '/iu';
            if (!preg_match($pattern, $before)) {
                return false;
            }
        }

        // Keyword after check
        if (!empty($rule->keyword_after)) {
            $pattern = '/' . preg_quote($rule->keyword_after, '/') . '/iu';
            if (!preg_match($pattern, $after)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get string pattern for string_before/string_after
     *
     * @param int $type Type (1=word boundary, 2=space, 3=comma, 4=dot, 5=none)
     * @return string|null Pattern
     */
    private function getStringPattern($type)
    {
        switch ($type) {
            case 1:
                return '\\b';
            case 2:
                return '\\s';
            case 3:
                return ',';
            case 4:
                return '\\.';
            case 5:
                return null;
            default:
                return '\\b';
        }
    }
}

