<?php
namespace gik25microdata\ListOfPosts\REST;

use gik25microdata\ListOfPosts\LinkBuilder;
use gik25microdata\ListOfPosts\Validation\UrlValidator;
use gik25microdata\ListOfPosts\Cache\LinkCache;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * API REST per gestione e query link
 */
class LinkApiController extends WP_REST_Controller
{
    protected $namespace = 'gik25/v1';
    protected $rest_base = 'links';
    
    public function __construct()
    {
        $this->namespace = 'gik25/v1';
        $this->rest_base = 'links';
    }
    
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args' => $this->get_collection_params(),
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_item'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
                'args' => $this->get_endpoint_args_for_item_schema(true),
            ],
        ]);
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_item'],
                'permission_callback' => [$this, 'update_item_permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_item'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
            ],
        ]);
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/validate', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'validate_urls'],
                'permission_callback' => [$this, 'validate_permissions_check'],
            ],
        ]);
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/render', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'render_links'],
                'permission_callback' => [$this, 'render_permissions_check'],
            ],
        ]);
    }
    
    public function get_items_permissions_check($request)
    {
        return true; // Pubblico
    }
    
    public function get_items($request)
    {
        $urls = $request->get_param('urls');
        $style = $request->get_param('style') ?? 'standard';
        
        if (empty($urls) || !is_array($urls)) {
            return new WP_Error('invalid_urls', 'URLs array required', ['status' => 400]);
        }
        
        $links = [];
        foreach ($urls as $url_data) {
            if (is_string($url_data)) {
                $url = $url_data;
                $title = '';
                $comment = '';
            } else {
                $url = $url_data['url'] ?? '';
                $title = $url_data['title'] ?? '';
                $comment = $url_data['comment'] ?? '';
            }
            
            // Verifica cache
            $cached = LinkCache::get($url);
            if ($cached) {
                $links[] = [
                    'url' => $cached->Url,
                    'title' => $cached->Title,
                    'comment' => $cached->Comment,
                ];
            } else {
                $validation = UrlValidator::validate($url);
                if ($validation['valid']) {
                    $links[] = [
                        'url' => $url,
                        'title' => $title,
                        'comment' => $comment,
                        'valid' => true,
                    ];
                }
            }
        }
        
        return new WP_REST_Response($links, 200);
    }
    
    public function create_item_permissions_check($request)
    {
        return current_user_can('edit_posts');
    }
    
    public function create_item($request)
    {
        $url = $request->get_param('url');
        $title = $request->get_param('title');
        $comment = $request->get_param('comment') ?? '';
        
        if (empty($url) || empty($title)) {
            return new WP_Error('missing_params', 'URL and title required', ['status' => 400]);
        }
        
        $validation = UrlValidator::validate($url);
        if (!$validation['valid']) {
            return new WP_Error('invalid_url', 'URL validation failed', [
                'status' => 400,
                'errors' => $validation['errors'],
            ]);
        }
        
        // Qui si potrebbe salvare in un custom post type o opzione
        // Per ora restituiamo solo la validazione
        
        return new WP_REST_Response([
            'url' => $url,
            'title' => $title,
            'comment' => $comment,
            'valid' => true,
        ], 201);
    }
    
    public function get_item_permissions_check($request)
    {
        return true;
    }
    
    public function get_item($request)
    {
        $id = (int)$request->get_param('id');
        $post = get_post($id);
        
        if (!$post) {
            return new WP_Error('post_not_found', 'Post not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'id' => $post->ID,
            'title' => $post->post_title,
            'url' => get_permalink($post->ID),
            'status' => $post->post_status,
        ], 200);
    }
    
    public function update_item_permissions_check($request)
    {
        return current_user_can('edit_posts');
    }
    
    public function update_item($request)
    {
        // Implementazione update
        return new WP_REST_Response(['message' => 'Updated'], 200);
    }
    
    public function delete_item_permissions_check($request)
    {
        return current_user_can('delete_posts');
    }
    
    public function delete_item($request)
    {
        // Implementazione delete
        return new WP_REST_Response(['message' => 'Deleted'], 200);
    }
    
    public function validate_permissions_check($request)
    {
        return current_user_can('edit_posts');
    }
    
    public function validate_urls($request)
    {
        $urls = $request->get_param('urls');
        if (!is_array($urls)) {
            return new WP_Error('invalid_urls', 'URLs array required', ['status' => 400]);
        }
        
        $results = UrlValidator::validateBatch($urls);
        return new WP_REST_Response($results, 200);
    }
    
    public function render_permissions_check($request)
    {
        return true; // Pubblico
    }
    
    public function render_links($request)
    {
        $links = $request->get_param('links');
        $style = $request->get_param('style') ?? 'standard';
        $options = $request->get_param('options') ?? [];
        
        if (!is_array($links)) {
            return new WP_Error('invalid_links', 'Links array required', ['status' => 400]);
        }
        
        $builder = new LinkBuilder($style, $options);
        $html = $builder->createLinksFromArray($links);
        
        return new WP_REST_Response([
            'html' => $html,
            'style' => $style,
        ], 200);
    }
    
    public function get_collection_params()
    {
        return [
            'urls' => [
                'description' => 'Array of URLs to query',
                'type' => 'array',
                'default' => [],
            ],
            'style' => [
                'description' => 'Rendering style',
                'type' => 'string',
                'enum' => ['standard', 'carousel', 'simple'],
                'default' => 'standard',
            ],
        ];
    }
    
    public function get_endpoint_args_for_item_schema($method = WP_REST_Server::CREATABLE)
    {
        $args = [];
        
        $args['url'] = [
            'description' => 'URL of the link',
            'type' => 'string',
            'required' => true,
            'format' => 'uri',
        ];
        
        $args['title'] = [
            'description' => 'Title of the link',
            'type' => 'string',
            'required' => true,
        ];
        
        $args['comment'] = [
            'description' => 'Optional comment',
            'type' => 'string',
        ];
        
        return $args;
    }
}
