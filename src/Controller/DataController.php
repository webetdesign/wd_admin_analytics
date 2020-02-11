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
    public function devices(Request $request){
        return new JsonResponse($this->analytics->getDevices($request->request->get('site_id'), $request->request->get('start')));
    }


}
