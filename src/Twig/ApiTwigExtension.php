<?php

namespace WebEtDesign\AnalyticsBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use WebEtDesign\AnalyticsBundle\Services\Analytics;

class ApiTwigExtension extends AbstractExtension
{

    /**
     * @var Analytics $analytics
     */
    public $analytics;

    /**
     * ApiExtension constructor.
     */
    public function __construct(Analytics $analytics)
    {
        $this->analytics = $analytics;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('view_for_path', [$this, 'getPathView']),
        ];
    }

    public function getPathView($site_id, $path, $start = null){
        try{
            return $start ? $this->analytics->getPage($site_id, $path, $start) : $this->analytics->getPage($site_id, $path);
        }catch (\Exception $e){
            return 0;
        }
    }
}
