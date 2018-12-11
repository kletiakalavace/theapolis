<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OrganizationStaffType
 * @package Theaterjobs\InserateBundle\Form
 */
class OrganizationStaffType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                    'label' => 'organization.edit.label.staff.title',
                    'attr' => array(
                        'placeholder' => false,
                        'maxlength' => 50,
                        'class' => "groupTitle",
                        'required' => true
                    ),

                )
            )
            ->add('groupNumber', 'integer', array(
                'label' => 'organization.edit.label.staff.number',
                'attr' => array(
                    'placeholder' => false,
                    'maxlength' => 3,
                    'class' => "groupNumber",
                    'required' => true,
                    'min' => 0,
                    'max' => 999
                ),
                'constraints' => [
                    new Assert\Range(['min' => 0, 'max' => 999])
                ]
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\InserateBundle\Entity\OrganizationStaff'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tj_inserate_organization_staff_type';
    }
}
