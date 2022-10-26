<?php

use Illuminate\Support\Collection;
use include\class\ListOfPosts\Types\LinkBase;

class Util
{

    /**
     * @return Collection<LinkBase>
     */
    public static function ConvertArrayToCollectionOfLinks(array $links_data): Collection
    {
        $collection = new Collection();

        foreach ($links_data as $item)
        {
            if(MyString::IsNullOrEmptyString($item["target_url"]) ||
                MyString::IsNullOrEmptyString($item["nome"] ) ||
                $item["target_url"] == null
            )
                throwException("grrr");
            else
            {
                $link = new LinkBase($item["target_url"], $item["nome"]);
                $collection->add($link);
            }
        }
        return $collection;
    }

    //Suddivide gli articoli in n colonne
    /**
     * @param Collection<LinkBase> $arr
     * @param int $pages
     */
    public static function PaginateArray(Collection $arr, int $pages): array
    {
        $item_count = $arr->count();
        $n_item_per_chunk = ceil($item_count / $pages);
        $chunks = $arr->chunk($n_item_per_chunk);
        return $chunks->all();
    }
}