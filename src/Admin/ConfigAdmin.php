<?php

declare(strict_types=1);

namespace WebEtDesign\AnalyticsBundle\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Count;
use WebEtDesign\AnalyticsBundle\Entity\Config;
use WebEtDesign\AnalyticsBundle\Enum\ConfigTypeEnum;

final class ConfigAdmin extends AbstractAdmin
{
    public function __construct($code, $class, $baseControllerName = null, private EntityManagerInterface $em)
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
            ->add('_action', null, [
                'actions' => [
                    'edit'   => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        if ($this->isCurrentRoute('create')){
            $formMapper
                ->add('code', ChoiceType::class, [
                    'label' => 'Type',
                    'choices' => ConfigTypeEnum::getChoicesList($this->em->getRepository(Config::class)->findAll(), $this->getSubject()),
                    'placeholder' => 'Choisir le type de configuration'
                ])
            ;

        }
        if ($this->isCurrentRoute('edit')){
            /** @var Config */
            switch ($this->getSubject()->getCode()){
                case ConfigTypeEnum::COLORS:
                    $formMapper->add('valueArray', CollectionType::class, [
                        'label' => 'Valeur',
                        'entry_type' => ColorType::class,
                        'allow_add' => true,
                        'allow_delete' => true,
                    ]);
                    break;
                case ConfigTypeEnum::DEGRADED_COLOR:
                    $formMapper->add('value', ColorType::class, [
                        'label' => 'Valeur',
                    ]);
                    break;
                case ConfigTypeEnum::DIFF_COLORS:
                    $formMapper->add('valueArray', CollectionType::class, [
                        'label' => 'Valeur',
                        'entry_type' => ColorType::class,
                        'allow_add' => true,
                        'allow_delete' => true,
                        'delete_empty' => function ($data) {
                            return null === $data;
                        },
                        'constraints' => [
                            new Count(['max' => 4])
                        ]
                    ]);
                    break;
            }
        }

        $formMapper->getFormBuilder()->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event){
            /** @var Config $config */
            $config = $this->getSubject();

           $same = $this->em->getRepository(Config::class)->findOneBy(['code' => $config->getCode()]);

           if ($same && $same->getId() != $config->getId()){
               $event->getForm()->addError(new FormError('Vous avez déjà défini une configuration de ce type'));
           }


           if ($config->getId() !== null){
               if ($config->getCode() === ConfigTypeEnum::DIFF_COLORS && count($event->getData()['valueArray']) !== 2){
                   $event->getForm()->addError(new FormError('Vous devez mettre deux valeurs pour ce type'));
               }
           }
        });

    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        parent::configureRoutes($collection);
        $collection->remove('show');
    }

}
