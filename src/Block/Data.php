<?php
/**
 * Created by PhpStorm.
 * User: benjamin
 * Date: 16/04/2019
 * Time: 16:38
 */

namespace WebEtDesign\AnalyticsBundle\Block;

use Sonata\BlockBundle\Block\AbstractBlockService;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WebEtDesign\AnalyticsBundle\Services\Analytics;

class Data extends AbstractBlockService
{
    /**
     * @var Analytics
     */
    private $analyticsService;

    /**
     * @param string $name
     * @param EngineInterface $templating
     * @param Analytics $analyticsService
     */
    public function __construct($name, EngineInterface $templating, Analytics $analyticsService)
    {
        parent::__construct($name, $templating);

        $this->analyticsService = $analyticsService;
    }

    /**
     * @param BlockContextInterface $blockContext
     * @param Response|null $response
     * @return mixed
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $settings = $blockContext->getSettings();

        $blocks = [];

        $this->analyticsService->maxPage = 5;

        foreach ($settings["analytics"] as $block) {
            $block_name = array_key_first($block);

            $start = key_exists('start', $block[$block_name]) ? $block[$block_name]["start"] : null;
            $icon = key_exists('icon', $block[$block_name]) ? $block[$block_name]["icon"] : null;
            $size = key_exists('size', $block[$block_name]) ? $block[$block_name]["size"] : null;

            $method = "get" . ucfirst($block_name);
            $row = [];
            $row["template"] = "@WDAdminAnalytics/" . $block_name . ".html.twig";
            $row["name"] = $block_name;
            $row["icon"] = $icon;
            $row["size"] = $size;
            $row["start"] = $start;
            $blocks[] = $row;
        }


        return $this->renderPrivateResponse("@WDAdminAnalytics/render.html.twig", [
            'blocks' => $blocks,
        ], $response);
    }

        public function getName()
    {
        return 'Admin Analytics';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureSettings(OptionsResolver $resolver)
    {

        $resolver->setDefaults([
            "analytics" => []
        ]);
        $resolver->setAllowedTypes('analytics', ['array', 'null']);

    }
}
