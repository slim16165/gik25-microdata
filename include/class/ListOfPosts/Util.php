<?php

class Util
{
    //Suddivide gli articoli in n colonne
    public static function PaginateArray(array $arr, int $pages): array
    {
        $item_count = count($arr);
        $n_item_per_chunk = ceil($item_count / $pages);
        return array_chunk($arr, $n_item_per_chunk);
    }
}