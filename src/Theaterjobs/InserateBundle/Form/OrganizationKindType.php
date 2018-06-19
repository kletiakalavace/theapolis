<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * OrganizationSection Form Type
 * Class OrganizationKindType
 * @package Theaterjobs\InserateBundle\Form
 */
class OrganizationKindType extends AbstractType {
     /**
     * (non-PHPdoc)
     * @param OptionsResolverInterface $resolver
     *
     * @see \Symfony\Component\Form\AbstractType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(
                array(
                    'translation_domain' => 'forms',
                    'class' => 'TheaterjobsInserateBundle:OrganizationKind',
                    'property' => 'name',
                    'empty_value' => false,
                    'empty_data' => null,
                    'label' => 'organization.edit.label.kind',
                    'expanded' => true,
                    'multiple' => true,
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
        return 'tj_inserate_form_organization_kind';
    }
}
