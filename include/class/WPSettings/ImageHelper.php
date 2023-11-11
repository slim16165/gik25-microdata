<?php

namespace gik25microdata\WPSettings;

class ImageHelper{
    public function __construct()
    {
        // add_action('after_setup_theme', 'wnd_default_image_settings');
    }

    //Change the default image settings in the Backend
    function wnd_default_image_settings(): void
    {
        // update_option('image_default_align', 'left');
        update_option('image_default_align', 'right');
        update_option('image_default_link_type', 'none');
        update_option('image_default_size', 'full-size');
    }

    function theme_gallery_defaults( $settings ): array
    {
        $settings['galleryDefaults']['columns'] = 1;
        $settings['galleryDefaults']['link'] = 'none';
        $settings['galleryDefaults']['size'] = 'full-size';
        return $settings;
    }
}