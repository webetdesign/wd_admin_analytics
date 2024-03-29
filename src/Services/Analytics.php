<?php

namespace WebEtDesign\AnalyticsBundle\Services;

use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_Dimension;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_AnalyticsReporting_GetReportsResponse as Google_Response;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_Report as Google_Report;
use Google_Service_AnalyticsReporting_ReportRequest;
use Google_Service_AnalyticsReporting_OrderBy;
use Google_Service_AnalyticsReporting_ReportRow;
use JetBrains\PhpStorm\Pure;

class Analytics
{

    private GoogleAnalyticsService $analyticsService;
    private EntityManagerInterface $em;
    private array $viewIds;
    private Google_Service_AnalyticsReporting $analyticsReport;
    private Google_Client $client;
    public int $maxPage;
    public Slugify $slugify;

    /**
     * Analytics constructor.
     * @param GoogleAnalyticsService $analyticsService
     * @param EntityManagerInterface $em
     * @param $ids
     * @param int $maxPage
     */
    public function __construct(GoogleAnalyticsService $analyticsService, EntityManagerInterface $em, $ids, int $maxPage = 10)
    {
        $this->analyticsService = $analyticsService;
        $this->em = $em;
        $this->viewIds          = $ids;
        $this->client           = $analyticsService->getClient();
        $this->analyticsReport  = new Google_Service_AnalyticsReporting($this->client);
        $this->maxPage          = $maxPage;
        $this->slugify          = new Slugify();
    }

    public function getBasicChart(string $metric_name, string $dimension_name, string $start, string $site_id, string $end = "yesterday", $max = null): array
    {
        $max = $max ?: $this->maxPage;

        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate(date('Y-m-d', strtotime($start)));
        $dateRange->setEndDate(date('Y-m-d', strtotime($end)));

        $metric = new Google_Service_AnalyticsReporting_Metric();
        $metric->setExpression("ga:" . $metric_name);
        $metric->setAlias(ucfirst($metric_name));

        $dimension = new Google_Service_AnalyticsReporting_Dimension();
        $dimension->setName("ga:" . $dimension_name);

        $actual = $this->makeRequest([$metric], [$dimension], [], [$dateRange], $site_id, "formatDataChart", $max, true);

        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate(
            date('Y-m-d', strtotime(date('Y-m-d', strtotime($start)) . $start))
        );
        $dateRange->setEndDate(date('Y-m-d', strtotime(date('Y-m-d', strtotime($start)) . ' -1 day')));
        $history = $this->makeRequest([$metric], [$dimension], [], [$dateRange], $site_id, "formatDataChart", $max, true);

        foreach ($actual as $siteId => $fields) {
            foreach ($fields['labels'] as $key => $item) {
                if (!in_array($item, $history[$siteId]['labels'])) {
                    return $actual;
                } else {
                    $id                            = array_search($item, $history[$siteId]['labels']);
                    $ac_value                      = intval($actual[$siteId]['values'][$key]);
                    $h_value                       = intval($history[$siteId]['values'][$key]);
                    $actual[$siteId]['diff'][$key] = (round((($ac_value - $h_value) / $h_value) * 100, 4));
                }
            }
        }

        return $actual;
    }

    public function getBrowsers($site_id, $start = "30 days ago"): array
    {
        $data = $this->getBasicChart("users", "browser", $start, $site_id, 'yesterday', 5);

        foreach ($data as $row_key => $row) {
            foreach ($row["labels"] as $key => $label) {
                $icon                = 'fa-2x fa fa-' . $this->slugify->slugify($label);
                $row["labels"][$key] = [$label, $icon];
                $data[$row_key]      = $row;
            }
        }

        return $data;
    }

    /**
     * @param string $site_id
     * Return the number of users each day for periods :
     *      [monday -> today] this week
     *      [monday -> sunday] last week
     * @return array
     */
    public function getUserWeek(string $site_id): array
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

        $response_this_week = $this->makeRequest([$metric], [$dimension_1, $dimension_2], [], [$thisWeek], $site_id);
        $response_last_week = $this->makeRequest([$metric], [$dimension_1, $dimension_2], [], [$lastWeek], $site_id);

        $response_this_week = $this->getDiffForWeek($response_this_week);
        $response_last_week = $this->getDiffForWeek($response_last_week);

        $data = [];
        foreach ($response_this_week as $key => $row) {
            $data[$key] = [
                "labels" => $this->getLabelsWeek(),
                "values" => [
                    "this_week" => $row,
                    "last_week" => $response_last_week[$key]
                ]
            ];
        }

        return $data;
    }

    private function getLabelsWeek(): array
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
    private function getDiffForWeek(array $array): array
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
     * @param string $site_id
     * Return the number of users each month for periods :
     *      [january -> today] this year
     *      [january -> december] last year
     * @return array
     */
    public function getUserYear(string $site_id): array
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

        $response_this_year = $this->makeRequest([$metric], [$dimension_1, $dimension_2], [], [$thisYear], $site_id);
        $response_last_year = $this->makeRequest([$metric], [$dimension_1, $dimension_2], [], [$lastYear], $site_id);

        $response_this_year = $this->getDiffForYear($response_this_year);
        $response_last_year = $this->getDiffForYear($response_last_year);

        foreach ($response_this_year as $key => $row) {
            $data[$key] = [
                "labels" => ['Jan.', 'Fev.', 'Mar.', 'Avr.', 'Mai.', 'Jui.', 'Juil.', 'Aou.', 'Sep.', 'Oct.', 'Nov.', 'Dec.'],
                "values" => [
                    "last_year" => $response_last_year[$key],
                    "this_year" => $row
                ]
            ];
        }

        return $data ?? [];
    }

    /**
     * add 0 to values table if a month is not defined
     * @param array $array
     * @return array
     */
    private function getDiffForYear(array $array): array
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
     * @param string $site_id
     * @param string $start
     * @return array
     */
    public function getSources(string $site_id, string $start = "first day of january this year"): array
    {
        $data = $this->getBasicChart("sessions", "source", $start, $site_id);

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
     * @param string $site_id
     * @return array
     */
    public function getDevices(string $site_id, string $start = "30 days ago"): array
    {
        $data = $this->getBasicChart("sessions", "deviceCategory", $start, $site_id);

        foreach ($data as $row_key => $row) {
            foreach ($row["labels"] as $key => $label) {
                $row["labels"][$key] = match (strtolower($label)) {
                    'mobile' => ['Mobile', 'fa-4x fa fa-mobile'],
                    'desktop' => ["Ordinateur", 'fa fa-desktop fa-4x'],
                    'tablet' => ["Tablette", 'fa-lg fa fa-tablet fa-4x fa-rotate-90'],
                };
                $data[$row_key] = $row;
            }
        }

        return $data;
    }

    /**
     * Return number of users per country for map
     * @param $site_id
     * @param string $start
     * @param int $max
     * @return array
     */
    public function getCountriesMap($site_id, string $start = "first day of january this year", $max = 30): array
    {
        $response = $this->getBasicChart("users", "country", $start, $site_id, 'yesterday', $max);
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

    /**
     * Return number of users per country for chart
     * @param string $site_id
     * @param string $start
     * @return array
     */
    public function getCountriesChart(string $site_id, string $start = "first day of january this year"): array
    {
        return $this->getBasicChart("users", "country", $start, $site_id, 'yesterday', 10);
    }

    /**
     * @param string $site_id
     * @param string $start
     * @return array
     */
    public function getUsers(string $site_id, string $start = "30 days ago"): mixed
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

        return $this->makeRequest([$metric], [$d1, $d2, $d3], [], [$dateRange], $site_id, "formatDataUsers");
    }

    public function getPage($site_id, $path, $start = '1 month ago')
    {
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate(date('Y-m-d', strtotime($start)));
        $dateRange->setEndDate(date('Y-m-d', strtotime('yesterday')));

        $metric = new Google_Service_AnalyticsReporting_Metric();
        $metric->setExpression("ga:uniquePageviews");
        $metric->setAlias(ucfirst('uniquePageviews'));

        $dimension = new Google_Service_AnalyticsReporting_Dimension();
        $dimension->setName("ga:pagePath");

        $filter = new \Google_Service_AnalyticsReporting_DimensionFilter();
        $filter->setDimensionName('ga:pagePath');
        $filter->setOperator('EXACT');
        $filter->setExpressions([$path]);

        $clause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
        $clause->setFilters([$filter]);
        $clause->setOperator('OR');

        $actual = $this->makeRequest([$metric], [$dimension], [$clause], [$dateRange], $site_id, "formatDataChart", 10);

        return isset($actual[$site_id]) && isset($actual[$site_id]['total']) ? $actual[$site_id]['total'] : 0;
    }

    public function getPageDetails($site_id, $path, $start = '1 month ago')
    {
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate(date('Y-m-d', strtotime($start)));
        $dateRange->setEndDate(date('Y-m-d', strtotime('yesterday')));

        $metric = new Google_Service_AnalyticsReporting_Metric();
        $metric->setExpression("ga:uniquePageviews");
        $metric->setAlias(ucfirst('uniquePageviews'));

        $d1 = new Google_Service_AnalyticsReporting_Dimension();
        $d1->setName("ga:pagePath");
        $d2 = new Google_Service_AnalyticsReporting_Dimension();
        $d2->setName('ga:date');

        $filter = new \Google_Service_AnalyticsReporting_DimensionFilter();
        $filter->setDimensionName('ga:pagePath');
        $filter->setOperator('EXACT');
        $filter->setExpressions([$path]);

        $clause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
        $clause->setFilters([$filter]);
        $clause->setOperator('OR');

        $actual = $this->makeRequest([$metric], [ $d2, $d1], [$clause], [$dateRange], $site_id, "formatDataPageDetails", 100);

        return $actual[$site_id] ?? [];
    }

    public function makeRequest(array $metrics, array $dimensions, array $dimensions_clause, array $dates, $site_id, $method = "formatDataChart", $max = null, $order = false): array
    {
        // Create the ReportRequest object.
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId(strval($site_id));
        $request->setMetrics($metrics);
        $request->setDimensions($dimensions);
        $request->setDateRanges($dates);
        $request->setDimensionFilterClauses($dimensions_clause);

        if ($order){
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

        $data[$site_id] = $this->$method($response);

        return $data;
    }

    private function formatDataChart(Google_Response $response): array
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
                $percent  = round(intval($value) / intval($data['total']), 4) * 100;

                array_push($data["values"], $value);
                array_push($data["percents"], $percent);
                array_push($data["labels"], ucfirst($row->getDimensions()[0]));
            }
        }

        return $data;
    }

    private function formatDataPageDetails(Google_Response $response): array
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
                $label = (ucfirst($row->getDimensions()[0]));
                $label =
                    substr($label,6) . '/' .
                    substr($label,4,2)
                ;
                array_push($data["labels"], $label);
            }
        }

        return $data;
    }

    /**
     * @param string $start
     * @param string $site_id
     * @return array
     */
    public function getPages(string $site_id, string $start = "30 days ago"): array
    {
        $max = 10;

        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate(date('Y-m-d', strtotime($start)));
        $dateRange->setEndDate(date('Y-m-d', strtotime('yesterday')));

        $metric = new Google_Service_AnalyticsReporting_Metric();
        $metric->setExpression("ga:pageviews");
        $metric->setAlias(ucfirst('pageviews'));

        $metric_2 = new Google_Service_AnalyticsReporting_Metric();
        $metric_2->setExpression("ga:entrances");
        $metric_2->setAlias(ucfirst('entrances'));

        $metric_3 = new Google_Service_AnalyticsReporting_Metric();
        $metric_3->setExpression("ga:exits");
        $metric_3->setAlias(ucfirst('exits'));

        $dimension = new Google_Service_AnalyticsReporting_Dimension();
        $dimension->setName("ga:pagePath");

        return $this->makeRequest([$metric, $metric_2, $metric_3], [$dimension], [], [$dateRange], $site_id, "formatPages", $max, true);
    }

    private function fomatPages(Google_Response $response): array
    {
        $data = [
            "labels" => [],
            "values" => []
        ];

        /** @var Google_Report $report */
        foreach ($response->getReports() as $report) {
            /** @var Google_Service_AnalyticsReporting_ReportRow $row */
            foreach ($report->getData()->getRows() as $row) {
                $label = ucfirst($row->getDimensions()[0]);
                $data["values"][$label] = [];
                foreach ($row->getMetrics()[0]->getValues() as $value) {
                    array_push($data["values"][$label], number_format($value, 0, ',', ' '));
                }
                array_push($data["labels"], $label);
            }
        }

        return $data;
    }

    #[Pure] private function formatDataUsers(Google_Response $response): array
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

    /**
     * @param string $site_id
     * @param ?int $newsletterId
     * @return array
     */
    public function getNewsletter(string $site_id, ?int $newsletterId = null): array
    {
        return [
            $site_id => $this->em->getRepository("WebEtDesign\NewsletterBundle\Entity\NewsletterLog")->getAnalyticsStats($site_id, $newsletterId)
        ];
    }

}
