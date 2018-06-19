<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrganizationScheduleType
 * @package Theaterjobs\InserateBundle\Form
 */
class OrganizationScheduleType extends AbstractType {
     /**
     * (non-PHPdoc)
     * @param OptionsResolver $resolver
     *
     * @see \Symfony\Component\Form\AbstractType::setDefaultOptions()
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(
                array(
                    'translation_domain' => 'forms',
                    'class' => 'TheaterjobsInserateBundle:OrganizationSchedule',
                    'property' => 'name',
                    'empty_value' => 'organization.categories.noSelection',
                    'empty_data' => null,
                    'label' => 'organization.edit.label.schedule',
                    'expanded' => false,
                    'multiple' => false,
                )
        );
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\AbstractType::getParent()
     *
     * @return Entity()
     */
    public function getParent() {
        return 'entity';
    }

    /** (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     * @return string
     */
    public function getName() {
        return 'tj_inserate_form_organization_schedule';
    }
}
