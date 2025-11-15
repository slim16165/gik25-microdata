<?php
/**
 * Autolink Rule Model
 *
 * @package gik25microdata\InternalLinks\Autolinks
 */

namespace gik25microdata\InternalLinks\Autolinks;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autolink Rule model class
 */
class AutolinkRule
{
    /**
     * Rule ID
     *
     * @var int
     */
    public $id;

    /**
     * Rule name
     *
     * @var string
     */
    public $name;

    /**
     * Keyword to match
     *
     * @var string
     */
    public $keyword;

    /**
     * Target URL
     *
     * @var string
     */
    public $url;

    /**
     * Anchor text
     *
     * @var string
     */
    public $anchor_text;

    /**
     * Title attribute
     *
     * @var string
     */
    public $title;

    /**
     * String before (1=word boundary, 2=space, 3=comma, 4=dot, 5=none)
     *
     * @var int
     */
    public $string_before = 1;

    /**
     * String after (1=word boundary, 2=space, 3=comma, 4=dot, 5=none)
     *
     * @var int
     */
    public $string_after = 1;

    /**
     * Keyword before (context)
     *
     * @var string
     */
    public $keyword_before = '';

    /**
     * Keyword after (context)
     *
     * @var string
     */
    public $keyword_after = '';

    /**
     * Case insensitive search
     *
     * @var bool
     */
    public $case_insensitive = false;

    /**
     * Use stemming
     *
     * @var bool
     */
    public $use_stemming = false;

    /**
     * Language for stemming
     *
     * @var string
     */
    public $language = 'it';

    /**
     * Max links per post
     *
     * @var int
     */
    public $max_links_per_post = 1;

    /**
     * Same URL limit
     *
     * @var int
     */
    public $same_url_limit = 1;

    /**
     * Priority
     *
     * @var int
     */
    public $priority = 0;

    /**
     * Post types (JSON array)
     *
     * @var array
     */
    public $post_types = [];

    /**
     * Categories (JSON array)
     *
     * @var array
     */
    public $categories = [];

    /**
     * Tags (JSON array)
     *
     * @var array
     */
    public $tags = [];

    /**
     * Term group ID
     *
     * @var int
     */
    public $term_group_id = 0;

    /**
     * Category ID
     *
     * @var int
     */
    public $category_id = 0;

    /**
     * Open in new tab
     *
     * @var bool
     */
    public $open_new_tab = false;

    /**
     * Use nofollow
     *
     * @var bool
     */
    public $use_nofollow = false;

    /**
     * Enabled
     *
     * @var bool
     */
    public $enabled = true;

    /**
     * Create from database row
     *
     * @param array $row Database row
     * @return AutolinkRule
     */
    public static function fromArray($row)
    {
        $rule = new self();
        $rule->id = isset($row['id']) ? intval($row['id']) : 0;
        $rule->name = isset($row['name']) ? $row['name'] : '';
        $rule->keyword = isset($row['keyword']) ? $row['keyword'] : '';
        $rule->url = isset($row['url']) ? $row['url'] : '';
        $rule->anchor_text = isset($row['anchor_text']) ? $row['anchor_text'] : '';
        $rule->title = isset($row['title']) ? $row['title'] : '';
        $rule->string_before = isset($row['string_before']) ? intval($row['string_before']) : 1;
        $rule->string_after = isset($row['string_after']) ? intval($row['string_after']) : 1;
        $rule->keyword_before = isset($row['keyword_before']) ? $row['keyword_before'] : '';
        $rule->keyword_after = isset($row['keyword_after']) ? $row['keyword_after'] : '';
        $rule->case_insensitive = isset($row['case_insensitive']) ? (bool) $row['case_insensitive'] : false;
        $rule->use_stemming = isset($row['use_stemming']) ? (bool) $row['use_stemming'] : false;
        $rule->language = isset($row['language']) ? $row['language'] : 'it';
        $rule->max_links_per_post = isset($row['max_links_per_post']) ? intval($row['max_links_per_post']) : 1;
        $rule->same_url_limit = isset($row['same_url_limit']) ? intval($row['same_url_limit']) : 1;
        $rule->priority = isset($row['priority']) ? intval($row['priority']) : 0;
        $rule->post_types = isset($row['post_types']) ? json_decode($row['post_types'], true) : [];
        $rule->categories = isset($row['categories']) ? json_decode($row['categories'], true) : [];
        $rule->tags = isset($row['tags']) ? json_decode($row['tags'], true) : [];
        $rule->term_group_id = isset($row['term_group_id']) ? intval($row['term_group_id']) : 0;
        $rule->category_id = isset($row['category_id']) ? intval($row['category_id']) : 0;
        $rule->open_new_tab = isset($row['open_new_tab']) ? (bool) $row['open_new_tab'] : false;
        $rule->use_nofollow = isset($row['use_nofollow']) ? (bool) $row['use_nofollow'] : false;
        $rule->enabled = isset($row['enabled']) ? (bool) $row['enabled'] : true;

        return $rule;
    }

    /**
     * Convert to array for database
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'keyword' => $this->keyword,
            'url' => $this->url,
            'anchor_text' => $this->anchor_text,
            'title' => $this->title,
            'string_before' => $this->string_before,
            'string_after' => $this->string_after,
            'keyword_before' => $this->keyword_before,
            'keyword_after' => $this->keyword_after,
            'case_insensitive' => $this->case_insensitive ? 1 : 0,
            'use_stemming' => $this->use_stemming ? 1 : 0,
            'language' => $this->language,
            'max_links_per_post' => $this->max_links_per_post,
            'same_url_limit' => $this->same_url_limit,
            'priority' => $this->priority,
            'post_types' => json_encode($this->post_types),
            'categories' => json_encode($this->categories),
            'tags' => json_encode($this->tags),
            'term_group_id' => $this->term_group_id,
            'category_id' => $this->category_id,
            'open_new_tab' => $this->open_new_tab ? 1 : 0,
            'use_nofollow' => $this->use_nofollow ? 1 : 0,
            'enabled' => $this->enabled ? 1 : 0,
        ];
    }
}

