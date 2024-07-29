<?php
declare(strict_types=1);

namespace THB\SummAiTranslate\Widgets;

use Psr\Http\Message\ServerRequestInterface;
use THB\SummAiTranslate\Utility\RequestHelper;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\Widgets\AdditionalCssInterface;
use TYPO3\CMS\Dashboard\Widgets\RequestAwareWidgetInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

class UsageWidget implements WidgetInterface, RequestAwareWidgetInterface, AdditionalCssInterface
{
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
        $usageRequest = RequestHelper::callEndpoint('usage');
        $this->view->assign('charLeft', $usageRequest['charLeft']);
        $this->view->assign('charLimit', $usageRequest['charLimit']);
        $this->view->assign('noKeyErr', $usageRequest['noKeyErr']);
        return $this->view->render();
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
