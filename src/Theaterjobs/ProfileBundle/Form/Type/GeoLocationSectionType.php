<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class GeoLocationSectionType
 * @package Theaterjobs\ProfileBundle\Form\Type
 */
class GeoLocationSectionType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('geolocation', 'text', array(
                'label' => 'form.profile.label.contact.geolocation',
                'attr' => array('class' => 'map-input')
            ))->add('country', 'text', array(
                'label' => 'form.profile.label.contact.country',
                'attr' => array('class' => 'map-input-country')
            ))->add('city', 'text', array(
                'label' => 'form.profile.label.contact.city',
                'attr' => array('class' => 'map-input-city')
            ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\ContactSection'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_profilebundle_geolocation';
    }

}
