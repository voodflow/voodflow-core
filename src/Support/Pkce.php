<?php

namespace Voodflow\Core\Support;

final class Pkce
{
    /**
     * @return array{code_verifier: string, code_challenge: string}
     */
    public static function generate(int $verifierLength = 64): array
    {
        $verifierLength = max(43, min(128, $verifierLength));

        $codeVerifier = self::randomUrlSafeString($verifierLength);

        $codeChallenge = rtrim(
            strtr(
                base64_encode(hash('sha256', $codeVerifier, true)),
                '+/',
                '-_'
            ),
            '='
        );

        return [
            'code_verifier' => $codeVerifier,
            'code_challenge' => $codeChallenge,
        ];
    }

    private static function randomUrlSafeString(int $length): string
    {
        $bytes = random_bytes((int) ceil($length * 0.75));
        $base64 = rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');

        return substr($base64, 0, $length);
    }
}

