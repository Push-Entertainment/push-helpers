<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;
use RuntimeException;

/**
 * Class Security
 *
 * @package Push\Functions
 */
class Security
{

    use Error;


    private const ENCRYPTION_CHIPPER_ALGO = 'aes-256-cbc';


    /**
     * Encrypt string
     *
     * @param string $value
     * @param string $crypto_key
     * @param bool   $url_safe
     *
     * @return string
     * @throws \Exception
     */
    public static function encryptText(string $value, string $crypto_key, bool $url_safe = false ): string
    {
        $encryptionKey            = base64_decode($crypto_key);
        $openssl_cipher_iv_length = openssl_cipher_iv_length(self::ENCRYPTION_CHIPPER_ALGO);

        if( $openssl_cipher_iv_length === false )
        {
            throw new RuntimeException("Unable to determine Cipher length openssl_cipher_iv_length");
        }

        $iv                       = openssl_random_pseudo_bytes( $openssl_cipher_iv_length);
        $encrypted                = openssl_encrypt($value, self::ENCRYPTION_CHIPPER_ALGO, $encryptionKey, 0, $iv);
        $encrypted_string         = base64_encode($encrypted . '::' . $iv);

        if( $url_safe )
        {
            $encrypted_string = bin2hex( $encrypted_string );
        }

        return $encrypted_string;
    }


    /**
     * Decrypt string
     *
     * @param string $value
     * @param string $crypto_key
     * @param bool $url_safe
     *
     * @return false|string
     */
    public static function decryptText(string $value, string $crypto_key, bool $url_safe = false ): bool|string
    {
        if( $url_safe )
        {
            $value = hex2bin( $value );
        }

        $encryptionKey = base64_decode($crypto_key);
        [$encryptedData, $iv] = array_pad(explode('::', base64_decode($value), 2), 2, NULL);

        return openssl_decrypt($encryptedData, self::ENCRYPTION_CHIPPER_ALGO, $encryptionKey, 0, $iv);
    }


    /**
     * Return password hash
     *
     * @param string $user_password
     * @param string $algo
     *
     * @return string
     */
    public static function generatePasswordHash(string $user_password, string $algo = PASSWORD_ARGON2I): string
    {
        return password_hash($user_password, $algo);
    }


    /**
     * Generate random shuffled string, alphanumeric and special characters
     *
     * @param int         $length / max 16.
     * @param bool        $include_special_chars
     * @param bool|string $uuid   - uuid prefix required
     *
     * @return string
     */
    public static function generatePassword(int $length = 16, bool $include_special_chars = TRUE, bool|string $uuid = FALSE): string
    {
        $password    = '';
        $alpha       = implode("", range('a', 'z'));
        $alpha_upper = strtoupper($alpha);
        $numeric     = implode("", range('0', '9'));
        $special     = "!@#{}[]^_-()*?";

        $password .= $alpha[(mt_rand() % strlen($alpha))];
        $password .= $alpha[(mt_rand() % strlen($alpha))];
        $password .= $alpha[(mt_rand() % strlen($alpha))];
        $password .= $alpha_upper[(mt_rand() % strlen($alpha_upper))];
        $password .= $alpha_upper[(mt_rand() % strlen($alpha_upper))];
        $password .= $alpha_upper[(mt_rand() % strlen($alpha_upper))];
        $password .= $numeric[(mt_rand() % strlen($numeric))];
        $password .= $numeric[(mt_rand() % strlen($numeric))];
        $password .= $numeric[(mt_rand() % strlen($numeric))];

        if ($include_special_chars) {
            $password .= $special[(mt_rand() % strlen($special))];
            $password .= $special[(mt_rand() % strlen($special))];
            $password .= $special[(mt_rand() % strlen($special))];
            $password .= $special[(mt_rand() % strlen($special))];
        }

        $password .= $alpha[(mt_rand() % strlen($alpha))];
        $password .= $alpha_upper[(mt_rand() % strlen($alpha_upper))];
        $password .= $numeric[(mt_rand() % strlen($numeric))];
        $password = substr(str_shuffle($password), 0, $length);

        if (!empty($uuid)) {
            $password = uniqid($uuid, TRUE) . $password;

            if (!$include_special_chars) {
                $password = preg_replace('~[^A-Za-z\d]+~i', '', $password);
            }
        }

        return $password;
    }


    /**
     * Validate digest auth
     *
     * @param string $txt
     *
     * @return array|bool
     */
    public static function parseDigestHeaders( string $txt): bool|array
    {
        // protect against missing data
        $needed_parts = [
            'nonce'    => 1,
            'nc'       => 1,
            'cnonce'   => 1,
            'qop'      => 1,
            'username' => 1,
            'uri'      => 1,
            'response' => 1
        ];
        $data         = [];
        $keys         = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);


        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ?: $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? FALSE : $data;
    }


    /**
     * Display digest authorization request | Looks if values exist in $_SERVER['PHP_AUTH_DIGEST']
     *
     * @param string $realm
     * @param array  $allowed_methods
     */
    public static function displayAuthorizationRequest(string $realm, array $allowed_methods = [ 'GET', 'POST', 'PUT', 'DELETE' ] ): void
    {
        header( "Access-Control-Allow-Origin: *" );
        header( "Access-Control-Allow-Methods: " . strtoupper( implode( ', ', $allowed_methods ) ) );
        header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );
        header( "Access-Control-Allow-Credentials: true" );
        header( 'X-Frame-Options: SAMEORIGIN' );
        header( 'X-Frame-Options: DENY' );
        header( 'X-Powered-By: ' . strtoupper( $realm ) );

        if ( empty($_SERVER['PHP_AUTH_DIGEST'] ?? '') ) {
            header('HTTP/1.1 401 Unauthorized');
            header(
                'WWW-Authenticate: Digest realm="' . $realm . '",qop="auth",nonce="' . uniqid(
                    '',
                    TRUE
                ) . '",opaque="' . md5($realm) . '"'
            );

            die('Failed authentication.');
        }
    }


    /**
     * Require digest authorization flow
     *
     * @param array  $users_passes
     * @param string $realm
     * @param array  $allowed_methods
     *
     * @return string|null
     */
    public static function requireDigestAuthorization( array $users_passes, string $realm = 'Restricted Area', array $allowed_methods = ['GET', 'POST', 'PUT', 'DELETE' ] ): ?string
    {
        // Display header
        self::displayAuthorizationRequest( $realm, $allowed_methods );

        // Validate data
        if ( ! ( $data = self::parseDigestHeaders($_SERVER['PHP_AUTH_DIGEST'])) ||  ! isset($users_passes[$data['username']]))
        {
            header('HTTP/1.1 401 Unauthorized');
            die('Wrong Credentials!');
        }

        // Validate parsed digest
        self::validateParsedDigest($data, $users_passes[$data['username'] ], $realm );

        return $data['username'];
    }


    /**
     * Validate parsed digest data
     *
     * @param array  $user_data
     * @param string $password
     * @param string $realm
     */
    public static function validateParsedDigest(array $user_data, string $password, string $realm): void
    {
        // generate the valid response
        $A1 = md5($user_data['username'] . ':' . $realm . ':' . $password);
        $A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $user_data['uri']);
        $valid_response = md5(
            $A1 . ':' . $user_data['nonce'] . ':' . $user_data['nc'] . ':' . $user_data['cnonce'] . ':' . $user_data['qop'] . ':' . $A2
        );

        if ($user_data['response'] !== $valid_response) {
            header('HTTP/1.1 401 Unauthorized');
            die('Wrong Credentials!!');
        }
    }
}