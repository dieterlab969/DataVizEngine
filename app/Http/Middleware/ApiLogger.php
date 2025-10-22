<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLogger
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->logRequest($request);

        $response = $next($request);

        $this->logResponse($request, $response);

        return $response;
    }

    protected function logRequest(Request $request)
    {
        Log::channel('debug')->info('Incoming Request', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $this->sanitizeHeaders($request->headers->all()),
            'input' => $this->sanitizeInput($request->all()),
        ]);
    }

    protected function LogResponse(Request $request, Response $response)
    {
        Log::channel('debug')->info('Outgoing Response', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'response_content' => $this->getResponseContent($response)
        ]);
    }

    protected function sanitizeHeaders(array $headers)
    {
        // Remove sensitive headers
        $sensitiveHeaders = ['authorization', 'cookie', 'set-cookie'];

        return collect($headers)
            ->filter(fn($value, $key) => !in_array(strtolower($key), $sensitiveHeaders))
            ->toArray();
    }

    protected function getResponseContent(Response $response)
    {
        // Safely get response content
        $content = $response->getContent();

        $decodedContent = json_decode($content, true);

        return $decodedContent ?? $content;
    }

    protected function sanitizeInput(array $input)
    {
        // Remove sensitive input fields
        $sensitiveFields = ['password', 'credit_card', 'token'];

        return collect($input)
            ->map(function($value, $key) use ($sensitiveFields) {
                return in_array(strtolower($key), $sensitiveFields)
                    ? '***REDACTED***'
                    : $value;
            })
            ->toArray();
    }
}
