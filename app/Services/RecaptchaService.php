<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para validar reCAPTCHA v3
 */
class RecaptchaService
{
    private string $secretKey;
    private string $verifyUrl;
    private ?string $caBundlePath = null;

    public function __construct()
    {
        $this->secretKey = config('services.recaptcha.secret_key', '');
        $this->verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        // Intentar obtener CA bundle de configuración o autodetectar en WAMP
        $configured = config('services.recaptcha.ca_bundle');
        $this->caBundlePath = is_string($configured) && $configured !== '' ? $configured : $this->detectWampCaBundle();
        Log::info('RecaptchaService CA bundle configured', ['caBundlePath' => $this->caBundlePath]);
    }

    /**
     * Verificar token de reCAPTCHA
     */
    public function verify(string $token, string $action = 'login', float $minScore = 0.5): bool
    {
        if (empty($this->secretKey)) {
            Log::warning('reCAPTCHA secret key not configured, skipping verification');
            return true;
        }

        try {
            // Usar explícitamente el CA bundle si está disponible (soluciona cURL error 60 en Windows/WAMP)
            $client = Http::asForm();
            if ($this->caBundlePath && file_exists($this->caBundlePath)) {
                Log::info('Using custom CA bundle for HTTP client', ['verify' => $this->caBundlePath]);
                $client = Http::withOptions(['verify' => $this->caBundlePath])->asForm();
            } else {
                Log::info('No custom CA bundle found, using default cURL CA', ['caBundlePath' => $this->caBundlePath]);
            }

            $response = $client->post($this->verifyUrl, [
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);

            $data = $response->json();

            // Validar respuesta básica
            if (!($data['success'] ?? false)) {
                Log::warning('reCAPTCHA verification failed', ['errors' => $data['error-codes'] ?? []]);
                return false;
            }

            // Validar acción (opcional)
            if (isset($data['action']) && $data['action'] !== $action) {
                Log::warning('reCAPTCHA action mismatch', [
                    'expected' => $action,
                    'received' => $data['action']
                ]);
                return false;
            }

            // Validar score (para reCAPTCHA v3)
            if (isset($data['score']) && $data['score'] < $minScore) {
                Log::warning('reCAPTCHA score too low', [
                    'score' => $data['score'],
                    'minScore' => $minScore
                ]);
                return false;
            }

            return true;

        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error: ' . $e->getMessage());
            // En caso de error, fallar de forma segura (rechazar)
            return false;
        }
    }

    /**
     * Verificar si reCAPTCHA está habilitado
     */
    public function isEnabled(): bool
    {
        return !empty($this->secretKey);
    }

    /**
     * Detectar automáticamente el cacert.pem que trae WampServer.
     */
    private function detectWampCaBundle(): ?string
    {
        // Solo tiene sentido intentar en Windows
        if (\PHP_OS_FAMILY !== 'Windows') {
            return null;
        }

        $base = 'C:/wamp64/bin/php';
        if (!is_dir($base)) {
            return null;
        }

        // Buscar en todas las versiones instaladas de PHP dentro de WAMP
        $matches = glob($base . '/php*/extras/ssl/cacert.pem', GLOB_NOSORT);

        if ($matches && count($matches) > 0) {
            // Elegir la versión "más alta" disponible
            natcasesort($matches);
            $candidate = end($matches) ?: null;

            if ($candidate && file_exists($candidate)) {
                Log::info('Using WAMP cacert.pem for reCAPTCHA verification', ['path' => $candidate]);
                return $candidate;
            }
        }

        // Fallback: bundle incluido con phpMyAdmin en WAMP
        $pmaCandidates = glob('C:/wamp64/apps/phpmyadmin*/vendor/composer/ca-bundle/res/cacert.pem', GLOB_NOSORT);
        if ($pmaCandidates) {
            natcasesort($pmaCandidates);
            $pma = end($pmaCandidates) ?: null;
            if ($pma && file_exists($pma)) {
                Log::info('Using phpMyAdmin cacert.pem for reCAPTCHA verification', ['path' => $pma]);
                return $pma;
            }
        }

        return null;
    }
}
