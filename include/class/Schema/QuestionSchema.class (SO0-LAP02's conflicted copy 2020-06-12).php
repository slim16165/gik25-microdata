<?php
	/**
	 * Created by PhpStorm.
	 * User: g.salvi
	 * Date: 21/10/2019
	 * Time: 12:14
	 */

	class QuestionSchema
	{
		//Vanno evitati wptexturize() e wpautop()
		public static function domande_e_risposte_handler($atts, $content = null)
		{
			//	add_filter('run_wptexturize', '__return_false');
			$result = "\n\n";
			$result.= "<!--adinj_exclude_start-->\n";
			$jsonIniziale = str_replace(
				array("<br />", "<p />", "<p>", "</p>"),
				array("", "", "", ""),
				$content);

			$jsonIniziale = html_entity_decode($jsonIniziale);

			$jsonUnparsed = str_replace(
				array("“","”"),
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
			$result.= CheckJsonError($jsonIniziale/*, $jsonUnparsed*/);

			//Parse Json e HTML
			$question_array_json = array();
			$question_array_html = array();
			foreach($jsonDecoded as $domandeRisposte)
			{
				$i = 1;
				foreach($domandeRisposte as $domandaRisposta)
				{
					$question = $domandaRisposta["domanda"];
					$answer = $domandaRisposta["risposta"];

					if($question == null || $answer == null)
					{
						$question_array_json[] = "ERRORE: Non ci possono essere domande o risposte vuote";
						$question_array_html[] = "<span style='color: red; font-size: xx-large; font-weight: bold;'>ERRORE: Non ci possono essere domande o risposte vuote</span>";
					}

					$question_array_json[] = QuestionSchema::RenderJson($question, $answer);
					$question_array_html[] = QuestionSchema::RenderHTML($question, $answer);

					if (class_exists('ACF'))
					{
						update_field("domanda_{$i}", $question);
						update_field("risposta_{$i}", $answer);
					}
				}
			}

			//Apertura Json
			$result.= <<<TAG
<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
TAG;

			$jsonDomande = implode(",\n", $question_array_json);
			$result.= $jsonDomande;


			//Chiusura Json
			$result.= <<<TAG
]} </script>

TAG;

			$result.= <<<TAG

<h3 id="DomandeERisposte">Domande frequenti</h3> 
<div class="schema-faq-section">

TAG;
			$htmlDomande = implode("", $question_array_html);
			$result.= $htmlDomande;

			$result.= "</div>\n";
			$result.= "<!--adinj_exclude_end-->\n\n\n";

			return $result;
		}

		public static function RenderJson($question, $answer)
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

		public static function RenderHTML($question, $answer)
		{
			$question_parsed = htmlspecialchars($question);
			$answer_parsed = htmlspecialchars($answer);

			return <<<TAG
<strong class="schema-faq- question">$question_parsed</strong>
<p class="schema-faq-answer">$answer_parsed</p>
TAG;
		}
	}