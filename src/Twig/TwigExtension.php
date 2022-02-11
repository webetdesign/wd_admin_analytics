<?php

namespace WebEtDesign\AnalyticsBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use WebEtDesign\AnalyticsBundle\Enum\BlockStartEnum;
use WebEtDesign\AnalyticsBundle\Services\Analytics;

class TwigExtension extends AbstractExtension
{

    /**
     * @var Analytics $analytics
     */
    public $analytics;

    /**
     * @var EntityManagerInterface 
     */
    public $em;

    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * ApiExtension constructor.
     */
    public function __construct(Analytics $analytics, EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->analytics = $analytics;
        $this->em = $em;
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('view_for_path', [$this, 'getPathView']),
            new TwigFunction('select_start', [$this, 'getStartChoices']),
            new TwigFunction('analytics_available_newsletters', [$this, 'getNewslettersChoices']),
        ];
    }

    public function getPathView($site_id, $path, $start = null){
        try{
            return $start ? $this->analytics->getPage($site_id, $path, $start) : $this->analytics->getPage($site_id, $path);
        }catch (\Exception $e){
            return 0;
        }
    }

    public function getStartChoices(){
        return BlockStartEnum::$choices;
    }
    
    public function getNewslettersChoices (){
        if (
            !class_exists("WebEtDesign\NewsletterBundle\Entity\NewsletterLog")
            || ($this->container->has('wd_newsletter.enable_log') && !$this->container->getParameter('wd_newsletter.enable_log'))
        ) return [];
        
        return $this->em->getRepository("WebEtDesign\NewsletterBundle\Entity\Newsletter")->findAvailableAnalytics();
    }
}
