<?php

namespace GoogleTranslate;


use GuzzleHttp\Client;

class Translate
{
    private static $urlBase = 'http://translate.google.com/translate_a/single';

    private static $urlParams = [
        'client' => 'webapp',
        'hl' => 'ru',
        'dj' => 1,
        'sl' => 'en', // Source language
        'tl' => 'ru', // Target language
        'q' => null, // String to translate
        'ie' => 'UTF-8', // Input encoding
        'oe' => 'UTF-8', // Output encoding
        'dt'       => [
            'bd', 'ld', 'qc', 'rm', 't'
        ],
        'tk' => null,
    ];

    public static function translate($text, $speech = true)
    {
        $token = Token::generate($text);
        $client = new Client();
        /*$dt = array_reduce(['bd', 'ld', 'qc', 'rm', 't'], function ($string, $element) {
            return $string . "&dt=" . $element;
        });*/
        $params = http_build_query(
                                    array_merge(
                                        self::$urlParams, ['tk' => $token, 'q' => $text]
                                    )
        );
        $params = preg_replace('/%5B[0-9]+%5D/simU', '', $params);

        $translation = json_decode(
            $client->get(self::$urlBase, [
                'query' => $params
            ])->getBody()->getContents(),
            true
        );

        $response = compact('translation', $translation);

        if($speech){
            $response['audio'] = self::speech($text, $token);
        }

        return $response;
    }

    private static function speech($text, $token){
        return 'https://translate.google.by/translate_tts?ie=UTF-8&q='.$text.'&tl=en&tk='.$token.'&client=webapp';
    }
}