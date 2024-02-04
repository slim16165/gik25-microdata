<?php

namespace gik25microdata\ListOfBlocks;

class BlockBase
{
    public string $type;
    public ?int $id = null;
    public ?object $data = null;

    /**
     * Costruttore flessibile che permette di creare un blocco specificando diversi set di parametri.
     *
     * @param string $type Tipo di blocco.
     * @param int|null $id Identificativo del blocco, se presente.
     * @param object|null $data Dati del blocco, se presenti.
     */
    public function __construct(string $type, $idOrData = null)
    {
        $this->type = $type;

        // Gestisce sia l'id che i dati in base al tipo di argomento passato
        if (is_int($idOrData)) {
            $this->id = $idOrData;
        } elseif (is_object($idOrData)) {
            $this->data = $idOrData;
        }
    }

    /**
     * Crea una rappresentazione array del blocco, adatta per la conversione in JSON.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'properties' => (array) $this->data,
        ];
    }

    /**
     * Metodo statico di fabbrica per creare un blocco con dati.
     *
     * @param string $type Tipo di blocco.
     * @param object $data Dati del blocco.
     * @return self
     */
    public static function withData(string $type, object $data): self
    {
        $block = new self($type);
        $block->data = $data;
        return $block;
    }

    /**
     * Metodo statico di fabbrica per creare un blocco con un ID.
     *
     * @param string $type Tipo di blocco.
     * @param int $id ID del blocco.
     * @return self
     */
    public static function withId(string $type, int $id): self
    {
        return new self($type, $id);
    }
}
