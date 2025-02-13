<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;

/**
 * Class GAuth
 *
 * @package Push\Functions
 */
class GAuth
{
    use Error;

    /**
     * @var int auth code length
     */
    protected static int $_codeLength = 6;

    /**
     * Create new secret.
     * 16 characters, randomly chosen from the allowed base32 characters.
     *
     * @param int $secretLength
     *
     * @return string
     */
    public static function createSecret(int $secretLength = 16): string
    {
        $validChars = self::_getBase32LookupTable();
        unset($validChars[32]);

        return str_repeat($validChars[array_rand($validChars)], $secretLength);
    }

    /**
     * Get array with all 32 characters for decoding from/encoding to base32
     *
     * @return array
     */
    protected static function _getBase32LookupTable(): array
    {
        return [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H', //  7
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P', // 15
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X', // 23
            'Y',
            'Z',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7', // 31
            '='  // padding char
        ];
    }

    /**
     * Get QR-Code URL for image, from google charts
     *
     * @param string $provider
     * @param string $name
     * @param string $secret
     *
     * @return string
     */
    public static function getQRCodeGoogleUrl(string $provider, string $name, string $secret): string
    {
        $urlencoded = urlencode(
            'otpauth://totp/' . $name . '?secret=' . $secret . '&issuer=' . urlencode($provider)
        );

        return 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . $urlencoded;
    }

    /**
     * Check if the code is correct. This will accept codes starting from $discrepancy*30sec ago to $discrepancy*30sec
     * from now
     *
     * @param string $secret
     * @param string $code
     * @param int    $discrepancy This is the allowed time drift in 30 second units (8 means 4 minutes before or after)
     *
     * @return bool
     */
    public static function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $currentTimeSlice = floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++)
        {
            $calculatedCode = self::getCode($secret, $currentTimeSlice + $i);
            if ($calculatedCode === $code)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate the code, with given secret and point in time
     *
     * @param string              $secret
     * @param float|bool|int|null $timeSlice
     *
     * @return string
     */
    public static function getCode(string $secret, float|bool|int $timeSlice = null): string
    {
        if ($timeSlice === null)
        {
            $timeSlice = floor(time() / 30);
        }

        $secretkey = self::_base32Decode($secret);

        // Pack time into binary string
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretkey, true);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashpart = substr($hm, $offset, 4);

        // Unpak binary value
        $value = unpack('N', $hashpart);
        $value = $value[1];
        // Only 32 bits
        $value &= 0x7FFFFFFF;

        $modulo = 10 ** self::$_codeLength;

        return str_pad($value % $modulo, self::$_codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Helper class to decode base32
     *
     * @param $secret
     *
     * @return bool|string
     */
    protected static function _base32Decode($secret): bool|string
    {
        if (empty($secret))
        {
            return '';
        }

        $base32chars        = self::_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);

        $paddingCharCount = substr_count($secret, $base32chars[32]);
        $allowedValues    = [6, 4, 3, 1, 0];
        if (!in_array($paddingCharCount, $allowedValues))
        {
            return false;
        }
        for ($i = 0; $i < 4; $i++)
        {
            if (
                $paddingCharCount === $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) !== str_repeat($base32chars[32], $allowedValues[$i]))
            {
                return false;
            }
        }
        $secret        = str_replace('=', '', $secret);
        $secret        = str_split($secret);
        $secret_length = count($secret);
        $binaryString  = "";

        for ($i = 0; $i < $secret_length; $i += 8)
        {
            $x = "";
            if (!in_array($secret[$i], $base32chars, true))
            {
                return false;
            }
            for ($j = 0; $j < 8; $j++)
            {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            foreach ($eightBits as $zValue)
            {
                $binaryString .= (($y = chr(base_convert($zValue, 2, 10))) || ord($y) === 48) ? $y : "";
            }
        }

        return $binaryString;
    }


    /**
     * Helper class to encode base32
     *
     * @param string $secret
     * @param bool   $padding
     *
     * @return string
     */
    protected static function _base32Encode(string $secret, bool $padding = true): string
    {
        if (empty($secret))
        {
            return '';
        }

        $base32chars  = self::_getBase32LookupTable();
        $binaryString = "";

        foreach (str_split($secret) as $iValue)
        {
            $binaryString .= str_pad(base_convert(ord($iValue), 10, 2), 8, '0', STR_PAD_LEFT);
        }

        $fiveBitBinaryArray = str_split($binaryString, 5);
        $base32             = "";
        $i                  = 0;

        while ($i < count($fiveBitBinaryArray))
        {
            $base32 .= $base32chars[base_convert(str_pad($fiveBitBinaryArray[$i], 5, '0'), 2, 10)];
            $i++;
        }

        if ($padding && $x = (int)((strlen($binaryString) % 40) !== 0))
        {
            if ($x === 8)
            {
                $base32 .= str_repeat($base32chars[32], 6);
            }
            elseif ($x === 16)
            {
                $base32 .= str_repeat($base32chars[32], 4);
            }
            elseif ($x === 24)
            {
                $base32 .= str_repeat($base32chars[32], 3);
            }
            elseif ($x === 32)
            {
                $base32 .= $base32chars[32];
            }
        }

        return $base32;
    }
}