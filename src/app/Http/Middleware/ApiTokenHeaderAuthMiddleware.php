<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiService;
use App\Models\ApiToken;
use App\Models\TokenType;
use Illuminate\Support\Facades\Hash;

class ApiTokenHeaderAuthMiddleware
{
    /**
     * Handle an incoming API request with token authentication
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the pipeline
     * @param string $serviceName The required API service name
     * @return mixed JSON response on failure or next middleware on success
     */
    public function handle(Request $request, Closure $next, string $serviceName): mixed
    {
        // Verify the requested API service exists
        $apiService = ApiService::where('name', $serviceName)->first();
        if (!$apiService) {
            return response()->json(['error' => 'API service not found'], 404);
        }

        $token = null;
        $tokenTypeName = null;

        // Determine authentication method from request headers
        if ($request->bearerToken()) {
            $tokenTypeName = 'bearer';
            $token = $request->bearerToken();
        } elseif ($request->header('X-API-KEY')) {
            $tokenTypeName = 'api-key';
            $token = $request->header('X-API-KEY');
        } elseif ($request->getUser() && $request->getPassword()) {
            $tokenTypeName = 'login-password'; // Basic auth with login/password
        }

        // Reject if no valid authentication method detected
        if (!$tokenTypeName) {
            return response()->json(['error' => 'No valid auth method provided'], 401);
        }

        // Verify the token type is valid
        $tokenType = TokenType::where('name', $tokenTypeName)->first();
        if (!$tokenType) {
            return response()->json(['error' => 'Unknown token type'], 401);
        }

        // Handle login/password authentication
        if ($tokenTypeName === 'login-password') {
            $login = trim($request->getUser());
            $password = trim($request->getPassword());
            $hashInput = "$login:$password";

            // Get all valid tokens for this service and token type
            $tokens = ApiToken::where('token_type_id', $tokenType->id)
                ->where('api_service_id', $apiService->id)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->get(['id', 'token', 'account_id']);

            // Check each token against the provided credentials
            foreach ($tokens as $apiToken) {
                if (Hash::check($hashInput, $apiToken->token)) {
                    $request->merge(['account_id' => $apiToken->account_id]);
                    return $next($request);
                }
            }

            return response()->json(['error' => 'Invalid credentials'], 403);
        }

        // Handle other token types
        $apiToken = ApiToken::where('token', $token)
            ->where('token_type_id', $tokenType->id)
            ->where('api_service_id', $apiService->id)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->first();

        if (!$apiToken) {
            return response()->json(['error' => 'Invalid or unauthorized token'], 403);
        }

        // Add account_id to request for downstream processing
        $request->merge([
            'account_id' => $apiToken->account_id,
        ]);

        return $next($request);
    }
}
