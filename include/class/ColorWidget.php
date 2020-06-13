<?php

if ( ! defined( 'ABSPATH' ) ) {
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
div.contain {
    padding: 0 10px;
    margin: 0;
    font-family: 'Open Sans', sans-serif;
    box-sizing: border-box;
    min-height: 100vh;
    align-items: center;
    width: 100%;
}

div.contain a:link,
div.contain a:hover,
div.contain a:active,
div.contain a:visited {
    -webkit-transition: color 150ms;
    transition: color 150ms;
    color: #7a7a7a;
    text-decoration: none;
}

div.contain a:hover {
    color: #7f8c8d;
    text-decoration: underline;
}

.row {
    width: 100%;
}

.row__inner {
    -webkit-transition: 450ms -webkit-transform;
    transition: 450ms -webkit-transform;
    transition: 450ms transform;
    transition: 450ms transform, 450ms -webkit-transform;
    font-size: 0;
    margin: 0;
    padding-bottom: 10px;
}

.tile {
    position: relative;
    display: inline-block;
    width: 120px;
    height: 120px;
    margin-right: 2px;
    margin-top: 5px;
    /*font-size: 20px;*/
    cursor: pointer;
    -webkit-transition: 450ms all;
    transition: 450ms all;
    -webkit-transform-origin: center;
    transform-origin: center;    
}

.tile__img {
    width: 120px;
    height: 120px;
    -o-object-fit: cover;
    object-fit: cover;
}

.tile__details {
    position: absolute;
    bottom: -30px;
    left: 0;
    right: 0;
    top: 0;
    font-size: 12px;
    opacity: 1;
    -webkit-transition: 450ms opacity;
    transition: 450ms opacity;
    font-weight: 600;
}

.tile:hover .tile__details {
    opacity: 1;
}

.tile__title {
    position: absolute;    
    padding: 0px;
    width: 120px;
    line-height: 12px;    
    bottom: -3px;
    height: 25px;
}

.row__inner:hover .tile:hover {
    -webkit-transform: scale(1.5);
    transform: scale(1.5);
    opacity: 1;
}

.tile:hover ~ .tile {
    -webkit-transform: translate3d(13px, 0, 0);
    transform: translate3d(13px, 0, 0);
}

EOF;
        }

        public static function carousel_js()
        {
            ?>
            <link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/pure-min.css" integrity="sha384-cg6SkqEOCV1NbJoCu11+bm0NvBRc8IYLRGXkmNrqUBfTjmMYwNKPWBTIKyw9mHNJ" crossorigin="anonymous">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script>

                $(document).ready(function () {
                    $('.row__inner > .tile').hover(
                        function () {
                            $(this).siblings().css('opacity', '0.1');
                        },
                        function () {
                            $(this).siblings().css('opacity', '1');
                        }
                    );
                });
            </script>

            <?php
        }





		public static function defineHTML($post)
		{ ?>
            <div class="swiper-container">
                <div class="swiper-wrapper text-center">

					<?php
						$rand_posts = get_posts(array(
							'post_type' => 'progetto',
							'posts_per_page' => 7,
							'post__not_in' => array($post->ID),
							'order' => 'DESC'
						));

						if ($rand_posts)
						{
							foreach ($rand_posts as $post)
							{
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

    /**
     * @param $target_url
     * @param $nome
     * @param $featured_img_url
     * @return string
     */
    public static function GetLinkTemplateCarousel($target_url, $nome, $featured_img_url): string
    {
        $k = <<<EOF
	<div class="tile">
        <div class="tile__media">
            <a href="$target_url"><img class="tile__img" src="$featured_img_url" alt="$nome" /></a>
        </div>
        <div class="tile__details">
            <div class="tile__title">
                <a href="$target_url"> $nome</a>
            </div>
        </div>
    </div>	
EOF;

        return $k;
    }

    public static function GetLinkWithImageCarousel(string $target_url, string $nome)
    {
        $target_url = ReplaceTargetUrlIfStaging($target_url);
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
}

?>

