<?php ?>
    /**
    * Created by PhpStorm.
    * User: g.salvi
    * Date: 17/09/2019
    * Time: 11:37
    */


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
                foreach ($rand_posts as $post) :
                    setup_postdata($post);
                    ?>


                    <div class="swiper-slide d-block py-1 mb-4 mb-sm-0" itemscope
                         itemtype="http://schema.org/CreativeWork">
                        <div class="cover">
                            <a href="<?php the_permalink(); ?>" class="">

                                <img itemprop="image" alt="<?php the_title(); ?>" title="<?php the_title(); ?>"
                                     class="aligncenter shrink" src="<?php the_field('immagine_colore'); ?>">
                            </a>
                            <h5 itemprop="headline" class="h3 font-weight-bold pt-4 pb-4">
                                <a class="text-dark" title="<?php the_title(); ?>" href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h5>

                        </div>
                    </div>


                <?php endforeach;
                wp_reset_postdata();
            } ?>
        </div>

    </div>

<?php

public class ColorWidget
{
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



