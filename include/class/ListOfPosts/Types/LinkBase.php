<?php

namespace gik25microdata\ListOfPosts\Types;

class LinkBase
{
    public ?int $Id = null; // Aggiunta di una proprietà ID
    public string $Title;
    public string $Url;
    public string $Comment;
    public string $Category;

    public function __construct(string $Url, string $Title, string $Comment = '')
    {
        $this->Title = $Title;
        $this->Url = $Url;
        $this->Comment = $Comment;
    }

    /**
     * Salva o aggiorna l'oggetto LinkBase nel database e aggiorna la proprietà Id con l'ID generato.
     */
    public function SaveToDb()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_links';

        $data = [
            'link_url' => $this->Url,
            'link_name' => $this->Title,
            'link_description' => $this->Comment,
            'link_category' => $this->Category,
        ];

        if ($this->Id === null) {
            // Inserimento
            $wpdb->insert($table_name, $data);
            $this->Id = $wpdb->insert_id;
        } else {
            // Aggiornamento
            $wpdb->update($table_name, $data, ['id' => $this->Id]);
        }
    }

    /**
     * Carica i dettagli di un LinkBase esistente dal database utilizzando l'ID.
     *
     * @param int $id ID del link da caricare.
     */
    public static function Load($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_links';

        $link = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

        if ($link) {
            $instance = new self($link->link_url, $link->link_name);
            $instance->Id = (int) $link->id;
            $instance->Comment = $link->link_description;
            $instance->Category = $link->link_category;

            return $instance;
        }

        return null;
    }
}
