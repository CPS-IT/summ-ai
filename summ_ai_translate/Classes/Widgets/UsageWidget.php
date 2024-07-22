<?php

namespace THB\SummAiTranslate\Widgets;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\Widgets\AdditionalCssInterface;
use TYPO3\CMS\Dashboard\Widgets\AdditionalJavaScriptInterface;
use TYPO3\CMS\Dashboard\Widgets\RequestAwareWidgetInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

class UsageWidget implements WidgetInterface, RequestAwareWidgetInterface, AdditionalCssInterface
{

    private ServerRequestInterface $request;
    private static string $API_KEY = '4O2W9THJ.qj0Z9UyKV2RqquoTMFgdhpVivttooNxj';
    private static string $API_URL_BASE = 'https://backend.summ-ai.com';
    private static string $API_URL_USAGE = '/api/v1/translation/usage/';

    public function __construct(
        WidgetConfigurationInterface $configuration,
        StandaloneView               $view,
        array                        $options = []
    )
    {
        $this->view = $view;
        $this->options = $options;
        $this->configuration = $configuration;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * Renders the widget content.
     *
     * This function sets the template root path for the view and assigns the template 'Widget/UsageWidget'.
     * It then retrieves the configuration for the 'summ_ai_translate' extension and assigns the 'cache'
     * directory to the variable $config. The function calls the fetchUsage() method to get the usage
     * request and assigns the result to the variable $usageRequest. Finally, it assigns the print_r
     * output of $usageRequest to the 'config' variable in the view and returns the rendered view.
     *
     * @return string The rendered widget content.
     */
    public function renderWidgetContent(): string
    {
        $this->view->setTemplateRootPaths([
            GeneralUtility::getFileAbsFileName('EXT:summ_ai_translate/Resources/Private/Templates/')
        ]);
        $this->view->setTemplate('Widget/UsageWidget');

        $config = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(
            'summ_ai_translate',
            'directories',
        )['cache'];
        $usageRequest = $this->fetchUsage();
        //$this->view->assign('config', print_r($usageRequest, true));
        $this->view->assign('charLeft', $usageRequest['charLeft']);
        $this->view->assign('charLimit', $usageRequest['charLimit']);
        return $this->view->render();
    }

    /**
     * Retrieves the API key.
     *
     * @return string
     */
    private function getApiKey(): string
    {
        return self::$API_KEY;
    }

    /**
     * Fetches the current usage from the API, calculates the remaining characters,
     * and returns a formatted string indicating the characters left out of the total limit.
     *
     * @return string The formatted string indicating the characters left out of the total limit.
     */
    protected function fetchUsage(): array
    {
        $authHeader = ['Authorization' => 'Api-Key ' . $this->getApiKey()];
        $options = ['headers' => $authHeader];

        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $response = $requestFactory->request(
            self::$API_URL_BASE . self::$API_URL_USAGE,
            'GET',
            $options
        );

        $this->validateResponse($response);

        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        $charLimit = $data['character_limit'];
        $charSpent = $data['character_spent'];

        $charLeft = $charLimit - $charSpent;

        //return "Noch $charLeft von $charLimit Zeichen übrig.";
        return [
            'charLeft' => $charLeft,
            'charLimit' => $charLimit
        ];
    }

    /**
     * Validates the response from an HTTP request.
     *
     * This function checks the status code and content type of the response.
     * If the status code is not 200, it throws a RuntimeException with the
     * message 'Returned status code is {status code}'. If the content type is
     * not 'application/json', it throws a RuntimeException with the message
     * 'Returned content type is not JSON, but {content type}'.
     *
     * @param mixed $response The response object to validate.
     * @return void
     * @throws \RuntimeException If the status code is not 200 or the content type is not 'application/json'.
     */
    private function validateResponse($response): void
    {
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                'Returned status code is ' . $response->getStatusCode()
            );
        }

        if ($response->getHeaderLine('Content-Type') !== 'application/json') {
            throw new \RuntimeException(
                'Returned content type is not JSON, but ' . $response->getHeaderLine('Content-Type')
            );
        }
    }

    /**
     * Retrieves the options array.
     *
     * @return array The options array.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Retrieves an array of CSS files for the widget.
     *
     * @return array An array containing the path to the CSS file for the widget.
     */
    public function getCssFiles(): array
    {
        return ['EXT:summ_ai_translate/Resources/Public/Css/Widget.css'];
    }
}
