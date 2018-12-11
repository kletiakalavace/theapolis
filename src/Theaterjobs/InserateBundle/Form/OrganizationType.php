<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Organization Form Type
 *
 * @category Form
 * @package  Theaterjobs\MainBundle\Form\Type
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class OrganizationType extends AbstractType
{
    /**
     * (non-PHPdoc)
     * @param FormBuilderInterface $builder The form builder.
     * @param array $options An arrray with options.
     *
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organizationOwner', 'text', array(
                    'label' => 'organization.edit.label.owner',
                    'attr' => array(
                        'placeholder' => false,
                        'maxlength' => 100,
                    ),
                    'required' => false
                )
            )
            ->add('staff', 'integer', array(
                    'label' => 'organization.edit.label.staffcomplete',
                    'precision' => 0,
                    'attr' => array(
                        'placeholder' => false,
                        'maxlength' => 4,
                    ),
                    'required' => false,
                    'constraints' => [
                        new Assert\Range(['min' => 0, 'max' => 9999])
                    ]
                )
            )
            ->add('form', FormOfOrganizationType::class, array(
                'required' => false,
                'label' => 'organization.edit.label.organizationform',
                 'empty_value' => 'organization.categories.noSelection'
            ))
            ->add('organizationSection', OrganizationSectionType::class, array('required' => false))
            ->add('organizationKind', OrganizationKindType::class, array('required' => false))
            ->add('organizationSchedule', OrganizationScheduleType::class, array('required' => false))
            ->add('organizationStaff', 'collection', array(
                'label' => false,
                'required' => false,
                'type' => OrganizationStaffType::class,
                'allow_delete' => true,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false,
            ))
            ->add('orchestraClass', 'choice', array(
                'choices' => array(
                    'a_plus' => 'organization.edit.orchestra.choiceA_plus',
                    'a' => 'organization.edit.orchestra.choiceA',
                    'b' => 'organization.edit.orchestra.choiceB',
                    'c' => 'organization.edit.orchestra.choiceC',
                    'd' => 'organization.edit.orchestra.choiceD',
                    'other' => 'organization.edit.orchestra.choiceOther',
                ),
                'required' => false,
                'choices_as_values' => false,
                'label' => 'organization.edit.label.orchestraClass',
                'empty_value' => 'organization.edit.choice.orchestraClass.empty'
            ));

    }

    /**
     * (non-PHPdoc)
     * @param OptionsResolver $resolver
     *
     * @see \Symfony\Component\Form\AbstractType::setDefaultOptions()
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\InserateBundle\Entity\Organization',
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'tj_inserate_form_organization';
    }

}
