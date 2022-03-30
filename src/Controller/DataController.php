<?php
namespace WebEtDesign\AnalyticsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use WebEtDesign\AnalyticsBundle\Services\Analytics;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Cache\CacheItem;

class DataController extends AbstractController
{
    private Analytics $analytics;
    private CacheInterface $cache;

    /**
     * DataController constructor.
     * @param Analytics $analytics
     * @param string $base_view_id
     */
    public function __construct(CacheInterface $cache,Analytics $analytics, string $base_view_id)
    {
        $this->analytics = $analytics;
        $this->base_view_id = $base_view_id;
        $this->cache = $cache;
    }

    public function basic(Request $request){
        $method = 'get' . ucfirst($request->request->get('method', 'Devices'));
        $site_id = $request->request->get('site_id');
        $start = $request->request->get('start');

        $key = 'dashboard_' . strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $start))) . '_' . $method . '_' . $site_id;

        $cache_value = null;
        $data = null;

        try {
            $cache_value = $this->cache->getItem($key);
            if ($cache_value->isHit()) {
                $data = $cache_value->get();
            }
        } catch (InvalidArgumentException $e) {
        }

        if (!$data){
            $data = $this->analytics->$method($site_id, $start);
            if ($cache_value instanceOf CacheItem){
                $cache_value->expiresAfter(86400);
                $cache_value->set($data);
                $this->cache->save($cache_value);
            }
        }

        return new JsonResponse($data);
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
