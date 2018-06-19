<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BiographySectionType
 * @package Theaterjobs\ProfileBundle\Form\Type
 */
class BiographySectionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('biography', 'textarea', [
                'label' => 'form.profile.label.biography.description'
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\BiographySection'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_profile_biography';
    }
}
