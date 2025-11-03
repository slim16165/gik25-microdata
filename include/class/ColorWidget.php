<?php
 namespace gik25microdata;

use gik25microdata\ListOfPosts\WPPostsHelper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ColorWidget
{
    public static function Initialize()
    {
        add_action('wp_head', array(__CLASS__, 'carousel_js'));
    }

    public static function get_carousel_css()
    {
        return /** @lang CSS */
            <<<EOF
/* Mobile First - Base Styles */
div.contain {
    padding: 0;
    margin: 0;
    font-family: 'Open Sans', sans-serif;
    box-sizing: border-box;
    width: 100%;
}

div.contain a:link,
div.contain a:hover,
div.contain a:active,
div.contain a:visited {
    transition: color 150ms ease;
    color: #7a7a7a;
    text-decoration: none;
}

div.contain a:hover {
    color: #7f8c8d;
    text-decoration: underline;
}

.row {
    position: relative;
    width: 100%;
    overflow: visible;
}

/* Rimuove le frecce - non servono con layout a griglia */

.row__inner {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
    margin: 0;
    padding: 10px 0 20px 0;
    font-size: 0;
    justify-content: flex-start;
}

@media (min-width: 768px) {
    .row__inner {
        gap: 2px;
        padding: 10px 0 30px 0;
    }
}

/* Tile Base - Dimensioni originali */
.tile {
    position: relative;
    display: inline-block;
    flex: 0 0 auto;
    width: 120px;
    height: 120px;
    margin: 0;
    cursor: pointer;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                opacity 0.3s ease,
                z-index 0s linear 0.3s;
    transform-origin: center center;
    will-change: transform;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    z-index: 1;
    border-radius: 4px;
    overflow: hidden;
}

.tile__link {
    width: 100%;
    height: 100%;
    display: block;
    text-decoration: none;
    color: inherit;
    position: relative;
}

.tile__img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    display: block;
    pointer-events: none;
    transition: transform 0.3s ease;
}

.tile:hover .tile__img,
.tile.active .tile__img {
    transform: scale(1.05);
}

.tile__details {
    position: absolute;
    bottom: -35px;
    left: 0;
    right: 0;
    font-size: 11px;
    opacity: 1;
    transition: all 0.3s ease;
    font-weight: 600;
    pointer-events: none;
    padding: 4px;
    text-align: center;
}

.tile:hover .tile__details,
.tile.active .tile__details {
    position: absolute;
    bottom: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.75) 0%, rgba(0, 0, 0, 0.5) 60%, transparent 100%);
    padding: 20px 4px 6px 4px;
}

.tile__title {
    position: relative;
    padding: 0px;
    width: 120px;
    line-height: 12px;
    height: 25px;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #fff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    bottom: -3px;
}

/* Hover States - Desktop Only */
@media (hover: hover) and (pointer: fine) {
    .row__inner:hover .tile {
        opacity: 0.6;
    }
    
    .row__inner:hover .tile:hover {
        opacity: 1;
        transform: scale(1.2);
        z-index: 10;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                    opacity 0.3s ease,
                    z-index 0s linear 0s;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }
    
    /* Non sposta più i tile - layout a griglia */
}

/* Touch States - Mobile */
@media (hover: none) and (pointer: coarse) {
    .tile:active {
        transform: scale(1.05);
        transition: transform 0.2s ease;
    }
    
    .tile.active {
        transform: scale(1.15);
        z-index: 10;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
}

/* Tablet and Up */
@media (min-width: 768px) {
    .tile__details {
        font-size: 12px;
    }
    
    .tile__title {
        font-size: 12px;
    }
    
    @media (hover: hover) and (pointer: fine) {
        .row__inner:hover .tile:hover {
            transform: scale(1.2);
        }
        
        .row__inner:hover .tile:hover ~ .tile {
            transform: translateX(30px);
        }
    }
}

/* Desktop */
@media (min-width: 1024px) {
    .row__inner {
        gap: 2px;
        padding: 10px 0 30px 0;
    }
    
    .tile__details {
        font-size: 12px;
        padding: 25px 4px 6px 4px;
    }
    
    .tile__title {
        font-size: 12px;
        width: 120px;
    }
    
    @media (hover: hover) and (pointer: fine) {
        .row__inner:hover .tile:hover {
            transform: scale(1.2);
        }
        
    }
}

/* Tablet and Up */
@media (min-width: 768px) {
    .row__inner:hover .tile:hover ~ .tile {
        /* Non sposta più i tile - layout a griglia */
    }
}

/* Accessibility - Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    .tile,
    .tile__details,
    .row__inner:hover .tile,
    .row__inner:hover .tile:hover {
        transition: none;
        transform: none !important;
    }
}

EOF;
    }

    public static function carousel_js()
    {
        ?>
        <link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/pure-min.css"
              integrity="sha384-cg6SkqEOCV1NbJoCu11+bm0NvBRc8IYLRGXkmNrqUBfTjmMYwNKPWBTIKyw9mHNJ"
              crossorigin="anonymous">
        <script>
            (function() {
                'use strict';
                
                // Verifica se jQuery è già caricato (per compatibilità)
                var hasJQuery = typeof jQuery !== 'undefined';
                
                // Funzione per gestire il supporto touch su mobile
                function initTouchSupport() {
                    // Rileva dispositivi touch
                    var isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
                    
                    if (!isTouchDevice) {
                        return; // Desktop - usa solo CSS hover
                    }
                    
                    // Mobile/Touch - aggiungi gestione tap
                    var tiles = document.querySelectorAll('.tile');
                    var activeTile = null;
                    
                    function handleTileTap(tile) {
                        // Rimuovi classe active da tutti i tiles
                        tiles.forEach(function(t) {
                            t.classList.remove('active');
                        });
                        
                        // Aggiungi classe active al tile cliccato
                        if (tile === activeTile) {
                            // Secondo tap - chiudi
                            activeTile = null;
                        } else {
                            // Primo tap o nuovo tile - apri
                            tile.classList.add('active');
                            activeTile = tile;
                            
                            // Scrolla il tile in vista se necessario
                            setTimeout(function() {
                                tile.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'nearest',
                                    inline: 'center'
                                });
                            }, 100);
                        }
                    }
                    
                    // Gestione eventi touch e click
                    tiles.forEach(function(tile) {
                        var link = tile.querySelector('.tile__link');
                        
                        // Preveniamo la navigazione al primo tap su mobile
                        link.addEventListener('click', function(e) {
                            // Se il tile non è attivo, preveniamo la navigazione e zoomiamo
                            if (!tile.classList.contains('active')) {
                                e.preventDefault();
                                e.stopPropagation();
                                handleTileTap(tile);
                                return false;
                            }
                            // Se il tile è già attivo, permetti la navigazione normale
                            return true;
                        }, { passive: false });
                        
                        // Gestione touch per zoom immediato
                        tile.addEventListener('touchend', function(e) {
                            if (!tile.classList.contains('active')) {
                                e.preventDefault();
                                e.stopPropagation();
                                handleTileTap(tile);
                            }
                        }, { passive: false });
                    });
                    
                    // Chiudi il tile attivo se si clicca fuori
                    document.addEventListener('click', function(e) {
                        if (activeTile && !activeTile.contains(e.target)) {
                            activeTile.classList.remove('active');
                            activeTile = null;
                        }
                    });
                }
                
                // Inizializza quando il DOM è pronto
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initTouchSupport);
                } else {
                    initTouchSupport();
                }
                
                // Smooth scroll polyfill per browser più vecchi (opzionale)
                if (!Element.prototype.scrollIntoView) {
                    Element.prototype.scrollIntoView = function(options) {
                        var element = this;
                        var parent = element.parentElement;
                        if (parent) {
                            var x = element.offsetLeft - (parent.offsetWidth / 2) + (element.offsetWidth / 2);
                            parent.scrollLeft = x;
                        }
                    };
                }
                
                // Performance: Lazy load delle immagini se supportato
                if ('loading' in HTMLImageElement.prototype) {
                    var images = document.querySelectorAll('.tile__img[data-src]');
                    images.forEach(function(img) {
                        img.src = img.dataset.src;
                    });
                }
                
            })();
        </script>

        <?php
    }


    public static function GetLinkTemplateCarousel($target_url, $nome, $featured_img_url): string
    {
        // Sanitizza i dati per sicurezza
        $safe_url = esc_url($target_url);
        $safe_nome = esc_html($nome);
        $safe_img_url = esc_url($featured_img_url);
        
        $k = <<<EOF
	<div class="tile" role="button" tabindex="0" aria-label="Vai a $safe_nome">        
        <a href="$safe_url" class="tile__link" aria-label="$safe_nome">
            <div class="tile__media">
                <img class="tile__img" src="$safe_img_url" alt="$safe_nome" loading="lazy" />
            </div>        
            <div class="tile__details">
                <div class="tile__title">
                    $safe_nome
                </div>
            </div>
        </a>        
    </div>    	
EOF;

        return $k;
    }

    public static function GetLinkWithImageCarousel(string $target_url, string $nome)
    {
        $target_url = WPPostsHelper::ReplaceTargetUrlIfStaging($target_url);
        global $post, $MY_DEBUG; //il post corrente
        $result = "";

        $target_postid = url_to_postid($target_url);

        if ($target_postid == 0) {
            if ($MY_DEBUG)
                return "target_postid == 0";
            else
                return "";
        }

        $target_post = get_post($target_postid);

        if ($target_post->post_status === "publish") {
            $featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');
            $result = ColorWidget::GetLinkTemplateCarousel($target_url, $nome, $featured_img_url);
        } else {
            if ($MY_DEBUG)
                $result .= "NON PUBBLICATO: $target_url";
            else
                $result .= "<!-- NON PUBBLICATO -->";
        }

        return $result;
    }


    public static function defineHTML($post)
    {?>
        <div class="swiper-container">
            <div class="swiper-wrapper text-center">

                <?php
                $rand_posts = get_posts(array(
                    'post_type' => 'progetto',
                    'posts_per_page' => 7,
                    'post__not_in' => array($post->ID),
                    'order' => 'DESC'
                ));

                if ($rand_posts) {
                    foreach ($rand_posts as $post) {
                        setup_postdata($post);
                        ?>


                        <div class="swiper-slide d-block py-1 mb-4 mb-sm-0" itemscope
                             itemtype="http://schema.org/CreativeWork">
                            <div class="cover">
                                <a href="<?php the_permalink(); ?>" class="">

                                    <img itemprop="image" alt="<?php the_title(); ?>"
                                         title="<?php the_title(); ?>"
                                         class="aligncenter shrink"
                                         src="<?php the_field('immagine_colore'); ?>">
                                </a>
                                <h5 itemprop="headline" class="h3 font-weight-bold pt-4 pb-4">
                                    <a class="text-dark" title="<?php the_title(); ?>"
                                       href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h5>

                            </div>
                        </div>


                        <?php
                    }
                    wp_reset_postdata();
                } ?>
            </div>
        </div>

        <?php
    }

    public static function defineJS()
    {
        $js = /** @lang javascript */
            <<<TAG
<!-- Initialize Swiper -->
<script>
    var swiper = new Swiper('.swiper-container', {
        autoplay: {
            delay: 6500,
        },
        effect: 'coverflow',
        centeredSlides: true,
        slidesPerView: '3',
        coverflowEffect: {
            rotate: 50,
            stretch: 0,
            depth: 100,
            modifier: 1,
            slideShadows: false,
        },
        grabCursor: true,
        loop: true,
        breakpoints: {
            1024: {
                slidesPerView: 3,
                spaceBetween: 30,
            },
            768: {
                slidesPerView: 1,
                spaceBetween: 0,
            },
            640: {
                slidesPerView: 1,
                spaceBetween: 0,
            },
            320: {
                slidesPerView: 1,
                spaceBetween: 0,
            }
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
</script>
TAG;

    }
}
?>