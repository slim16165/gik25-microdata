<?php

namespace ListOfPosts;

class LinkConfig
{
    public int $nColumns;
    public bool $removeIfSelf;
    public bool $withImage;
    public bool $linkSelf;

    function __construct($removeIfSelf, $withImage, $linkSelf, $listOfPostsStyle = '')
    {
        $this->removeIfSelf = $removeIfSelf;
        $this->withImage = $withImage;
        $this->linkSelf = $linkSelf;
        $this->nColumns = $listOfPostsStyle;
    }
}