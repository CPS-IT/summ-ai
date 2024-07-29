<?php

namespace THB\SummAiTranslate\Widgets\Provider;

use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;
use THB\SummAiTranslate\Utility\RequestHelper;

class UsageDataProvider implements ChartDataProviderInterface
{
    /**
     * Retrieves the chart data for the usage of the API.
     * This function calls the `RequestHelper::callEndpoint('usage')` method to get the usage data.
     * If the `noKeyErr` property of the usage data is true, it returns an array with info
     * about missing api key.
     *
     * @return array The chart data for the usage of the API.
     */
    public function getChartData(): array
    {
        $usageData = RequestHelper::callEndpoint('usage');
        if ($usageData['noKeyErr']) {
            return [
                'labels' => ['Kein API Schlüssel hinterlegt!'],
                'datasets' => ['data' => [0]],
                'backgroundColor' => ['#374d5d']
            ];
        }
        return [
            'labels' => ['Zeichen übrig', 'Zeichen verbraucht'],
            'datasets' => [
                [
                    'data' => [
                        $usageData['charLeft'],
                        max(0,$usageData['charLimit'] - $usageData['charLeft'])
                    ],
                    'backgroundColor' => ['#374d5d', '#ff8700']
                ]
            ],
        ];
    }
}
