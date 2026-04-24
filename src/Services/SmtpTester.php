<?php

namespace Voodflow\Core\Services;

use Voodflow\Core\DataTransferObjects\TestResult;

final class SmtpTester
{
    /**
     * @param array{host?:string,port?:int|string,username?:string,password?:string,encryption?:string} $credentials
     */
    public static function test(array $credentials): TestResult
    {
        $host = $credentials['host'] ?? null;
        $port = (int) ($credentials['port'] ?? 587);
        $username = $credentials['username'] ?? null;
        $password = $credentials['password'] ?? null;
        $encryption = $credentials['encryption'] ?? 'tls';

        if (! is_string($host) || $host === '') {
            return TestResult::failure('Missing SMTP host');
        }

        $requiresAuth = (is_string($username) && $username !== '') || (is_string($password) && $password !== '');

        $prefix = '';
        if ($encryption === 'ssl' || $port === 465) {
            $prefix = 'ssl://';
        }

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);

        $socket = @stream_socket_client(
            $prefix . $host . ':' . $port,
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (! is_resource($socket)) {
            return TestResult::failure("Connection failed: $errstr ($errno)", [
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption,
            ]);
        }

        stream_set_timeout($socket, 10);

        $greeting = fgets($socket, 1024);
        if (! $greeting || ! str_starts_with(trim($greeting), '220')) {
            fclose($socket);
            return TestResult::failure('Invalid SMTP server greeting: ' . trim($greeting ?: 'no response'));
        }

        fwrite($socket, "EHLO voodflow-test\r\n");
        $ehloResponse = '';
        while ($line = fgets($socket, 1024)) {
            $ehloResponse .= $line;
            if (preg_match('/^250 /', $line)) {
                break;
            }
        }

        if (! $requiresAuth) {
            fclose($socket);
            return TestResult::success("✅ SMTP connection successful to {$host}:{$port} (no auth required)", [
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption,
            ]);
        }

        if (! str_contains($ehloResponse, 'AUTH')) {
            fclose($socket);
            return TestResult::failure('SMTP server does not support authentication', [
                'server_response' => trim($ehloResponse),
            ]);
        }

        if ($encryption === 'tls' && $port !== 465) {
            fwrite($socket, "STARTTLS\r\n");
            $tlsResponse = fgets($socket, 1024);

            if (! str_starts_with(trim($tlsResponse), '220')) {
                fclose($socket);
                return TestResult::failure('STARTTLS failed: ' . trim($tlsResponse));
            }

            if (! stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return TestResult::failure('TLS encryption failed');
            }

            fwrite($socket, "EHLO voodflow-test\r\n");
            while ($line = fgets($socket, 1024)) {
                if (preg_match('/^250 /', $line)) {
                    break;
                }
            }
        }

        fwrite($socket, "AUTH LOGIN\r\n");
        $authResponse = fgets($socket, 1024);

        if (! str_starts_with(trim($authResponse), '334')) {
            fclose($socket);
            return TestResult::failure('SMTP AUTH LOGIN not accepted: ' . trim($authResponse));
        }

        fwrite($socket, base64_encode((string) $username) . "\r\n");
        $userResponse = fgets($socket, 1024);
        if (! str_starts_with(trim($userResponse), '334')) {
            fclose($socket);
            return TestResult::failure('SMTP authentication failed at username step: ' . trim($userResponse));
        }

        fwrite($socket, base64_encode((string) $password) . "\r\n");
        $passResponse = fgets($socket, 1024);
        fclose($socket);

        if (str_starts_with(trim($passResponse), '235')) {
            return TestResult::success("✅ SMTP authentication successful to $host:$port", [
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption,
                'method' => 'AUTH LOGIN',
                'username' => $username,
            ]);
        }

        return TestResult::failure('❌ SMTP authentication failed: Invalid username or password', [
            'server_response' => trim($passResponse),
            'method' => 'AUTH LOGIN',
            'host' => $host,
            'port' => $port,
        ]);
    }
}

