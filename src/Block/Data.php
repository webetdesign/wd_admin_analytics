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
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use WebEtDesign\AnalyticsBundle\Entity\Block;
use WebEtDesign\AnalyticsBundle\Services\Analytics;

class Data extends AbstractBlockService
{
    #[Pure] public function __construct(
        $name,
        private Analytics $analyticsService,
        private EntityManagerInterface $em,
        private Security $security,
        private FlashBagInterface $flashBag
    )
    {
        parent::__construct($name);
    }

    /**
     * @param BlockContextInterface $blockContext
     * @param Response|null $response
     * @return mixed
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        $settings = $blockContext->getSettings();

        $blocks = [];

        $this->analyticsService->maxPage = 5;

        /** @var Block $block */
        foreach ($this->em->getRepository(Block::class)->findBy(['active' => true]) as $block) {
            $block_name = $block->getCode();

            $method = "get" . ucfirst($block_name);
            $row = [];
            $row["template"] = "@WDAdminAnalytics/" . $block_name . ".html.twig";
            $row["name"] = $block_name;
            $row["icon"] = $block->getIcon();
            $row["size"] = $block->getSize();
            $row["start"] = $block->getStart();
            $blocks[$block_name] = $row;
        }

        $old_block = false;
        foreach ($settings["analytics"] as $block) {
            $old_block = true;
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
            $blocks[$block_name] = $row;
        }

        $current_user = $this->security->getUser();
        if (in_array('ROLE_ADMIN_CMS', $current_user->getRoles()) && $old_block) {
            $this->flashBag->add('error', 'La configuration du bundle Analytics n\'est plus à jour. Vous devez utilisé le crud pour définir les blocks. (voir documentation)');
        }

        return $this->renderResponse("@WDAdminAnalytics/render.html.twig", [
            'blocks' => $blocks,
        ], $response);
    }

    public function getName(): string
    {
        return 'Admin Analytics';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "analytics" => []
        ]);
        $resolver->setAllowedTypes('analytics', ['array', 'null']);

    }
}
