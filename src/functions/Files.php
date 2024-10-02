<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;

/**
 * Class Files
 *
 * @package Push\Functions
 */
class Files
{

    use Error;


    /**
     * @param string $directory_path
     *
     * @return string
     * @throws \Exception
     */
    public static function createDir( string $directory_path ): string
    {
        // Dir exists
        if( is_dir( $directory_path ) )
        {
            if( ! is_writable( $directory_path ) )
            {
                chmod( $directory_path, 0755 );
                chown( $directory_path, getmyuid() );
            }

            if( ! is_writable( $directory_path ) )
            {
                throw new \Exception('Directory exists, but it\'s not writable ' . $directory_path );
            }
        }
        elseif( ! is_dir($directory_path) )
        {
            if( ! mkdir($directory_path, 0755, true) && ! is_dir( $directory_path ) )
            {
                throw new \Exception('Unable to create directory under given path ' . $directory_path );
            }
        }
        
        return $directory_path;
    }


    /**
     * @param array  $files [ car.txt, dog.log ]
     * @param string $extension 'txt'
     *
     * @return array [ car.txt ]
     */
    public static function filterArrayOfFilesBasedOnExtension(array $files, string $extension = 'txt'): array
    {
        return array_filter(
            array_map(
                static function ($file) use ($extension) {
                    return (stripos($file, ".{$extension}") ? $file : NULL);
                },
                $files
            )
        );
    }


    /**
     * Replace anything in path apart from '~[^A-Za-z0-9-@]~' Alphanumeric and -@
     *
     * @param string $path
     * @param string $allowed
     *
     * @return string
     */
    public static function sanitizeFileName( string $path, string $allowed = 'A-Za-z0-9\-_.' ):string
    {
        return (string) preg_replace('~-{2,}~','-', preg_replace('~[^' . Texts::escapeCharacter($allowed, '~'). ']~', '-', $path ) );
    }


    /**
     * Download file from remote url
     * @param string $url
     * @param string $download_dir
     *
     * @return string|null
     * @throws \Exception
     */
    public static function downloadFileFromUrl( string $url, string $download_dir ):?string
    {
        $name          = md5($url);
        $extension     = pathinfo($url, PATHINFO_EXTENSION);
        $download_path = rtrim($download_dir, '/' ) . '/' . $name . '.' . $extension;

        /** @noinspection NotOptimalIfConditionsInspection */
        if(self::createDir(dirname($download_path)) && file_put_contents(
                $download_path,
                file_get_contents($url)
            ) && file_exists( $download_path )) {
            return $download_path;
        }

        return null;
    }



    /**
     * Removes files if they exists
     *
     * @param array $array_file_paths
     */
    public static function deleteFiles( array $array_file_paths ): void
    {
        foreach ( $array_file_paths as $file ) {
            if ( file_exists( $file ) && is_writable( $file ) ) {
                unlink( $file );
            }
        }
    }
}