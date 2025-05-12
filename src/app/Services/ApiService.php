<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class ApiService
{
    protected Client $client;
    protected string $baseUrl;
    protected string $apiKey;
    protected int $limit;
    protected int $maxRetries = 3; // Maximum number of retry attempts for failed requests

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = config('services.api.base_url');
        $this->apiKey = config('services.api.key');
        $this->limit = config('services.api.limit');
    }

    /**
     * Fetches data from a specified API endpoint with retry logic for 429 errors.
     *
     * @param string $endpoint API endpoint to query
     * @param array $params Additional query parameters
     * @return array Decoded JSON response or empty array on failure
     */
    public function fetchData(string $endpoint, array $params = []): array
    {
        $params = array_merge([
            'key' => $this->apiKey,
            'limit' => $this->limit,
            'page' => 1,
        ], $params);

        $attempt = 0;

        // Retry loop for handling rate limits and temporary failures
        while ($attempt < $this->maxRetries) {
            try {
                $response = $this->client->get("{$this->baseUrl}/{$endpoint}", [
                    'query' => $params,
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                // Handle empty response
                if (empty($data)) {
                    Log::warning("Empty API response received", [
                        'endpoint' => $endpoint,
                        'params' => $params,
                        'attempt' => $attempt + 1,
                    ]);
                    return [];
                }

                if (isset($data['data']) && empty($data['data'])) {
                    Log::error("API returned empty data set", ['services' => $endpoint]);
                }
                return $data;

            } catch (RequestException $e) {
                $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : null;

                // Handling for rate limits (429 status)
                if ($statusCode === 429) {
                    $retryAfter = $e->getResponse()->getHeaderLine('Retry-After');
                    $waitTime = $retryAfter ? (int)$retryAfter : 30;

                    Log::warning("API rate limit exceeded", [
                        'endpoint' => $endpoint,
                        'retry_after' => $waitTime,
                        'attempt' => $attempt + 1,
                    ]);

                    // Wait before retrying
                    sleep($waitTime);
                    $attempt++;
                    continue;
                }

                Log::error("API request failed", [
                    'endpoint' => $endpoint,
                    'status_code' => $statusCode,
                    'params' => $params,
                ]);
                break;
            } catch (\Exception $e) {
                Log::error("Unexpected API error", [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                    'params' => $params,
                ]);
                break;
            }
        }
        return [];
    }

    /**
     * Fetches paginated data from an API endpoint.
     *
     * @param string $endpoint API endpoint to query
     * @param array $params Additional query parameters
     * @return \Generator Yields arrays of data for each page
     */
    public function fetchPaginatedData(string $endpoint, array $params = []): \Generator
    {
        $page = 1;
        $totalPages = null;

        do {
            $params['page'] = $page;
            $data = $this->fetchData($endpoint, $params);
            $lastPage = $data['meta']['last_page'] ?? null;

            // Initialize pagination on first iteration
            if ($page === 1) {
                $totalPages = $lastPage ?? 1; // Default to single page if last_page not provided

                // If API doesn't provide pagination info, yield single page and exit
                if ($lastPage === null) {
                    yield $data['data'] ?? [];
                    break;
                }
            }

            // Skip empty data pages
            if (empty($data['data'])) {
                if ($page >= $totalPages) {
                    break;
                }
                $page++;
                continue;
            }

            // Yield current page's data
            yield $data['data'];
            $page++;

        } while ($page <= $totalPages);
    }
}