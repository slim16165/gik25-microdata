<?php

namespace gik25microdata\ListOfPosts;

use gik25microdata\ListOfPosts\Types\LinkBase;
use gik25microdata\Utility\HtmlHelper;
use gik25microdata\Utility\MyString;
use gik25microdata\Utility\ServerHelper;
use Illuminate\Support\Collection;
use WP_MatchesMapRegex;
use WP_Query;
use WP_Rewrite;

class WPPostsHelper
{
    const MY_DEBUG = true;

//    public static function GetPostData(string &$target_url, bool $removeIfSelf, $current_permalink): array
//    {
//        //se siamo su staging modifica il target_url
//        $target_url = self::ReplaceTargetUrlIfStaging($target_url);
//        //Check if the current post is the same of the target_url
//        $isSameFile = self::IsTargetUrlSamePost($target_url, $current_permalink);
//
//        //Find the post id from the url
//        $target_postid = self::url_to_postid_bulk($target_url);
//        $target_post = get_post($target_postid);
//        // Gestisci il caso in cui $post non sia un oggetto (cioè stiamo testando da PHPUnit)
//
//        if ($removeIfSelf && $isSameFile)
//        {
//            do_action('qm/debug', 'Stesso file e rimuovi link');
//        }
//
//        if ($target_postid == 0)
//        {
//            $debugMsg = self::HandleMsgTargetPostNotExist();
//        } else
//        {
//            $debugMsg = self::HandleMsgTargetPostNotPublished($target_post->post_status, $target_url);
//        }
//
//        return [$target_post, $isSameFile, $debugMsg];
//    }


    /**
     * Examines a URL and try to determine the post ID it represents.
     *
     * Checks are supposedly from the hosted site blog.
     *
     * @param string $url Permalink to check.
     * @return int Post ID, or 0 on failure.
     *
     * @global WP_Rewrite $wp_rewrite WordPress rewrite component.
     */
    private static function url_to_postid_bulk($urls) : array
    {
        //TODO: non funziona correttamente
        global $wp_rewrite;
        $results = [];
        $rewrite_urls = [];

        // Pre-process and check for direct IDs
        foreach ($urls as $url)
        {
            $preprocessed_url = self::pre_process_url($url);
            $direct_id = self::check_direct_id_from_url($preprocessed_url);
            if ($direct_id)
            {
                $results[$url] = $direct_id;
            } else
            {
                $rewrite_urls[$url] = $preprocessed_url;
            }
        }

        // Process with rewrite rules in bulk
        if (!empty($rewrite_urls))
        {
            $rewrite_rules = $wp_rewrite->wp_rewrite_rules();

            if (empty($rewrite_rules))
            {
                return 0; // No rewrite rules defined
            }

            $bulk_results = self::process_with_rewrite_rules_bulk($rewrite_urls, $rewrite_rules);
            $results = array_merge($results, $bulk_results);
        }

        return $results;
    }

    #region URL Parsing
    private static function pre_process_url($url)
    {
        $url = apply_filters('url_to_postid', $url);
        $url_host = str_replace('www.', '', parse_url($url, PHP_URL_HOST) ?: '');
        $home_url_host = str_replace('www.', '', parse_url(home_url(), PHP_URL_HOST) ?: '');

        // Bail early if the URL does not belong to this site.
        if ($url_host && $url_host !== $home_url_host)
        {
            return 0;
        }

        // Normalize the URL
        $url = self::remove_url_fragment($url);
        $url = self::remove_query_string($url);
        $url = self::set_url_scheme_to_match_home($url);

        return $url;
    }

    private static function remove_url_fragment($url) {
        // Split URL to remove fragment
        $url_split = explode('#', $url);
        return $url_split[0];
    }

    private static function remove_query_string($url) {
        // Split URL to remove query string
        $url_split = explode('?', $url);
        return $url_split[0];
    }

    private static function set_url_scheme_to_match_home($url) {
        // Set the correct URL scheme to match the home URL scheme
        $scheme = parse_url(home_url(), PHP_URL_SCHEME);
        $url = set_url_scheme($url, $scheme);

        // Add 'www.' if it is absent and should be there
        if (strpos(home_url(), '://www.') !== false && strpos($url, '://www.') === false) {
            $url = str_replace('://', '://www.', $url);
        }

        // Strip 'www.' if it is present and shouldn't be
        if (strpos(home_url(), '://www.') === false) {
            $url = str_replace('://www.', '://', $url);
        }

        return $url;
    }

    #endregion

    private static function check_direct_id_from_url($url): int
    {
        if (preg_match('#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values)) {
            return absint($values[2]);
        }
        return 0;
    }

    private static function process_with_rewrite_rules_bulk($urls, $rewrite_rules): array
    {
        $adjusted_urls = [];

        // Prima preparazione: adeguamento degli URL per le rewrite rules
        foreach ($urls as $original_url => $preprocessed_url)
        {
            $adjusted_urls[$original_url] = self::adjust_url_for_rewrite_rules($preprocessed_url);
        }

        // Inizializza l'array dei risultati
        $results = array_fill_keys(array_keys($urls), 0);

        // Matching Bulk: Processa tutti gli URL adeguati contro le rewrite rules
        foreach ($rewrite_rules as $match => $query)
        {
            $bulk_results = [];
            if (self::urls_match_rewrite_rule_bulk($adjusted_urls, $match, $query, $bulk_results))
            {
                // Aggiorna i risultati solo se abbiamo un match
                $results = array_merge($results, $bulk_results);
            }
        }

        return $results;
    }



    private static function adjust_url_for_rewrite_rules($url): string
    {
        global $wp_rewrite;

        // Strip 'index.php/' if we're not using path info permalinks
        if (!$wp_rewrite->using_index_permalinks())
        {
            $url = str_replace($wp_rewrite->index . '/', '', $url);
        }

        if (strpos(trailingslashit($url), home_url('/')) === 0)
        {
            // Chop off http://domain.com/[path]
            $url = str_replace(home_url(), '', $url);
        } else
        {
            // Chop off /path/to/blog
            $home_path = parse_url(home_url('/'), PHP_URL_PATH);
            $url = preg_replace('#^' . preg_quote($home_path, '#') . '#', '', trailingslashit($url));
        }

        // Trim leading and lagging slashes
        return trim($url, '/');
    }

    private static function urls_match_rewrite_rule_bulk($urls, $match, $input_query, &$results): bool
    {
        global $wp;
        $results = [];
        $post_names = [];

        foreach ($urls as $url) {
            $request_match = $url;
            if (preg_match("#^$match#", $request_match, $matches)) {
                $query_vars = self::process_and_parse_query($input_query, $matches);
                if (isset($query_vars['name'])) {
                    $post_names[] = $query_vars['name'];
                }
            }
        }

        if (!empty($post_names)) {
            $wp_query = new WP_Query([
                'post_type' => 'post',
                'name__in' => $post_names,
                'posts_per_page' => -1,
            ]);

            while ($wp_query->have_posts()) {
                $wp_query->the_post();
                $results[get_post_field('post_name')] = get_the_ID();
            }
            wp_reset_postdata();
        }

        return !empty($results);
    }


    private static function process_and_parse_query($input_query, $matches): array
    {
        // Rimuove la parte iniziale della query
        $processed_query = preg_replace('!^.+\?!', '', $input_query);
        // Sostituisce i segnaposto nella query con i valori corrispondenti
        $processed_query = addslashes(WP_MatchesMapRegex::apply($processed_query, $matches));
        // Analizza una stringa di query in un array
        parse_str($processed_query, $query_vars);

        return $query_vars;
    }


    /**
     * Check if the current executing post is the same of the target_url
     */
    public static function IsTargetUrlSamePost(string $target_url, $current_permalink): bool
    {
        return strcmp($current_permalink, $target_url) == 0;
    }

    /**
     * The provided url should be an article of this WordPress installation. This method is used to test on staging environments
     * @param Collection<LinkBase> $links_col_x
     * @return Collection
     */
    public static function ReplaceTargetUrlIfStagingBulk(Collection $links): Collection
    {
        $domain = ServerHelper::getDomain();
        $executingOnStaging = MyString::Contains($domain, "cloudwaysapps.com") || MyString::Contains($domain, ".local");

        $links = $links->map(function (LinkBase $link) use ($domain, $executingOnStaging) {
            $target_url = $link->Url;
            $linkingToStaging = MyString::Contains($target_url, "cloudwaysapps.com") || MyString::Contains($target_url, ".local");

            if ($executingOnStaging && !$linkingToStaging) {
                // Solo se sono un server di staging ma l'url lincato non ne tiene conto, ho un problema
                $link->Url = HtmlHelper::ReplaceDomain($target_url, $domain);
            }
            return $link;
        });

        return $links;
    }




    public static function HandleMsgTargetPostNotExist(): string
    {
        $msg = "This post does not exist, or it is on other domain, or URL is wrong (target_postid == 0)";
        $debugMsg = WPPostsHelper::MY_DEBUG ? "<h5 style=\"color: red;\">$msg</h5>" : "";
        do_action('qm/warning', $msg);
        return $debugMsg;
    }

    public static function HandleMsgTargetPostNotPublished($target_post_status, string $target_url): string
    {
        $debugMsg = "";
        if ($target_post_status !== "publish")
        {
            $debugMsg = "NON PUBBLICATO: " . $target_url;
            do_action('qm/debug', $debugMsg);
        }
        return $debugMsg;
    }

    public static function GetBulkPostDataCached(array $target_urls): array
    {
        // Genera una chiave univoca per la cache basata sugli URL di destinazione
        $transient_key = 'bulk_post_data_' . md5(implode(',', $target_urls));

        // Prova a ottenere i dati dal transient
        $url_to_post = get_transient($transient_key);

        // Se i dati non sono nel transient, ottienili e memorizzali nel transient
        if ($url_to_post === false)
        {
            $url_to_post = self::GetBulkPostData($target_urls);
            set_transient($transient_key, $url_to_post, WEEK_IN_SECONDS);
        }
        else
        {
            do_action('qm/debug', 'Dati trovati in cache');
        }

        return $url_to_post;
    }


    private static function GetBulkPostData(array $target_urls): array
    {
        $url_to_post = [];

        foreach ($target_urls as $target_url)
        {
            // Raccoglie l'ID del post dall'URL
            $post_id = url_to_postid($target_url);

            // Se l'ID del post è valido, carica il post
            if ($post_id > 0)
            {
                $post = get_post($post_id);

                // Se il post è valido, aggiungilo all'array
                if (!is_null($post))
                {
                    $url_to_post[$target_url] = [
                        'id' => $post_id,
                        'permalink' => get_permalink($post),
                        'post' => $post
                    ];
                }
                else
                {
                    do_action('qm/debug', "Nessun post valido trovato per l'URL: $target_url");
                    $url_to_post[$target_url] = [
                        'id' => null,
                        'permalink' => null,
                        'post' => null,
                        'error' => "Nessun post valido trovato per l'URL: $target_url"
                    ];
                }
            }
            else
            {
                do_action('qm/debug', "Nessun ID valido trovato per l'URL: $target_url");
                $url_to_post[$target_url] = [
                    'id' => null,
                    'permalink' => null,
                    'post' => null,
                    'error' => "Nessun ID valido trovato per l'URL: $target_url"
                ];
            }
        }

        return $url_to_post;
    }

    public static function getDebugMsg(bool $removeIfSelf, bool $isSameFile, $post, $url): string
    {
        $debugMsg = '';
        if ($removeIfSelf && $isSameFile)
        {
            $debugMsg = 'Stesso file e rimuovi link';
            do_action('qm/debug', $debugMsg);
        } else if (!$post)
        {
            $debugMsg = "Post non trovato o non pubblicato per URL: $url";
            do_action('qm/warning', $debugMsg);
        } else if ($post->post_status !== 'publish')
        {
            $debugMsg = "Post non pubblicato: " . get_permalink($post->ID);
            do_action('qm/info', $debugMsg);
        }
        return $debugMsg;
    }

    public static function GetCurrentPostPermalink() : string
    {
        // Ottiene l'ID del post corrente
        $current_post_id = get_the_ID();

        // Calcola il permalink del post corrente
        $current_permalink = get_permalink($current_post_id);

        // Assicurati che $current_permalink sia valido
        if (false === $current_permalink) {
            // Gestisci l'errore o imposta un valore predefinito
            $current_permalink = home_url(); // fallback all'homepage se il permalink non è valido
        }
        return $current_permalink;
    }
}