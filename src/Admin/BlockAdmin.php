<?php

declare(strict_types=1);

namespace WebEtDesign\AnalyticsBundle\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use WebEtDesign\AnalyticsBundle\Entity\Block;
use WebEtDesign\AnalyticsBundle\Enum\BlockStartEnum;
use WebEtDesign\AnalyticsBundle\Enum\BlockTypeEnum;

final class BlockAdmin extends AbstractAdmin
{
    public function __construct($code, $class, $baseControllerName = null, private EntityManagerInterface $em, private ContainerInterface $container)
    {
        parent::__construct($code, $class, $baseControllerName);
    }

    protected string $translationDomain = 'admin';

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('codeList', null, [
                'label' => 'Type'
            ])
            ->add('startList', null, [
                'label' => 'Début'
            ])
            ->add('active', null, [
                'label' => 'Actif',
                'editable' => true
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit'   => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('code', ChoiceType::class, [
                'label' => 'Type',
                'choices' => BlockTypeEnum::getChoicesList($this->em->getRepository(Block::class)->findAll(), $this->getSubject(), $this->container->hasParameter('wd_newsletter.enable_log') && $this->container->getParameter('wd_newsletter.enable_log')),
                'placeholder' => 'Choisir le type de block'
            ])
            ->add('start', ChoiceType::class, [
                'label' => 'Début',
                'choices' => BlockStartEnum::getChoicesList(),
                'placeholder' => 'Choisir la date de début'
            ])
            ->add('icon', null, [
                'label' => 'Icône',
                'help' => 'Vous pouvez choisir un icon dans la liste <a href="https://fontawesome.com/v4.7.0/icons/" target="_blank">suivante.</a>'
            ])
            ->add('size', null, [
                'label' => 'Taille',
                'help' => 'Class bootstrap : col-md-6 col-xs-12 (<a href="https://getbootstrap.com/docs/4.5/layout/grid/" target="_blank">Documentation</a>)'
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false
            ])
            ;

        $formMapper->getFormBuilder()->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event){
            /** @var Block $block */
           $block = $this->getSubject();

           $same = $this->em->getRepository(Block::class)->findOneBy(['code' => $block->getCode()]);

           if ($same && $same->getId() != $block->getId()){
               $event->getForm()->addError(new FormError('Vous avez déjà défini un block de ce type'));
           }
        });
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        parent::configureRoutes($collection);
        $collection->remove('show');
    }

}
