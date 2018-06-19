<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TagsType
 * @package Theaterjobs\InserateBundle\Form
 */
class TagsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                    'label' => 'organization.edit.label.stageTagTitle',
                    'mapped' => true,
                    'attr' => array(
                        'placeholder' => "organization.edit.placeholder.stageTagTitle"
                    )
                )
            );
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Theaterjobs\InserateBundle\Entity\Tags'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_inseratebundle_tags';
    }
}
