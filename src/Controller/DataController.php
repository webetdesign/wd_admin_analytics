<?php
namespace WebEtDesign\AnalyticsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use WebEtDesign\AnalyticsBundle\Services\Analytics;

class DataController extends AbstractController
{
    /**
     * @var Analytics
     */
    private $analytics;

    /**
     * DataController constructor.
     * @param Analytics $analytics
     */
    public function __construct(Analytics $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * @param Request $request
     */
    public function doughnut(Request $request){
        $method = 'get' . ucfirst($request->request->get('method', 'Devices'));
        return new JsonResponse($this->analytics->$method(
            $request->request->get('site_id'),
            $request->request->get('start'))
        );
    }


}
