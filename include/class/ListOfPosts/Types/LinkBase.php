<?php

namespace include\class\ListOfPosts\Types;

class LinkBase
{
    public string $Title;
    public string $Url;
    public string $Comment;

    public function __construct(string $Title, string $Url, string $Comment = '')
    {
        $this->Title = $Title;
        $this->Url = $Url;
        $this->Comment = $Comment;
    }
}