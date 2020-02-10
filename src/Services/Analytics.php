<?php

namespace WebEtDesign\AnalyticsBundle\Services;

use DateTime;
use Google_Client;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_Dimension;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_AnalyticsReporting_GetReportsResponse as Google_Response;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_Report as Google_Report;
use Google_Service_AnalyticsReporting_ReportRequest;
use Google_Service_AnalyticsReporting_ReportRow;
use MediaFigaro\GoogleAnalyticsApi\Service\GoogleAnalyticsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Google_Service_AnalyticsReporting_OrderBy;

class Analytics
{
    /**
     * @var GoogleAnalyticsService
     */
    private $analyticsService;

    /**
     * @var array
     */
    private $viewIds;

    /**
     * @var Google_Service_AnalyticsReporting
     */
    private $analyticsReport;

    /**
     * @var Google_Client
     */
    private $client;
    /**
     * @var int
     */
    public $maxPage;

    /**
     * Analytics constructor.
     * @param GoogleAnalyticsService $analyticsService
     * @param $ids
     * @param int $maxPage
     */
    public function __construct(GoogleAnalyticsService $analyticsService, $ids, int $maxPage = 10)
    {
        $this->analyticsService = $analyticsService;
        $this->viewIds          = $ids;
        $this->client           = $analyticsService->getClient();
        $this->analyticsReport  = new Google_Service_AnalyticsReporting($this->client);
        $this->maxPage          = $maxPage;
    }

    /**
     * @param string $metric_name
     * @param string $dimension_name
     * @param string $start
     * @param string $end
     * @return array
     */
    public function getBasicChart($metric_name, $dimension_name, $start, $end = "yesterday", $max = null)
    {
        $max = $max ? $max : $this->maxPage;

        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate(date('Y-m-d', strtotime($start)));
        $dateRange->setEndDate(date('Y-m-d', strtotime($end)));

        $metric = new Google_Service_AnalyticsReporting_Metric();
        $metric->setExpression("ga:" . $metric_name);
        $metric->setAlias(ucfirst($metric_name));

        $dimension = new Google_Service_AnalyticsReporting_Dimension();
        $dimension->setName("ga:" . $dimension_name);

        $actual = $this->makeRequest([$metric], [$dimension], [$dateRange], "formatDataChart", $max);

        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate(
            date('Y-m-d', strtotime(date('Y-m-d', strtotime($start)) . $start))
        );
        $dateRange->setEndDate(date('Y-m-d', strtotime(date('Y-m-d', strtotime($start)) . ' -1 day')));

        $history = $this->makeRequest([$metric], [$dimension], [$dateRange], "formatDataChart", $max);

        foreach ($actual as $siteId => $fields) {
            foreach ($fields['labels'] as $key => $item) {
                if (!in_array($item, $history[$siteId]['labels'])) {
                    return $actual;
                } else {
                    $id                            = array_search($item, $history[$siteId]['labels']);
                    $actual[$siteId]['diff'][$key] = $actual[$siteId]['percents'][$key] - $history[$siteId]['percents'][$id];
                }
            }
        }

        return $actual;
    }

    /**
     * Return Number or Users per Browser
     * @param string $start
     * @return array
     */
    public function getBrowsers($start = "30 days ago")
    {
        $data = $this->getBasicChart("users", "browser", $start);

        foreach ($data as $row_key => $row) {
            foreach ($row["labels"] as $key => $label) {
                $row["labels"][$key] = [$label, null];
                $data[$row_key]      = $row;
            }
        }

        return $data;
    }

    /**
     * Return the number of users each day for periods :
     *      [monday -> today] this week
     *      [monday -> sunday] last week
     * @return array
     */
    public function getUserWeek()
    {
        $thisWeek = new Google_Service_AnalyticsReporting_DateRange();
        // week - 1
        $thisWeek->setStartDate(date('Y-m-d', strtotime('-7 days')));
        // today
        $thisWeek->setEndDate(date('Y-m-d', strtotime('-1 days')));

        $lastWeek = new Google_Service_AnalyticsReporting_DateRange();
        // week - 2
        $lastWeek->setStartDate(date('Y-m-d', strtotime('-14 days')));
        // week - 1
        $lastWeek->setEndDate(date('Y-m-d', strtotime('-8 days')));

        $metric = new Google_Service_AnalyticsReporting_Metric();
        $metric->setExpression("ga:users");
        $metric->setAlias("sessions");

        $dimension_1 = new Google_Service_AnalyticsReporting_Dimension();
        $dimension_1->setName("ga:date");

        $dimension_2 = new Google_Service_AnalyticsReporting_Dimension();
        $dimension_2->setName("ga:nthDay");

        $response_this_week = $this->makeRequest([$metric], [$dimension_1, $dimension_2], [$thisWeek]);
        $response_last_week = $this->makeRequest([$metric], [$dimension_1, $dimension_2], [$lastWeek]);

        $response_this_week = $this->getDiffForWeek($response_this_week);
        $response_last_week = $this->getDiffForWeek($response_last_week);

        foreach ($response_this_week as $key => $row) {
            $data[$key] = [
                "labels" => $this->getLabelsWeek(),
                "values" => [
                    "this_week" => $response_this_week[$key],
                    "last_week" => $response_last_week[$key]
                ]
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getLabelsWeek()
    {
        $days   = ["Lun.", "Mar.", "Mer.", "Jeu.", "Ven.", "Sam.", "Dim."];
        $labels = [];

        for ($i = date('N', strtotime('now')); $i < 8; $i++) {
            array_push($labels, $days[$i - 1]);
        }

        $i = 0;
        while (count($labels) != count($days)) {
            array_push($labels, $days[$i]);
            $i++;
        }

        return $labels;
    }

    /**
     * add 0 to values table if a day is not defined
     * @param array $array
     * @return array
     */
    private function getDiffForWeek($array)
    {
        $previous = null;
        // append 0 in values array if days are missing because API not return days without session
        foreach ($array as $row_key => $row) {
            foreach ($row['labels'] as $key => $item) {
                if ($previous && date('d-m-Y', strtotime($previous . " 1 day")) != date('d-m-Y', strtotime($item))) {
                    $diff   = abs(strtotime($previous) - strtotime($item));
                    $years  = floor($diff / (365 * 60 * 60 * 24));
                    $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $days   = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24)) - 1;

                    for ($i = 0; $i < $days; $i++) {
                        array_splice($row['values'], $key, 0, 0);
                    }
                }
                $previous = $item;
            }
            $array[$row_key] = $row["values"];
        }

        return $array;
    }

    /**
     * Return the number of users each month for periods :
     *      [january -> today] this year
     *      [january -> december] last year
     * @return array
     */
    public function getUserYear()
    {
        $thisYear = new Google_Service_AnalyticsReporting_DateRange();
        // this monday
        $thisYear->setStartDate(date('Y-m-d', strtotime('first day of january this year')));
        // today
        $thisYear->setEndDate(date('Y-m-d', time()));

        $lastYear = new Google_Service_AnalyticsReporting_DateRange();
        // last monday
        $lastYear->setStartDate(date('Y-m-d', strtotime('first day of january last year')));
        //last sunday
        $lastYear->setEndDate(date('Y-m-d', strtotime('last day of december last year')));

        $metric = new Google_Service_AnalyticsReporting_Metric();
        $metric->setExpression("ga:users");
        $metric->setAlias("sessions");

        $dimension_1 = new Google_Service_AnalyticsReporting_Dimension();
        $dimension_1->setName("ga:month");

        $dimension_2 = new Google_Service_AnalyticsReporting_Dimension();
        $dimension_2->setName("ga:nthMonth");

        $response_this_year = $this->makeRequest([$metric], [$dimension_1, $dimension_2], [$thisYear]);
        $response_last_year = $this->makeRequest([$metric], [$dimension_1, $dimension_2], [$lastYear]);

        $response_this_year = $this->getDiffForYear($response_this_year);
        $response_last_year = $this->getDiffForYear($response_last_year);

        foreach ($response_this_year as $key => $row) {
            $data[$key] = [
                "labels" => ['Jan.', 'Fev.', 'Mar.', 'Avr.', 'Mai.', 'Jui.', 'Juil.', 'Aou.', 'Sep.', 'Oct.', 'Nov.', 'Dec.'],
                "values" => [
                    "last_year" => $response_last_year[$key],
                    "this_year" => $response_this_year[$key]
                ]
            ];
        }

        return $data;
    }

    /**
     * add 0 to values table if a month is not defined
     * @param array $array
     * @return array
     */
    private function getDiffForYear($array)
    {
        $previous = "00";

        // append 0 in values array if days are missing because API not return days without session
        foreach ($array as $row_key => $row) {
            foreach ($row['labels'] as $key => $item) {
                if (intval($previous) + 1 != intval($item)) {
                    $diff = intval($item) - intval($previous) - 1;
                    for ($i = 0; $i < $diff; $i++) {
                        array_splice($row['values'], $key, 0, 0);
                    }
                }
                $previous = $item;
            }
            $array[$row_key] = $row["values"];
        }

        return $array;
    }

    /**
     * Return number of users per source
     * @param string $start
     * @return array
     */
    public function getSources($start = "first day of january this year")
    {
        $data = $this->getBasicChart("users", "channelGrouping", $start);

        foreach ($data as $row_key => $row) {
            foreach ($row["labels"] as $key => $label) {
                if ($label == "(none)") {
                    $row["labels"][$key] = ["Direct", null];
                } else {
                    $row["labels"][$key] = [$label, null];
                }
                $data[$row_key] = $row;
            }
        }

        return $data;
    }

    /**
     * Return of users per device
     * @param string $start
     * @return array
     */
    public function getDevices($start = "30 days ago")
    {
        $data = $this->getBasicChart("visits", "deviceCategory", $start);

        foreach ($data as $row_key => $row) {
            foreach ($row["labels"] as $key => $label) {
                switch (strtolower($label)) {
                    case 'mobile':
                        $row["labels"][$key] = ['Mobile', 'fa-4x fa fa-mobile'];
                        break;
                    case 'desktop':
                        $row["labels"][$key] = ["Ordinateur", 'fa fa-desktop fa-4x'];
                        break;
                    case 'tablet':
                        $row["labels"][$key] = ["Tablette", 'fa-lg fa fa-tablet fa-4x fa-rotate-90'];
                        break;
                }
                $data[$row_key] = $row;
            }
        }

        return $data;
    }

    /**
     * Return of users per country
     * @param string $start
     * @return array
     */
    public function getCountries($start = "first day of january this year")
    {
        $response = $this->getBasicChart("users", "country", $start);
        $data     = [];

        foreach ($response as $row_key => $row) {
            $data_row = [['Country', 'Popularity']];

            foreach ($row["labels"] as $key => $item) {
                $data_row[] = [
                    $row["labels"][$key],
                    intval($row["values"][$key])
                ];
            }
            $data[$row_key] = $data_row;
        }

        return $data;
    }

    public function getUsers($start = "30 days ago")
    {
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate(date('Y-m-d', strtotime($start)));
        $dateRange->setEndDate(date('Y-m-d', strtotime("today")));

        $metric = new Google_Service_AnalyticsReporting_Metric();
        $metric->setExpression("ga:users");
        $metric->setAlias("Users");

        $d1 = new Google_Service_AnalyticsReporting_Dimension();
        $d1->setName('ga:hour');

        $d2 = new Google_Service_AnalyticsReporting_Dimension();
        $d2->setName('ga:dayOfWeekName');

        $d3 = new Google_Service_AnalyticsReporting_Dimension();
        $d3->setName('ga:day');

        $data = $this->makeRequest([$metric], [$d1, $d2, $d3], [$dateRange], "formatDataUsers");

        return $data;
    }

    public function getPages($start = "30 days ago"){
        return $this->getBasicChart("pageviews", "pagePath", $start, 'yesterday', 10);
    }

    private function makeRequest(array $metrics, array $dimensions, array $dates, $method = "formatDataChart", $max = null)
    {
        $data = [];
        foreach ($this->viewIds as $id) {
            // Create the ReportRequest object.
            $request = new Google_Service_AnalyticsReporting_ReportRequest();
            $request->setViewId(strval($id));
            $request->setMetrics($metrics);
            $request->setDimensions($dimensions);
            $request->setDateRanges($dates);

            if (count($metrics) == 1) {
                $order = new Google_Service_AnalyticsReporting_OrderBy();
                $order->setFieldName($metrics[0]->getExpression());
                $order->setOrderType("VALUE");
                $order->setSortOrder("DESCENDING");
                $request->setOrderBys($order);
            }

            if ($max) {
                $request->setPageSize($max);
            }

            $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
            $body->setReportRequests([$request]);
            $response = $this->analyticsReport->reports->batchGet($body);

            $data[$id] = $this->$method($response);
        }

        return $data;
    }

    private function formatDataChart(Google_Response $response)
    {
        $data = [
            "labels"   => [],
            "values"   => [],
            "total"    => 0,
            'percents' => [],
            'diff'     => []
        ];

        /** @var Google_Report $report */
        foreach ($response->getReports() as $report) {
            /** @var Google_Service_AnalyticsReporting_ReportRow $row */
            $data["total"] = $report->getData()->getTotals()[0]->getValues()[0];
            foreach ($report->getData()->getRows() as $row) {
                $value = $row->getMetrics()[0]->getValues()[0];
                $prct  = round(intval($value) / intval($data['total']), 4) * 100;

                array_push($data["values"], $value);
                array_push($data["percents"], $prct);
                array_push($data["labels"], ucfirst($row->getDimensions()[0]));
            }
        }

        return $data;
    }

    private function formatDataUsers(Google_Response $response)
    {
        $data = [
            "labels" => [],
            "values" => []
        ];

        $days   = ["Monday" => 0, "Tuesday" => 0, "Wednesday" => 0, "Thursday" => 0, "Friday" => 0, "Saturday" => 0, "Sunday" => 0];
        $visits = [];

        for ($i = 0; $i < 24; $i++) {
            $visits[] = $days;
        }

        /** @var Google_Report $report */
        foreach ($response->getReports() as $report) {
            /** @var Google_Service_AnalyticsReporting_ReportRow $row */
            foreach ($report->getData()->getRows() as $row) {
                $visits[intval($row->getDimensions()[0])][ucfirst($row->getDimensions()[1])] += intval($row->getMetrics()[0]->getValues()[0]);
            }

            if ($report->getData()->getMaximums()) {
                $data["max"] = $report->getData()->getMaximums()[0]->getValues()[0];
            }
        }

        $data["values"] = $visits;

        return $data;
    }

}
