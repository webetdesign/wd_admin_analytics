<?php
/**
 * Created by PhpStorm.
 * User: benjamin
 * Date: 16/04/2019
 * Time: 16:38
 */

namespace WebEtDesign\AnalyticsBundle\Block;

use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WebEtDesign\AnalyticsBundle\Enum\ConfigTypeEnum;

class Config extends AbstractBlockService
{

    #[Pure] public function __construct(
        $name,
        private string $mapKey,
        private array $view_names,
        private array $view_ids,
        private EntityManagerInterface $em
    )
    {
        parent::__construct($name);
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        $settings = $blockContext->getSettings();

        $diffColor = $this->em->getRepository(\WebEtDesign\AnalyticsBundle\Entity\Config::class)->findOneBy([
            'code' => ConfigTypeEnum::DIFF_COLORS
        ]);
        $colors = $this->em->getRepository(\WebEtDesign\AnalyticsBundle\Entity\Config::class)->findOneBy([
            'code' => ConfigTypeEnum::COLORS
        ]);
        $degradedColor = $this->em->getRepository(\WebEtDesign\AnalyticsBundle\Entity\Config::class)->findOneBy([
            'code' => ConfigTypeEnum::DEGRADED_COLOR
        ]);

        return $this->renderResponse("@WDAdminAnalytics/base.html.twig", [
            'map_key' => $this->mapKey,
            'map_color' => $degradedColor ? $degradedColor->getValue() : $settings['map_color'],
            'users_color' => $degradedColor ? $degradedColor->formatColor($degradedColor->getValue()) : $settings['users_color'],
            'week_colors' => $diffColor ? json_encode($diffColor->getValueFormated()) : json_encode($settings['week_colors']),
            'year_colors' => $diffColor ? json_encode($diffColor->getValueFormated()) : json_encode($settings['year_colors']),
            'colors' => $colors ? json_encode($colors->getValueFormated()) : json_encode($settings['colors']),
            'view_names' => $this->view_names,
            'view_ids' => $this->view_ids
        ], $response);
    }

    public function getName(): string
    {
        return 'Admin Analytics';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'map_key' => null,
            'map_color' => "#0077ae",
            'users_color' => 'rgb(179, 000, 000)',
            'week_colors' => ['rgb(255, 077, 077)', 'rgb(230, 000, 000)'],
            'year_colors' => ['rgb(255, 077, 077)', 'rgb(230, 000, 000)'],
            'colors' => ['rgb(255, 102, 102)', 'rgb(255, 051, 051)', 'rgb(230, 000, 000)', 'rgb(179, 000, 000)', 'rgb(128, 000, 000)'],
            "view_names" => []
        ]);

        $resolver->setAllowedTypes('map_key', ['string', 'null']);
        $resolver->setAllowedTypes('week_colors', ['array', 'null']);
        $resolver->setAllowedTypes('year_colors', ['array', 'null']);
        $resolver->setAllowedTypes('users_color', ['string', 'null']);
        $resolver->setAllowedTypes('map_color', ['string', 'null']);
        $resolver->setAllowedTypes('colors', ['array', 'null']);
        $resolver->setAllowedTypes('view_names', ['array', 'null']);
    }
}
