<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class QuestionSchema
{

    static function AddShortcode()
    {
        add_shortcode('domande_e_risposte', array(__CLASS__, 'domande_e_risposte_handler'), 8);
    }


    //Vanno evitati wptexturize() e wpautop()
    static function domande_e_risposte_handler($atts, $content = null)
    {
        //	add_filter('run_wptexturize', '__return_false');
        $result = "\n\n";
        $result .= "<!--adinj_exclude_start-->\n";
        $jsonIniziale = str_replace(
            array("<br />", "<p />", "<p>", "</p>"),
            array("", "", "", ""),
            $content);

        $jsonIniziale = html_entity_decode($jsonIniziale);

        $jsonUnparsed = str_replace(
            array("“", "”"),
            array("\"", "\""),
            $jsonIniziale
        );


        $jsonIniziale = <<<TAG
{
"domande": [
$jsonUnparsed
]}
TAG;

        $jsonDecoded = json_decode($jsonIniziale, true);
        $result .= CheckJsonError($jsonIniziale/*, $jsonUnparsed*/);

        //Parse Json e HTML
        $question_array_json = array();
        $question_array_html = array();

        foreach ($jsonDecoded as $domandeRisposte)
        {
            foreach ($domandeRisposte as $domandaRisposta)
            {
                if ($domandaRisposta["domanda"] == null || $domandaRisposta["risposta"] == null)
                {
                    $question_array_json[] = "ERRORE: Non ci possono essere domande o risposte vuote";
                    $question_array_html[] = "<span style='color: red; font-size: xx-large; font-weight: bold;'>ERRORE: Non ci possono essere domande o risposte vuote</span>";
                }

                $question_array_json[] = QuestionSchema::RenderJson($domandaRisposta["domanda"], $domandaRisposta["risposta"]);
                $question_array_html[] = QuestionSchema::RenderHTML($domandaRisposta["domanda"], $domandaRisposta["risposta"]);
            }
        }

        //Apertura Json
        $result .= <<<TAG
<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
TAG;

        $jsonDomande = implode(",\n", $question_array_json);
        $result .= $jsonDomande;


        //Chiusura Json
        $result .= <<<TAG
]} </script>

TAG;

        $result .= <<<TAG

<h3 id="DomandeERisposte">Domande frequenti</h3> 
<div class="schema-faq-section">

TAG;
        $htmlDomande = implode("", $question_array_html);
        $result .= $htmlDomande;

        $result .= "</div>\n";
        $result .= "<!--adinj_exclude_end-->\n\n\n";

        return $result;
    }


    static function RenderJson($question, $answer)
    {
        $question_parsed = htmlspecialchars($question);
        $answer_parsed = htmlspecialchars($answer);

        return <<<TAG
{
  "@type": "Question",
  "name": "$question_parsed",
  "acceptedAnswer": {
    "@type": "Answer",
    "text": "$answer_parsed"
  }
}
TAG;
    }

    static function RenderHTML($question, $answer)
    {
        $question_parsed = htmlspecialchars($question);
        $answer_parsed = htmlspecialchars($answer);

        return <<<TAG
<strong class="schema-faq- question">$question_parsed</strong>
<p class="schema-faq-answer">$answer_parsed</p>
TAG;
    }
}