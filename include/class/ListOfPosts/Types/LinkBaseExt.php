<?php

namespace gik25microdata\ListOfPosts\Types;

use WP_Post;

class LinkBaseExt extends LinkBase {
    public ?int $postId;
    public ?string $permalink;
    public ?WP_Post $post;
    public string $error;
    public bool $isSamepage;

    public function __construct(string $title, string $url, string $comment, ?int $postId, ?string $permalink, ?WP_Post $post, string $error, bool $isSamepage) {
        parent::__construct($url, $title, $comment);
        $this->postId = $postId;
        $this->permalink = $permalink;
        $this->post = $post;
        $this->error = $error;
        $this->isSamepage = $isSamepage;
    }
}
