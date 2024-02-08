<?php

namespace gik25microdata\Utility;

class ImageHelper
{
    public static function getOrCreateCustomThumb(ImageDetails $featured_img): ImageDetails
    {
        $small_thumbnail = self::getSmallThumbnailName($featured_img);

        if ($small_thumbnail->FileExists())
        {
            // Se esiste, usa l'immagine thumbnail piccolina
            $featured_img = $small_thumbnail;
        } else
        {
            //creala
            $featured_img = self::create_small_thumbnail($featured_img);
        }
        return $featured_img;
    }

    public static function getSmallThumbnailName(ImageDetails $imageDetails): ImageDetails
    {
        // Get the original filename without the "-50x50" suffix
        $img = $imageDetails
            ->stripImgSizeSuffix()
            ->createThumbnailFilename(50, 50);

        return $img;
    }

    public static function create_small_thumbnail(ImageDetails $img): ImageDetails
    {
        // Carica l'immagine
        $image_editor = wp_get_image_editor($img->getComputedPhisicalPath());

        if (!is_wp_error($image_editor))
        {
            // Ridimensiona l'immagine a 50x50 pixel
            $image_editor->resize(50, 50, true);

            // Crea il nuovo nome del file
            $newImageName = self::getSmallThumbnailName($img);

            // Salva l'immagine ridimensionata
            $saved_image = $image_editor->save($newImageName->getComputedPhisicalPath());

            // Restituisci il percorso dell'immagine ridimensionata
            if (!is_wp_error($saved_image))
                $img = $newImageName;
        }
        return $img;
    }
}