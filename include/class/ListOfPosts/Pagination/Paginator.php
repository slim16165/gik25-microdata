<?php
namespace gik25microdata\ListOfPosts\Pagination;

use Illuminate\Support\Collection;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sistema di paginazione per liste lunghe
 */
class Paginator
{
    /**
     * Pagina una collezione di link
     * 
     * @param Collection $links Collezione di link
     * @param int $page Pagina corrente (1-based)
     * @param int $perPage Elementi per pagina
     * @return array Dati paginati ['items' => Collection, 'pagination' => array]
     */
    public static function paginate(Collection $links, int $page = 1, int $perPage = 10): array
    {
        $total = $links->count();
        $totalPages = (int)ceil($total / $perPage);
        $page = max(1, min($page, $totalPages));
        
        $offset = ($page - 1) * $perPage;
        $items = $links->slice($offset, $perPage);
        
        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_prev' => $page > 1,
                'has_next' => $page < $totalPages,
                'prev_page' => $page > 1 ? $page - 1 : null,
                'next_page' => $page < $totalPages ? $page + 1 : null,
            ],
        ];
    }
    
    /**
     * Genera HTML per la paginazione
     * 
     * @param array $pagination Dati paginazione
     * @param string $baseUrl URL base per i link
     * @param string $pageParam Nome parametro pagina (default: 'page')
     * @return string HTML paginazione
     */
    public static function renderPagination(array $pagination, string $baseUrl = '', string $pageParam = 'page'): string
    {
        if ($pagination['total_pages'] <= 1) {
            return '';
        }
        
        $html = '<nav class="gik25-pagination" aria-label="Paginazione">';
        $html .= '<ul class="pagination-list">';
        
        // Link precedente
        if ($pagination['has_prev']) {
            $prevUrl = add_query_arg($pageParam, $pagination['prev_page'], $baseUrl);
            $html .= '<li class="pagination-item pagination-prev">';
            $html .= '<a href="' . esc_url($prevUrl) . '" aria-label="Pagina precedente">&laquo; Precedente</a>';
            $html .= '</li>';
        }
        
        // Numeri pagina
        $start = max(1, $pagination['current_page'] - 2);
        $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
        
        if ($start > 1) {
            $firstUrl = add_query_arg($pageParam, 1, $baseUrl);
            $html .= '<li class="pagination-item"><a href="' . esc_url($firstUrl) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="pagination-item pagination-ellipsis"><span>...</span></li>';
            }
        }
        
        for ($i = $start; $i <= $end; $i++) {
            $isCurrent = $i === $pagination['current_page'];
            $pageUrl = add_query_arg($pageParam, $i, $baseUrl);
            
            $html .= '<li class="pagination-item' . ($isCurrent ? ' pagination-current' : '') . '">';
            if ($isCurrent) {
                $html .= '<span aria-current="page">' . esc_html($i) . '</span>';
            } else {
                $html .= '<a href="' . esc_url($pageUrl) . '">' . esc_html($i) . '</a>';
            }
            $html .= '</li>';
        }
        
        if ($end < $pagination['total_pages']) {
            if ($end < $pagination['total_pages'] - 1) {
                $html .= '<li class="pagination-item pagination-ellipsis"><span>...</span></li>';
            }
            $lastUrl = add_query_arg($pageParam, $pagination['total_pages'], $baseUrl);
            $html .= '<li class="pagination-item"><a href="' . esc_url($lastUrl) . '">' . esc_html($pagination['total_pages']) . '</a></li>';
        }
        
        // Link successivo
        if ($pagination['has_next']) {
            $nextUrl = add_query_arg($pageParam, $pagination['next_page'], $baseUrl);
            $html .= '<li class="pagination-item pagination-next">';
            $html .= '<a href="' . esc_url($nextUrl) . '" aria-label="Pagina successiva">Successivo &raquo;</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
}
