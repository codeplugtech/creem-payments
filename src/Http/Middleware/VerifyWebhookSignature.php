<?php

namespace Codeplugtech\CreemPayments\Http\Middleware;

use Closure;
use Codeplugtech\CreemPayments\Webhook;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $secret = config('creem.webhook_secret');
            if (empty($secret)) {
                throw new \Exception('Creem Webhook Secret not set');
            }

            Webhook::fromRaw($secret)->verify(
                $request->getContent(),
                $request->headers->all()
            );
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 403);
        }

        return $next($request);
    }
}
