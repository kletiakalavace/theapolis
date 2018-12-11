<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrganizationSectionType
 * @package Theaterjobs\InserateBundle\Form
 */
class OrganizationSectionType extends AbstractType
{
    /**
     * (non-PHPdoc)
     * @param OptionsResolver $resolver
     *
     * @see \Symfony\Component\Form\AbstractType::setDefaultOptions()
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'forms',
                'class' => 'TheaterjobsInserateBundle:OrganizationSection',
                'property' => 'name',
                'empty_value' => 'form.choice.organization_section.empty_value',
                'empty_data' => null,
                'label' => 'organization.edit.label.section',
                'expanded' => true,
                'multiple' => true,
                'span' => 'test',
            )
        );
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\AbstractType::getParent()
     *
     * @return Entity()
     */
    public function getParent()
    {
        return 'entity';
    }

    /** (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'tj_inserate_form_organization_section';
    }
}
