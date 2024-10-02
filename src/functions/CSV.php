<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;
use JetBrains\PhpStorm\NoReturn;

/**
 * Class CSV
 *
 * @package Push\Functions
 */
class CSV
{

    use Error;

    /**
     * Downloads
     *
     * @param string       $file_name
     * @param array|string $data
     */
    #[NoReturn] public static function downloadCsv(string $file_name, array|string $data ): void
    {
        $file_name = preg_replace("~(\.csv$|\s)~i", "", trim($file_name)) . ".csv";
        if( is_array( $data ))
        {
            $data = implode( "\n", $data );
        }

        // Clean output buffer
        if (ob_get_level() !== 0 && @ob_end_clean() === FALSE)
        {
            @ob_clean();
        }

        //ini_set('auto_detect_line_endings', TRUE); @deprecated
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . $file_name);
        header('Content-Length: '. strlen($data) );
        header('Cache-Control: private, no-transform, no-store, must-revalidate');
        header('Pragma: no-cache');
        header("Expires: 0");

        echo $data;
        exit;
    }


    /**
     * Escape Csv file data columns
     *
     * @param array|string $row
     *
     * @return array|string
     */
    public static function escapeCsvFields( array|string $row = [] ): array|string
    {
        // Multiple columns / array provided
        if (is_array($row))
        {
            $data = [];
            foreach ($row as $entry)
            {
                $data[] = self::escapeField( $entry );
            }

            return $data;
        }

        // Single value
        return self::escapeField( $row );
    }


    /**
     * Escape single field/column value
     *
     * @param string $field
     *
     * @return string
     */
    public static function escapeField( string $field ): string
    {
        return '"' . str_replace(array('"', ','), array('""', ','), preg_replace("~[\n\t]+~i", "", $field )) . '"';
    }


    /**
     * @param string $csvFile
     *
     * @return false|int|string
     */
    public static function detectDelimiter( string $csvFile ): bool|int|string
    {
        $delimiters = array(
            ';'  => 0,
            ','  => 0,
            "\t" => 0,
            "|"  => 0
        );

        $handle = fopen($csvFile, "rb");
        $firstLine = fgets($handle);
        fclose($handle);
        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        return array_search(max($delimiters), $delimiters, false);
    }


}