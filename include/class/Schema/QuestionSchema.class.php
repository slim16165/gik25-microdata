<?php
	/**
	 * Created by PhpStorm.
	 * User: g.salvi
	 * Date: 21/10/2019
	 * Time: 12:14
	 */

	class QuestionSchema
	{
		static function RenderJson(string $question, string $answer)
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

		static function RenderHTML(string $question, string $answer)
		{
			$question_parsed = htmlspecialchars($question);
			$answer_parsed = htmlspecialchars($answer);

			return <<<TAG
<strong class="schema-faq- question">$question_parsed</strong>
<p class="schema-faq-answer">$answer_parsed</p>
TAG;
		}
	}