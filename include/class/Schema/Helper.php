<?php

namespace include\class\Schema;

class Helper
{
    public static function CheckJsonError(string $json): string
    {
        $json_last_error = json_last_error();

        switch ($json_last_error)
        {
            //Nessun errore
            case JSON_ERROR_NONE:
                return "";
                break;

            //varie casistiche di errori
            case JSON_ERROR_DEPTH:
                $errormessage = "Maximum stack depth exceeded";
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $errormessage = "Underflow or the modes mismatch";
                break;
            case JSON_ERROR_CTRL_CHAR:
                $errormessage = "Unexpected control character found";
                break;
            case JSON_ERROR_SYNTAX:
                $errormessage = "Syntax error, malformed JSON";
                break;
            case JSON_ERROR_UTF8:
                $errormessage = "Malformed UTF-8 characters, possibly incorrectly encoded";
                break;
            default:
                $errormessage = "Unknown error";
                break;
        }

//		https://github.com/scrivo/highlight.php


        $errormessage = "<pre>$errormessage<br/>
		[$json]
		<a href='https://codebeautify.org/jsonviewer?input=[$json]'>Validator</a>
		</pre>";
        // Instantiate the Highlighter.
        //$hl = new \Highlight\Highlighter();

//		try {
//			// Highlight some code.
//			$highlighted = $hl->highlight('json', $code);
//
//			echo "<pre><code class=\"hljs {$highlighted->language}\">";
//			echo $highlighted->value;
//			echo "</code></pre>";
//		}
//		catch (DomainException $e) {
//			// This is thrown if the specified language does not exist
//
//			echo "<pre><code>";
//			echo $code;
//			echo "</code></pre>";
//		}

        return $errormessage;
    }
}