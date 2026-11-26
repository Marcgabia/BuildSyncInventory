<?php

namespace App\Http\Middleware;

use App\Models\IntegrationLog;
use App\Support\IntegrationResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * B5 / B7: checks the X-API-KEY header against config('integration.api_key')
 * and writes every incoming call to integration_logs.
 * Alias: integration.key (registered in bootstrap/app.php).
 */
class VerifyIntegrationKey
{
    /** Response bodies bigger than this are not stored in the log. */
    private const MAX_LOGGED_BYTES = 65535;

    public function handle(Request $request, Closure $next): Response
    {
        $started = microtime(true);
        $expected = (string) config('integration.api_key');
        $given = (string) $request->header('X-API-KEY', '');

        // No key configured means nobody gets in. hash_equals avoids timing attacks.
        if ($expected === '' || $given === '' || ! hash_equals($expected, $given)) {
            $response = IntegrationResponse::error('Invalid or missing API key', [], 401);
        } else {
            $response = $next($request);
        }

        $this->log($request, $response, $started);

        return $response;
    }

    private function log(Request $request, Response $response, float $started): void
    {
        try {
            $body = $request->isMethod('GET') ? null : $request->all();
            $content = (string) $response->getContent();
            $decoded = strlen($content) <= self::MAX_LOGGED_BYTES ? json_decode($content, true) : null;
            $status = $response->getStatusCode();

            IntegrationLog::create([
                'direction' => 'in',
                'system' => $this->callerSystem($request),
                'method' => $request->method(),
                'url' => substr($request->fullUrl(), 0, 500),
                'reference_number' => $request->input('reference_number'),
                'request_body' => $body ?: null,
                'status_code' => $status,
                'response_body' => is_array($decoded) ? $decoded : null,
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'error' => $status >= 400 ? ($decoded['message'] ?? null) : null,
            ]);
        } catch (Throwable $e) {
            // A logging problem must never break the API call itself.
            report($e);
        }
    }

    private function callerSystem(Request $request): string
    {
        $source = strtoupper((string) $request->input('source_system', ''));

        return in_array($source, ['CRM', 'PROC', 'INV', 'HR'], true) ? $source : 'UNKNOWN';
    }
}
