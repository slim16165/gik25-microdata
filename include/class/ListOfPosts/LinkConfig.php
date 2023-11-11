<?php

namespace gik25microdata\ListOfPosts;

class LinkConfig
{
    public int $nColumns;
    public bool $removeIfSelf;
    public bool $withImage;
    public bool $linkSelf;

    function __construct(bool $removeIfSelf, bool $withImage, bool $linkSelf, string $listOfPostsStyle = '')
    {
        $this->removeIfSelf = $removeIfSelf;
        $this->withImage = $withImage;
        $this->linkSelf = $linkSelf;

        if( is_int($listOfPostsStyle) )
            $this->nColumns = $listOfPostsStyle;
        else
            $this->nColumns = 1;
    }
}