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
     * @param string $base_view_id
     */
    public function __construct(Analytics $analytics, string $base_view_id)
    {
        $this->analytics = $analytics;
        $this->base_view_id = $base_view_id;
    }

    /**
     * @param Request $request
     */
    public function basic(Request $request){
        $method = 'get' . ucfirst($request->request->get('method', 'Devices'));
        return new JsonResponse($this->analytics->$method(
            $request->request->get('site_id'),
            $request->request->get('start'))
        );
    }

    /**
     * @param Request $request
     */
    public function users(Request $request){
        $method = 'get' . ucfirst($request->request->get('method', 'UserWeek'));
        return new JsonResponse($this->analytics->$method(
            $request->request->get('site_id'))
        );
    }

    public function page(Request $request){
        $path = $request->request->get('path', null);
        $start = $request->request->get('start', null);
        $details = $request->request->get('details', false);

        try{
            if ($details){
                $views = $start ? $this->analytics->getPageDetails($this->base_view_id, $path, $start) : $this->analytics->getPageDetails($this->base_view_id, $path);
            }else{
                $views = $start ? $this->analytics->getPage($this->base_view_id, $path, $start) : $this->analytics->getPage($this->base_view_id, $path);
            }
        }catch (\Exception $e){
            $views = 0;
        }

        return new JsonResponse([
            'views' => $views
        ]);
    }

}
