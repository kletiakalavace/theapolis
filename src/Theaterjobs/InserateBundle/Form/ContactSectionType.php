<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ContactSectionType
 * @package Theaterjobs\InserateBundle\Form
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
                'label' => 'organization.edit.label.contact',
                'required' => false
            ))
            ->add('email', 'email', array(
                'label' => 'organization.edit.label.email',
                'required' => false,
                'constraints' => [
                    new Assert\Email()
                ],

            ))
            ->add('social', CollectionType::class, array(
                    'label' => false,
                    'type' => OrganizationSocialMediaType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
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
            'data_class' => 'Theaterjobs\InserateBundle\Entity\ContactSection'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_inseratebundle_cvsection';
    }

}
