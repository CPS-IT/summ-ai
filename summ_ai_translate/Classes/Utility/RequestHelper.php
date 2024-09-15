<?php
declare(strict_types=1);

namespace THB\SummAiTranslate\Utility;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RequestHelper
{
    private static array $API_URLS = [
        'base' => 'https://backend.summ-ai.com',
        'usage' => '/api/v1/translation/usage/',
        'translate' => '/api/v1/translation/',
    ];

    /**
     * Calls the specified endpoint with the given API key and optional body.
     *
     * @param string $endpoint The endpoint to call. Must be either "usage" or "translate".
     * @param string|null $body The optional body to send with the request.
     * @return array|string The response data from the endpoint, or null if the endpoint is invalid.
     */
    public static function callEndpoint(string $endpoint, ?string $apiKey = null, ?string $body = null, ?bool $text = false): array | string
    {
        if(!$apiKey) {
            $apiKey = TranslationHelper::getApiKey();
        }
        $url = self::createApiUrl($endpoint);

        if ($endpoint === 'usage') {
            return self::requestUsage($apiKey, $url);
        }
        if ($endpoint === 'translate') {
            return self::requestTranslation($apiKey, $url, $body, $text);
        }
        return [];
    }

    /**
     * Creates an API URL based on the given endpoint.
     *
     * @param string $endpoint The endpoint to create the URL for.
     * @return string The API URL.
     * @throws \RuntimeException If the endpoint is invalid.
     */
    private static function createApiUrl(string $endpoint): string
    {
        if (array_key_exists($endpoint, self::$API_URLS)) {
            return self::$API_URLS['base'] . self::$API_URLS[$endpoint];
        }
        throw new \RuntimeException('Invalid endpoint: ' . $endpoint);
    }

    /**
     * Sends a request to the usage endpoint and returns the usage data.
     *
     * @param string|null $apiKey The API key for authorization.
     * @param string $url The API URL.
     * @return array The usage data.
     */
    private static function requestUsage(?string $apiKey, string $url): array
    {
        if ($apiKey === null | $apiKey === '') {
            return [
                'charLeft' => 0,
                'charLimit' => 0,
                'noKeyErr' => true
            ];
        }
        $authHeader = ['Authorization' => 'Api-Key ' . $apiKey];
        $options = ['headers' => $authHeader];
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $response = $requestFactory->request($url, 'GET', $options);

        self::validateResponse($response);

        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        $charLimit = $data['character_limit'];
        $charSpent = $data['character_spent'];
        $charLeft = max($charLimit - $charSpent, 0);

        return [
            'charLeft' => $charLeft,
            'charLimit' => $charLimit,
            'noKeyErr' => false
        ];
    }

    /**
     * Sends a request to the translation endpoint and returns the translation data.
     *
     * @param string $apiKey The API key for authorization.
     * @param string $url The API URL.
     * @param string $textToTranslate The text to be translated.
     * @return string The translated text.
     * @throws \RuntimeException If the response is invalid.
     */
    private static function requestTranslation(string $apiKey, string $url, string $textToTranslate, bool $text): string
    {
        //Prevent header to be translated with html tags as TYPO3 tt_content header can only render plain text
        $inputTextType = $text ? 'plain_text' : 'html';

        $headers = [
            'Authorization' => 'Api-Key ' . $apiKey,
            'Content-Type' => 'application/json'
        ];
        $requestBody = [
            'input_text' => $textToTranslate,
            'user' => 'user',
            'input_text_type' => $inputTextType,
            'output_language_level' => 'easy',
            'separator' => 'hyphen',
            'embolden_negative' => false
        ];
        $options = [
            'headers' => $headers,
            'body' => json_encode($requestBody)
        ];

        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $response = $requestFactory->request($url, 'POST', $options);

        self::validateResponse($response);
        $responseData = json_decode($response->getBody()->getContents(), true);
        return $responseData['translated_text'];
    }

    /**
     * Validates the API response.
     *
     * @param ResponseInterface $response The API response.
     * @throws \RuntimeException If the response is invalid.
     */
    private static function validateResponse(ResponseInterface $response): void
    {
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Returned status code is ' . $response->getStatusCode());
        }

        if (stripos($response->getHeaderLine('Content-Type'), 'application/json') === false) {
            throw new \RuntimeException('Returned content type is not JSON, but ' . $response->getHeaderLine('Content-Type'));
        }
    }
}
