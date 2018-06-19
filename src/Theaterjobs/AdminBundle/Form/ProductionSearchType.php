<?php

namespace Theaterjobs\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductionSearchType
 * @package Theaterjobs\AdminBundle\Form
 *
 */
class ProductionSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('input', TextType::class)
            ->add('name', TextType::class)
            ->add('director', TextType::class)
            ->add('creator', TextType::class)
            ->add('organization', TextType::class)
            ->add('year', TextType::class)
            ->add('order', HiddenType::class)
            ->add('orderCol', HiddenType::class)
            ->add('choices', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'admin.production.choice.All' => 'input',
                    'admin.production.choice.Name' => 'name',
                    'admin.production.choice.Creator' => 'creator',
                    'admin.production.choice.Director' => 'director',
                    'admin.production.choice.Organization' => 'organization',
                    'admin.production.choice.Year' => 'year'
                ],
                'choices_as_values' => true,
            ])
            ->add('status', ChoiceType::class, [
                    'label' => false,
                    'attr' => ['class' => 'form-control'],
                    'choices' => [
                        'Select a status' => '',
                        'admin.production.status.Checked' => 1,
                        'admin.production.status.Unchecked' => 0,
                    ],
                    'data' => 0,
                    'choices_as_values' => true,
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => 'Theaterjobs\AdminBundle\Model\ProductionSearch',
            'translation_domain' => 'forms',
            'role' => null
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_productions_search_type';
    }
}