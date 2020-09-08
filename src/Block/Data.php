<?php
/**
 * Created by PhpStorm.
 * User: benjamin
 * Date: 16/04/2019
 * Time: 16:38
 */

namespace WebEtDesign\AnalyticsBundle\Block;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\BlockBundle\Block\AbstractBlockService;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use WebEtDesign\AnalyticsBundle\Entity\Block;
use WebEtDesign\AnalyticsBundle\Services\Analytics;

class Data extends AbstractBlockService
{
    /**
     * @var Analytics
     */
    private $analyticsService;

    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @var TokenStorageInterface $security
     */
    private $security;

    /**
     * @var FlashBagInterface $flashBag
     */
    private $flashBag;

    /**
     * @param string $name
     * @param EngineInterface $templating
     * @param Analytics $analyticsService
     * @param EntityManagerInterface $em
     * @param Security $security
     * @param FlashBagInterface $flashBag
     */
    public function __construct($name, EngineInterface $templating, Analytics $analyticsService, EntityManagerInterface $em, Security $security, FlashBagInterface $flashBag)
    {
        parent::__construct($name, $templating);

        $this->analyticsService = $analyticsService;
        $this->em = $em;
        $this->security = $security;
        $this->flashBag = $flashBag;
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
        if (in_array('ROLE_ADMIN_CMS', $current_user->getRoles()) && $old_block){
            $this->flashBag->add('error', 'La configuration du bundle Analytics n\'est plus à jour. Vous devez utilisé le crud pour définir les blocks. (voir documentation)');
        }

        dump($blocks);
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
