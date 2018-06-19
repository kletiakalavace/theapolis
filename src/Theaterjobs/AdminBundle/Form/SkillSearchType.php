<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 11/03/2018
 * Time: 12:54
 */

namespace Theaterjobs\AdminBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkillSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => false
            ])
            ->add('isLanguage', HiddenType::class)
            ->add('order', HiddenType::class)
            ->add('orderCol', HiddenType::class)
            ->add('choices', ChoiceType::class, [
                    'label' => false,
                    'choices' => [
                        'Select a status' => '',
                        'admin.stageTag.Checked' => 1,
                        'admin.stageTag.Unchecked' => 0
                    ],
                    'choices_as_values' => true,
                    'data' => 0
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
            'data_class' => 'Theaterjobs\AdminBundle\Model\SkillSearch',
            'translation_domain' => 'messages',
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return '';
    }

}