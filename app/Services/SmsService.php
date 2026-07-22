<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SmsService
{
    protected string $apiKey;

    protected string $baseUrl;

    protected string $defaultSender;

    protected ?string $appId;

    public function __construct(?string $apiKey = null, ?string $baseUrl = null, ?string $defaultSender = null, ?string $appId = null)
    {
        $this->apiKey = $apiKey ?? (string) config('services.briq.api_key');
        $this->baseUrl = rtrim($baseUrl ?? (string) config('services.briq.base_url', 'https://karibu.briq.tz'), '/');
        $this->defaultSender = $defaultSender ?? (string) config('services.briq.default_sender', 'BRIQ');
        $this->appId = $appId ?? config('services.briq.app_id');
    }

    /**
     * Send an instant SMS via Briq Karibu API.
     *
     * @param  string  $content  Message body
     * @param  string|array<int, string>  $recipients  One or more phone numbers
     * @param  string|null  $senderId  Sender ID (defaults to BRIQ_DEFAULT_SENDER)
     * @param  array{
     *     campaign_id?: string|null,
     *     groups?: array<int, string>|null,
     *     flash?: bool,
     *     send_at?: string|null
     * }  $options
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    public function send(string $content, string|array $recipients, ?string $senderId = null, array $options = []): array
    {
        $this->ensureConfigured();

        $recipients = $this->normalizeRecipients($recipients);

        if ($recipients === []) {
            throw new RuntimeException('At least one valid SMS recipient is required.');
        }

        if (trim($content) === '') {
            throw new RuntimeException('SMS content cannot be empty.');
        }

        $payload = array_filter([
            'content' => $content,
            'recipients' => $recipients,
            'sender_id' => $senderId ?: $this->defaultSender,
            'campaign_id' => $options['campaign_id'] ?? null,
            'groups' => $options['groups'] ?? null,
            'flash' => $options['flash'] ?? false,
            'send_at' => $options['send_at'] ?? null,
        ], static fn ($value) => $value !== null);

        try {
            $response = Http::withHeaders($this->headers())
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->post("{$this->baseUrl}/v1/message/send-instant", $payload)
                ->throw();
        } catch (ConnectionException $e) {
            Log::error('Briq SMS connection failed', [
                'message' => $e->getMessage(),
                'recipients' => $recipients,
            ]);

            throw new RuntimeException('Unable to reach Briq SMS API: '.$e->getMessage(), 0, $e);
        } catch (RequestException $e) {
            $body = $e->response?->json() ?? $e->response?->body();

            Log::error('Briq SMS send failed', [
                'status' => $e->response?->status(),
                'body' => $body,
                'recipients' => $recipients,
            ]);

            throw new RuntimeException(
                'Briq SMS API request failed: '.($e->response?->body() ?: $e->getMessage()),
                (int) ($e->response?->status() ?? 0),
                $e
            );
        }

        $data = $response->json() ?? [];

        Log::info('Briq SMS sent', [
            'job_id' => $data['job_id'] ?? null,
            'status' => $data['status'] ?? null,
            'recipients_count' => count($recipients),
        ]);

        return $data;
    }

    /**
     * Convenience helper for a single recipient.
     *
     * @param  array{
     *     campaign_id?: string|null,
     *     groups?: array<int, string>|null,
     *     flash?: bool,
     *     send_at?: string|null
     * }  $options
     * @return array<string, mixed>
     */
    public function sendTo(string $phone, string $content, ?string $senderId = null, array $options = []): array
    {
        return $this->send($content, [$phone], $senderId, $options);
    }

    /**
     * Normalize a phone number to Briq's expected format (e.g. 255788344348).
     */
    public function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        // Local TZ format: 07XXXXXXXX / 06XXXXXXXX
        if (preg_match('/^0([67]\d{8})$/', $digits, $matches)) {
            return '255'.$matches[1];
        }

        // Without leading zero: 7XXXXXXXX / 6XXXXXXXX
        if (preg_match('/^([67]\d{8})$/', $digits)) {
            return '255'.$digits;
        }

        // Already international without plus: 255XXXXXXXXX
        if (preg_match('/^255[67]\d{8}$/', $digits)) {
            return $digits;
        }

        // Fallback: keep digits if reasonably long (other country codes)
        if (strlen($digits) >= 10 && strlen($digits) <= 15) {
            return $digits;
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    protected function headers(): array
    {
        $headers = [
            'X-API-Key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        if (! empty($this->appId)) {
            $headers['X-App-ID'] = $this->appId;
        }

        return $headers;
    }

    /**
     * @param  string|array<int, string>  $recipients
     * @return array<int, string>
     */
    protected function normalizeRecipients(string|array $recipients): array
    {
        $list = is_array($recipients) ? $recipients : [$recipients];

        $normalized = [];

        foreach ($list as $recipient) {
            $phone = $this->normalizePhone((string) $recipient);

            if ($phone !== null) {
                $normalized[] = $phone;
            }
        }

        return array_values(array_unique($normalized));
    }

    protected function ensureConfigured(): void
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('Briq SMS API key is not configured. Set BRIQ_API_KEY in your environment.');
        }
    }
}
