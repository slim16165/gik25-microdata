<?php

namespace gik25microdata\site_specific;

use gik25microdata\ListOfBlocks\BlockBase;
use gik25microdata\ListOfPosts\Types\LinkBase;
use PHPUnit\Framework\TestCase;

require_smart('/../../../../../wp-load.php');

function require_smart($path): void
{
    $autoloadPath = realpath(__DIR__ . '' . $path . '');

    if ($autoloadPath === false)
    {
        exit('Failed to resolve path: ' . $path);
    }

    require_once $autoloadPath;
}


class Gik25BlockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }
    public function testBlockSaving()
    {
        // Imitazione della creazione di un link e salvataggio
        $link = new LinkBase('Test Title', 'https://example.com', 'Test Description');
        $link->Category = 'test_category';
        $link->SaveToDb();

        // Creazione di un blocco per il link
        $block = new BlockBase('link', $link->Id);

        // Simulazione del salvataggio del blocco nel database e verifica
        $blocks = [$block->toArray()];
        $blocksJson = json_encode($blocks);

        // Qui dovresti inserire la logica per salvare $blocksJson nel database
        // e poi recuperarlo per verificare che sia stato salvato correttamente.

        // Asserzioni per verificare che il JSON salvato corrisponda a quello atteso
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                [
                    'type' => 'link',
                    'id' => $link->Id,
                    'properties' => []
                ]
            ]),
            $blocksJson
        );
    }

    public function test_populate_sedi_inps_link_lists_table()
    {
        $_SERVER["SERVER_NAME"] = "www.superinformati.com";


        $startTime = microtime(true);

        require_smart('/../../vendor/autoload.php');

        require_once('superinformati_specific.php');

//      sedi_inps_handler(null);
//      link_diete_handler(null);
        link_tatuaggi_handler(null);
//      link_vitamine_handler(null);
//      link_dimagrimento_handler(null);
//      link_analisi_sangue_handler_2(null);

        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        echo "Execution time of the method: " . $executionTime . " seconds.";

        //truncate table oak_custom_links;
        //truncate table oak_custom_link_lists
    }


    protected function tearDown(): void
    {
        // Pulizia dopo il test, se necessario
        parent::tearDown();
    }
}