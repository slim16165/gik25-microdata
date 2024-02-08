<?php

namespace gik25microdata\Utility;

class ImageDetails
{
    public static string $baseUrl;
    public static string $baseAbsolutePath;

    // Static property to store base URL and path of images
    public string $filename;
    public string $folder;

    public function __construct(string $folder, string $filename)
    {
        $this->filename = $filename;
        $this->folder = $folder;
    }

    public static function createFromUrl(string $imageUrl): self
    {
        $uploads = wp_upload_dir();
        self::$baseUrl = $uploads['baseurl'];
        self::$baseAbsolutePath = $uploads['basedir'];

        // Se l'URL dell'immagine non inizia con l'URL base degli upload, prova a rimuovere "wp-content/uploads/" dai percorsi base.
        if (!str_starts_with($imageUrl, self::$baseUrl))
        {
	    //TODO: controllare che vada
            return wp_error(); // Se l'URL non appartiene al tuo dominio, restituisci lo stesso URL.
        }

        // Use WordPress function to parse URL and extract components
        $parsed_url = wp_parse_url($imageUrl);

        // Check if the 'path' component exists in the parsed URL
//        if (!empty($parsed_url['path'])) {
        // Normalize the path to remove leading or trailing slashes
        $path = trim($parsed_url['path'], '/');

        $path = str_replace("wp-content/uploads/", "", $path);

        $path2 = pathinfo($path);
        $folder = $path2['dirname'];
        $filename = $path2['basename'];


        // Construct the full path
        $fullPath = self::$baseAbsolutePath . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $filename;

        return new self($folder, $filename);
    }

    public static function createFromPath(string $imagePath): self
    {
	//TODO: non funziona
        $uploads = wp_upload_dir();
        $baseAbsolutePath = $uploads['basedir'];

        // Ottieni il percorso assoluto della directory dell'immagine
        $imageDir = dirname($imagePath);

        // Calcola il percorso relativo rimuovendo il percorso della cartella di upload
        $relativePath = str_replace($baseAbsolutePath, '', $imageDir);

        // Normalizza il percorso per rimuovere le barre iniziali o finali
        $relativePath = trim($relativePath, '/');

        // Ottieni la directory e il nome del file
        $path_info = pathinfo($imagePath);
//        $folder = $path_info['dirname'];
        $filename = $path_info['basename'];

        return new self($relativePath, $filename);
    }

    public function getComputedUrl(): string
    {
        return self::$baseUrl . "/" . $this->folder . "/" . $this->filename;
    }

    public function stripImgSizeSuffix(): self
    {
        // Extract the filename without the "-50x50" suffix
        $new_filename = preg_replace('/-\d+x\d+(?=\.[a-z]{3,4}$)/i', '', $this->filename);
        return new self($this->folder, $new_filename);
    }

    /**
     * @param int $width
     * @param int $height
     * @return self
     */
    public function createThumbnailFilename(int $width, int $height): self
    {
        // Create the filename with dimensions specified
        $new_filename = preg_replace('/(?=\.[a-z]{3,4}$)/i', "-{$width}x{$height}", $this->filename);
        return new self($this->folder, $new_filename);
    }

    public function FileExists(): bool
    {
        return file_exists($this->getComputedPhisicalPath());
    }

    public function getComputedPhisicalPath(): string
    {
        return self::$baseAbsolutePath . DIRECTORY_SEPARATOR . $this->folder . DIRECTORY_SEPARATOR . $this->filename;
    }
}
