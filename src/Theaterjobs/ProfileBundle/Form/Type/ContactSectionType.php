<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContactSectionType
 * @package Theaterjobs\ProfileBundle\Form\Type
 */
class ContactSectionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contact', 'textarea', array(
                'label' => 'form.profile.label.contact.description'
            ))
            ->add('social', CollectionType::class, array(
                    'label' => false,
                    'type' => ProfileSocialMediaType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false
                )
            );

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
        return 'theaterjobs_profilebundle_cvsection';
    }

}
