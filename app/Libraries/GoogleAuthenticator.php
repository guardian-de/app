<?php

namespace App\Libraries;

class GoogleAuthenticator
{
    private static $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function createSecret(): string
    {
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= self::$base32Chars[random_int(0, 31)];
        }
        return $secret;
    }

    public static function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $currentTimeSlice = floor(time() / 30);
        
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::getCode($secret, $currentTimeSlice + $i);
            if ($calculatedCode === $code) {
                return true;
            }
        }
        return false;
    }

    private static function getCode(string $secret, int $timeSlice): string
    {
        $secretKey = self::base32Decode($secret);
        
        // Pack time slice into 8-byte binary string
        $time = chr(0).chr(0).chr(0).chr(0).pack('N', $timeSlice);
        
        // HMAC-SHA1
        $hm = hash_hmac('sha1', $time, $secretKey, true);
        
        // Determine offset
        $offset = ord($hm[19]) & 0x0F;
        
        // Extract 4 bytes
        $hashPart = substr($hm, $offset, 4);
        
        // Unpack into integer
        $value = unpack('N', $hashPart);
        $value = $value[1];
        $value = $value & 0x7FFFFFFF;
        
        $modulo = pow(10, 6);
        $calculated = str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
        
        return $calculated;
    }

    private static function base32Decode(string $base32): string
    {
        $base32 = strtoupper($base32);
        if (!preg_match('/^[A-Z2-7=]+$/', $base32)) {
            return '';
        }
        
        $base32 = str_replace('=', '', $base32);
        $binaryString = '';
        
        foreach (str_split($base32) as $char) {
            $val = strpos(self::$base32Chars, $char);
            $binaryString .= str_pad(decbin($val), 5, '0', STR_PAD_LEFT);
        }
        
        $chunks = str_split($binaryString, 8);
        $decoded = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) === 8) {
                $decoded .= chr(bindec($chunk));
            }
        }
        
        return $decoded;
    }
}
