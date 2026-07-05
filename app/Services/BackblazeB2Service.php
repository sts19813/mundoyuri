<?php

namespace App\Services;

use App\Models\BackblazeB2Setting;
use App\Support\BackblazeB2Url;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BackblazeB2Service
{
    private const AUTHORIZE_URL = 'https://api.backblazeb2.com/b2api/v4/b2_authorize_account';

    /**
     * @return array{bucket_id: string, bucket_name: string, bucket_type: string, download_url: string}
     */
    public function verify(BackblazeB2Setting $settings): array
    {
        $authorization = $this->authorize($settings);
        $bucket = $this->resolveBucket($settings, $authorization);

        $capabilities = $authorization['apiInfo']['storageApi']['allowed']['capabilities'] ?? [];

        if (! in_array('shareFiles', $capabilities, true)) {
            throw new RuntimeException('La Application Key necesita el permiso shareFiles para generar enlaces temporales.');
        }

        return [
            'bucket_id' => $bucket['bucketId'],
            'bucket_name' => $bucket['bucketName'],
            'bucket_type' => $bucket['bucketType'] ?? 'unknown',
            'download_url' => rtrim($authorization['apiInfo']['storageApi']['downloadUrl'], '/'),
        ];
    }

    public function temporaryDownloadUrl(string $sourceUrl): string
    {
        $settings = BackblazeB2Setting::current();

        if (! $settings->isConfigured()) {
            throw new RuntimeException('Backblaze B2 no está habilitado o su configuración está incompleta.');
        }

        $file = BackblazeB2Url::parse($sourceUrl);

        if (! $file) {
            throw new RuntimeException('La URL guardada no corresponde a un archivo válido de Backblaze B2.');
        }

        if (strcasecmp($file['bucket'], (string) $settings->bucket_name) !== 0) {
            throw new RuntimeException('La URL pertenece a un bucket distinto del configurado.');
        }

        $authorization = $this->authorize($settings);
        $bucket = $this->resolveBucket($settings, $authorization);
        $storageApi = $authorization['apiInfo']['storageApi'];

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(15)
                ->withHeaders(['Authorization' => $authorization['authorizationToken']])
                ->post(rtrim($storageApi['apiUrl'], '/').'/b2api/v4/b2_get_download_authorization', [
                    'bucketId' => $bucket['bucketId'],
                    'fileNamePrefix' => $file['file'],
                    'validDurationInSeconds' => min(604800, max(60, $settings->token_ttl_seconds)),
                ]);
        } catch (ConnectionException) {
            throw new RuntimeException('No fue posible solicitar el enlace temporal a Backblaze B2.');
        }

        $this->throwForB2Error($response, 'No se pudo autorizar la descarga del video');

        $downloadUrl = rtrim($storageApi['downloadUrl'], '/');
        $url = BackblazeB2Url::canonicalUrl($file['bucket'], $file['file'], $downloadUrl);

        return $url.'?'.http_build_query([
            'Authorization' => $response->json('authorizationToken'),
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @return array<string, mixed>
     */
    private function authorize(BackblazeB2Setting $settings): array
    {
        if (! filled($settings->key_id) || ! filled($settings->application_key)) {
            throw new RuntimeException('Faltan el Key ID o la Application Key de Backblaze B2.');
        }

        try {
            $response = Http::acceptJson()
                ->timeout(15)
                ->withBasicAuth($settings->key_id, $settings->application_key)
                ->get(self::AUTHORIZE_URL);
        } catch (ConnectionException) {
            throw new RuntimeException('No fue posible conectar con Backblaze B2. Inténtalo de nuevo.');
        }

        $this->throwForB2Error($response, 'No se pudo autenticar con Backblaze B2');

        $payload = $response->json();
        $storageApi = $payload['apiInfo']['storageApi'] ?? null;

        if (! is_array($storageApi)
            || empty($storageApi['apiUrl'])
            || empty($storageApi['downloadUrl'])
            || empty($payload['authorizationToken'])) {
            throw new RuntimeException('Backblaze B2 devolvió una respuesta de autenticación incompleta.');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $authorization
     * @return array{bucketId: string, bucketName: string, bucketType?: string}
     */
    private function resolveBucket(BackblazeB2Setting $settings, array $authorization): array
    {
        $storageApi = $authorization['apiInfo']['storageApi'];
        $allowedBuckets = $storageApi['allowed']['buckets'] ?? [];

        foreach (is_array($allowedBuckets) ? $allowedBuckets : [] as $bucket) {
            $bucketId = $bucket['id'] ?? $bucket['bucketId'] ?? null;
            $bucketName = $bucket['name'] ?? $bucket['bucketName'] ?? null;

            if ($bucketId && $bucketName && strcasecmp($bucketName, (string) $settings->bucket_name) === 0) {
                return [
                    'bucketId' => $bucketId,
                    'bucketName' => $bucketName,
                    'bucketType' => $bucket['type'] ?? $bucket['bucketType'] ?? 'unknown',
                ];
            }
        }

        if (filled($settings->bucket_id)) {
            return [
                'bucketId' => $settings->bucket_id,
                'bucketName' => $settings->bucket_name,
                'bucketType' => 'unknown',
            ];
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(15)
                ->withHeaders(['Authorization' => $authorization['authorizationToken']])
                ->post(rtrim($storageApi['apiUrl'], '/').'/b2api/v4/b2_list_buckets', [
                    'accountId' => $authorization['accountId'],
                    'bucketName' => $settings->bucket_name,
                ]);
        } catch (ConnectionException) {
            throw new RuntimeException('No fue posible consultar el bucket en Backblaze B2.');
        }

        $this->throwForB2Error($response, 'No se pudo localizar el bucket configurado');

        $bucket = collect($response->json('buckets', []))
            ->first(fn (array $item) => strcasecmp((string) ($item['bucketName'] ?? ''), (string) $settings->bucket_name) === 0);

        if (! $bucket || empty($bucket['bucketId'])) {
            throw new RuntimeException('No se encontró el bucket indicado en la cuenta de Backblaze B2.');
        }

        return $bucket;
    }

    private function throwForB2Error(Response $response, string $context): void
    {
        if ($response->successful()) {
            return;
        }

        $message = trim((string) $response->json('message'));
        $code = trim((string) $response->json('code'));
        $detail = $message !== '' ? $message : ($code !== '' ? $code : 'HTTP '.$response->status());

        throw new RuntimeException($context.': '.$detail.'.');
    }
}
