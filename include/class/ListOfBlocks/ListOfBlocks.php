<?php
declare(strict_types=1);
namespace gik25microdata\ListOfBlocks;
use Illuminate\Support\Collection;

class ListOfBlocks {
    private string $shortcode;
    private string $description;

    public Collection $blocks;

    public function __construct(string $shortcode, string $listDescription, $blocks) {
        $this->shortcode = $shortcode;
        $this->description = $listDescription;
        $this->blocks = $blocks;
    }

    public function SaveListOfBlocks(): void {
        global $wpdb;
        $lists_table_name = $wpdb->prefix . 'custom_link_lists';
        $blocks_json = json_encode($this->blocks);

        $wpdb->insert(
            $lists_table_name,
            [
                'shortcode_name' => $this->shortcode,
                'description' => $this->description,
                'links_json' => $blocks_json,
            ]
        );
    }
}
