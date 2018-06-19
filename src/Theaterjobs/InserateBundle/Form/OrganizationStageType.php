<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OrganizationStageType
 * @package Theaterjobs\InserateBundle\Form
 */
class OrganizationStageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stageTitle', TextType::class, [
                    'label' => 'organization.edit.stage.label.title',
                    'attr' => [
                        'placeholder' => false,
                        'maxlength' => 50,
                        'class' => 'stageTitle',
                        'required' => true
                    ],
                    'constraints' => [
                        new Assert\NotBlank()
                    ]
                ]
            )
            ->add('stageSeats', 'text', [
                    'label' => 'organization.edit.stage.label.seats',
                    'attr' => [
                        'placeholder' => false,
                        'class' => 'stageSeats',
                        'min' => 1,
                        'max' => 99000,
                    ],
                    'constraints' => [
                        new Assert\Type('digit'),
                        new Assert\Range(['min' => 1, 'max' => 99000])
                    ]
                ]
            )
            ->add('hubStages', 'text', [
                    'label' => 'organization.edit.stage.label.hub',
                    'attr' => [
                        'placeholder' => false,
                        'class' => 'hubStages',
                        'min' => 1,
                        'max' => 99
                    ],
                    'constraints' => [
                        new Assert\Type('digit'),
                        new Assert\Range(['min' => 1, 'max' => 99])
                    ]
                ]
            )
            ->add('stageWidth', 'number', [
                    'grouping' => true,
                    'label' => 'organization.edit.stage.label.width',
                    'attr' => [
                        'placeholder' => false,
                        'class' => 'stageWidth',
                        'data-min' => 0.01,
                        'data-max' => 99.99
                    ],
                    'constraints' => [
                        new Assert\Range(['min' => 0.01, 'max' => 99.99])
                    ]
                ]
            )
            ->add('stageDepth', 'number', [
                    'label' => 'organization.edit.stage.label.depth',
                    'grouping' => true,
                    'attr' => [
                        'placeholder' => false,
                        'class' => 'stageDepth',
                        'data-min' => 0.01,
                        'data-max' => 99.99
                    ],
                    'constraints' => [
                        new Assert\Range(['min' => 0.01, 'max' => 99.99])
                    ]
                ]
            )
            ->add('portalWidth', 'number', [
                    'grouping' => true,
                    'label' => 'organization.edit.stage.label.portalwidth',
                    'attr' => [
                        'placeholder' => false,
                        'class' => 'portalWidth',
                        'data-min' => 0.01,
                        'data-max' => 99.99
                    ],
                    'constraints' => [
                        new Assert\Range(['min' => 0.01, 'max' => 99.99])
                    ]
                ]
            )
            ->add('portalDepth', 'number', [
                    'label' => 'organization.edit.stage.label.portalHeight',
                    'grouping' => true,
                    'attr' => [
                        'placeholder' => false,
                        'class' => 'portalDepth',
                        'data-min' => 0.01,
                        'data-max' => 99.99
                    ],
                    'constraints' => [
                        new Assert\Range(['min' => 0.01, 'max' => 99.99])
                    ]
                ]
            )
            ->add('tags_helper', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'organization.edit.stage.tags',
                'attr' => ['class' => 'tag-input-style placeholder-entercharact',
                    'multiple' => true
                ],
            ])
            ->add('moreInfo', TextType::class, [
                'label' => 'organization.edit.label.moreInfo',
                'attr' => [
                    'placeholder' => false,
                    'maxlength' => 80
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\InserateBundle\Entity\OrganizationStage'
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tj_inserate_organization_stage_type';
    }
}
