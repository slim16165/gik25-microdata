<?php
if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

require_once '../../../vendor/autoload.php';
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Meta;
use YaLinqo\Enumerable as Linq;

class MultiColumnsLists extends ListOfPostsHelper
{
    function __construct($removeIfSelf, $withImage, $linkSelf, $nColumns)
    {
        parent::__construct($removeIfSelf, $withImage, $linkSelf, $nColumns);
    }

    public function GetLinksWithImagesByTag($tag)
    {
        $target_posts = PostData::GetPostsDataByTag($debugMsg, $tag);
        if ($debugMsg) return $debugMsg;

        return parent::GetLinksWithImages($target_posts);
    }

    //Multicolumns
    public function GetLinksWithImages(array $links_data) : string
    {
        $i = 0;
        $links_number = count($links_data);
        $n_links_per_column = ceil($links_number / self::$nColumns);
        $links_html = '';


        //attualmente è procedurale, fa un for, quando riempie la prima colonna si salva il risultato
        // in $links_html_col_1 e prosegue con i prossimi link, azzerando la variabile di appoggio $links_html che piazzerà in
        foreach ($links_data as $v)
        {
            $links_html .= $this->GetLinkWithImage1($v);

            if (self::$nColumns == 2)
            {
                $i++;

                if ($i == $n_links_per_column)
                {
                    //first col complete
                    $links_html_col_1 = "<ul>{$links_html}</ul>";
                    $links_html = '';
                }
            }
        }

//        //Implementazione con Yalinqo
//        $links_html_per_col = array();
//        for($col_ix = 0; $col_ix < self::$nColumns; $col_ix++)
//        {
//            //pagination
//            $linksToSkip = $n_links_per_column * $col_ix;
//            $links_col_x = Linq::from($links_data)->Skip($linksToSkip)->Take($n_links_per_column);
//
//            foreach ($links_col_x as $item)
//                $links_html_per_col[$col_ix].= $this->GetLinkWithImage1($item);
//        }

        //Suddivide gli articoli in n colonne
        $links_per_column_arr = array_chunk($links_data, $n_links_per_column);



        $cssDivClass = sprintf("list-of-posts-layout-%s", self::$nColumns);
        $links_html_per_col = array(); $col_ix = 0;
        foreach ($links_per_column_arr as $links_col_x)
        {
            $col_ix++;
            foreach ($links_col_x as $item)
                $links_html_per_col[$col_ix].= $this->GetLinkWithImage1($item);

            $links_html.= Html::div()->class($cssDivClass)->content($links_html_per_col)->close();
        }


        return $links_html;
    }
}