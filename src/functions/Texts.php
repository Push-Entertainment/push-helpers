<?php

namespace Push\Functions;
use Push\Functions\Traits\Error;
use ForceUTF8\Encoding;

/**
 * Class Texts
 *
 * @package Push\Functions
 */
class Texts
{

    use Error;

    /**
     * @param string|null $string
     * @param string      $replacement_char
     * @param float       $chars_percent_to_hide
     *
     * @return string
     */
    public static function maskString( ?string $string, string $replacement_char = "*", float $chars_percent_to_hide = 0.85 ): string
    {
        if( empty($string) )
        {
            return '';
        }

        $string_length      = strlen($string);
        $chars_to_hide      = round( $string_length * $chars_percent_to_hide );
        $string_subs        = substr($string, $string_length - $chars_to_hide, $chars_to_hide - ($string_length - $chars_to_hide ) );
        $replacement_length = strlen( $string_subs);


        return str_replace($string_subs, implode("", array_fill(0,$replacement_length, $replacement_char )), $string);
    }




    /**
     * Get first name from full name
     *
     * @param string $full_name
     *
     * @return string
     */
    public static function getFirstName(string $full_name): string
    {
        return trim(substr($full_name, 0, strpos($full_name, ' ')));
    }

    /**
     * Get last name from full name
     *
     * @param string $full_name
     *
     * @return string
     */
    public static function getLastName(string $full_name): string
    {
        return trim(substr($full_name, strpos($full_name, ' '), strlen($full_name)));
    }


    /**
     * Replace anything in path apart from 'A-Za-z0-9\-' Alphanumeric and -@
     *
     * @param string $title
     * @param string $allowed
     *
     * @return string
     */
    public static function sanitizeTitle(string $title, string $allowed = 'A-Za-z0-9\-'): string
    {
        return (string)preg_replace(
            '~-{2,}~',
            '-',
            preg_replace(
                '~[^' . self::escapeCharacter($allowed, '~') . ']~',
                '-',
                strtolower(trim($title))
            )
        );
    }


    /**
     * SANITIZE FIELDS
     *
     * @param string      $content
     * @param bool        $stripHTML
     * @param bool|string $encoding
     * @param array       $extra_args
     *
     * @return string
     */
    public static function sanitizeContent(
        string $content,
        bool $stripHTML = FALSE,
        bool $encoding = FALSE,
        array $extra_args = []
    ): string {
        $content = trim($content);

        /**
         * REMOVE HTML
         */
        if (filter_var($stripHTML, FILTER_VALIDATE_BOOLEAN)) {
            $content = htmlspecialchars(strip_tags(html_entity_decode($content)));
        }


        /**
         * FIX COPIED CHARS
         */
        $copyfix = [
            "\xC2\xAB"     => "<<",
            "\xC2\xBB"     => ">>",
            "\xE2\x80\x98" => "'",
            "\xE2\x80\x99" => "'",
            "\xE2\x80\x9A" => "'",
            "\xE2\x80\x9B" => "'",
            "\xE2\x80\x9C" => '"',
            "\xE2\x80\x9D" => '"',
            "\xE2\x80\x9E" => '"',
            "\xE2\x80\x9F" => '"',
            "\xE2\x80\xB9" => "<",
            "\xE2\x80\xBA" => ">",
            "\xE2\x80\x93" => "-",
            "\xE2\x80\x94" => "-",
            "\t"           => " ",
            chr(145)       => "'",
            chr(146)       => "'",
            chr(147)       => '"',
            chr(148)       => '"',
            chr(151)       => "-",

        ];

        $content = str_ireplace( array_keys( $copyfix), array_values($copyfix), $content );
        $content = trim(preg_replace('/(\s+|\t+)/i', ' ', $content));


        if ( !empty($extra_args) && !empty( $extra_args['regex-strip'])) {
            $content = preg_replace($extra_args['regex-strip'], "", $content);
        }


        if ((FALSE !== $encoding) && method_exists( Encoding::class, $encoding)) {
            $content = Encoding::$encoding(stripslashes($content));
        }

        return $content;
    }


    /**
     * @param string $phrase
     * @param array  $words
     *
     * @return string
     */
    public static function removeWords(
        string $phrase,
        array $words = [
            "Boys",
            "Boy's",
            "Girls",
            "Girl's",
            "Mens",
            "Men's",
            "Women's",
            "Ladies",
            "Unisex",
            "Kids",
            "Kid's"
        ]
    ): string {

        $phrase = preg_replace("~(" . self::escapeCharacter( implode("|", $words ), '~') . ")~i", "", $phrase );

        // Remove multiple spaces
        $phrase = preg_replace("~\s{2,}~", " ", $phrase );

        // Remove any starting/ending punctuation/spaces
        return preg_replace("~(^\s+|^[,;:.]+|\s+$|[,;:.]+$)~", "", $phrase );
    }


    /**
     * @param string $text
     * @param string $char
     *
     * @return string
     */
    public static function escapeCharacter( string $text, string $char ): string
    {
        return str_replace( $char, "\\" . $char, $text );
    }


    /**
     * Remove dups from a list of words
     *
     * @param string $words
     * @param string $separator
     *
     * @return string
     */
    public static function uniqueWordsLists( string $words, string $separator = ',' ): string
    {
        return implode(
            $separator,
            array_unique(
                array_filter(
                    array_map(
                        'trim',
                        explode( $separator, $words )
                    )
                )
            )
        );

    }
}