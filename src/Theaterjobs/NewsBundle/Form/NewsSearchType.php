<?php

namespace Theaterjobs\NewsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class NewsSearchType
 * @package Theaterjobs\NewsBundle\Form
 *
 * @DI\FormType
 */
class NewsSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('searchPhrase', TextType::class, [
                    'required' => false,
                    'label' => 'news.search.label.keyword',
                    'attr' =>
                        [
                            'class' => 'form-control no-border'
                        ]
                ]
            )
            ->add('years', ChoiceType::class, [
                    'choices' => range(date('Y'), date('Y') - $options['news_years_range']),
                    'choice_label' => function ($choice, $key, $value) {
                        return $value;
                    },
                    'required' => false,
                    'placeholder' => 'news.search.placeholder.allYears',
                    'choices_as_values' => true,
                    'label' => 'news.search.label.year'
                ]
            )
            ->add('favorite', HiddenType::class)
            ->add('organization', HiddenType::class)
            ->add('tags', TextType::class, [
                'label' => 'news.edit.label.tags',
                'mapped' => true,
                'required' => false,
                'attr' =>
                    [
                        'class' => 'tag-input-style',
                        'multiple' => true,
                    ]
            ]);

        if ($options['role']) {

            $builder->add('published', ChoiceType::class,
                [
                    'label' => false,
                    'choices' =>
                        [
                            'news.search.placeholder.published' => 1,
                            'news.search.placeholder.Unpublished' => 0
                        ],
                    'data' => 1,
                    'choices_as_values' => true
                ]
            );
        }

        $builder->add('page', HiddenType::class);
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // avoid to pass the csrf token in the url (but it's not protected anymore)
            'csrf_protection' => false,
            'data_class' => 'Theaterjobs\NewsBundle\Model\NewsSearch',
            'translation_domain' => 'forms',
            'role' => null,
            'news_years_range' => 4
        ]);

    }

    /**
     *
     * @param array $options
     * @return array
     */
    public function getDefaultOptions(array $options)
    {
        return $options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'news_search_type';
    }
}