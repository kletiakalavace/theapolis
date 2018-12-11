<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class JobSearchType
 * @package Theaterjobs\InserateBundle\Form
 *
 * @DI\FormType
 */
class JobSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gratification', GratificationType::class, [
                'empty_value' => 'work.filter.label.noSelection',
                'empty_data' => null,
                'expanded' => true,
                'multiple' => true
            ])
            ->add('searchPhrase', TextType::class,
                [
                    'required' => false,
                    'label' => 'work.search.label.keyword',
                    'attr' => array('class' => 'form-control no-border',
                    ),
                ]
            )
            ->add('favorite', HiddenType::class)
            ->add('organization', HiddenType::class)
            ->add('applied', HiddenType::class);
        if (!$options['isTeamList']) {
            $builder->add('location', TextType::class,
                [
                    'required' => false,
                    'label' => 'work.search.label.location',
                    'attr' =>
                        [
                            'class' => 'form-control form-control-location no-border',
                            'placeholder' => 'work.search.placeholder.location',
                        ],
                ])
                ->add('area', ChoiceType::class, [
                    'label' => 'work.search.label.distance',
                    'attr' => ['class' => 'form-control no-border'],
                    'choices' =>
                        [
                            '10 Km' => '10km',
                            '50 Km' => '50km',
                            '100 Km' => '100km',
                            '150 Km' => '150km',
                        ],
                    'empty_value' => false,
                    'choices_as_values' => true,
                ]);
        }
        $builder->add('subcategories', ChoiceType::class, [
                'choices' => $options['subcategories'],
                'expanded' => true,
                'multiple' => true
            ]
        );
        if ($options['role'] && $options['role'] != 3) {
            $status = [
                'job.status.draft' => 2,
                'job.status.published' => 1,
                'job.status.archived' => 3,
                'job.status.deleted' => 4,
                'job.status.pending' => 5
            ];

            if ($options['role'] != 1) {
                unset($status['job.status.deleted']);
            }

            $builder->add('status', ChoiceType::class,
                [
                    'choices' => $status,
                    'choices_as_values' => true,
                    'expanded' => true,
                    'multiple' => true
                ]
            );
        }
//        condition changed because of request to hide to all users role 6 is not available for anyone now
//        if ($options['role'] !== 2) {
        if ($options['role'] == 6) {
            $builder->add('sortBy', ChoiceType::class, [
                    'attr' => ['class' => 'form-control-small'],
                    'choices' => [
                        'order.date' => 'date',
                        'order.A-Z' => 'alphabetical',
                    ],
                    'empty_value' => false,
                    'data' => 'date',
                    'choices_as_values' => true,
                ]
            );
        }

        $builder
            ->add('page', HiddenType::class);

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // avoid to pass the csrf token in the url (but it's not protected anymore)
            'csrf_protection' => false,
            'data_class' => 'Theaterjobs\InserateBundle\Model\JobSearch',
            'translation_domain' => 'messages',
            'role' => null,
            'isTeamList' => false,
            'subcategories' => []
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'job_search_type';
    }
}