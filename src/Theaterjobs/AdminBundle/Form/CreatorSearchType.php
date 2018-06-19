<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 24/02/2018
 * Time: 20:09
 */

namespace Theaterjobs\AdminBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CreatorSearchType
 * @package Theaterjobs\AdminBundle\Form
 *
 */
class CreatorSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false
            ])
            ->add('order', HiddenType::class)
            ->add('orderCol', HiddenType::class)
            ->add('published', ChoiceType::class, [
                    'label' => false,
                    'choices' => [
                        'Select a status' => '',
                        'admin.creatorList.Checked' => 1,
                        'admin.creatorList.Unchecked' => 0
                    ],
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
            'data_class' => 'Theaterjobs\AdminBundle\Model\CreatorSearch',
            'translation_domain' => 'messages',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_creator_search_type';
    }

}