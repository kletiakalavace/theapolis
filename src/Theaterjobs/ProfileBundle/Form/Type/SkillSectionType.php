<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Theaterjobs\ProfileBundle\Form\DataTransformer\ProfileSkillTransformer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class SkillSectionType
 * @package Theaterjobs\ProfileBundle\Form\Type
 * @DI\FormType
 */
class SkillSectionType extends AbstractType
{
    /** @DI\Inject("doctrine.orm.entity_manager") */
    public $om;

    /** @DI\Inject("security.token_storage") */
    public $security;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ProfileSkillTransformer($this->om, $this->security);

        $builder
            ->add('driveLicense', 'theaterjobs_category_category', array(
                    'required' => false,
                    'label' => 'people.edit.drivingLicense',
                    // overrides the default value in /CategoryBundle/Form/Type/CategoryType.php to display parent category name
                    // if you don't like to display main category name uncomment it
                    //'group_by' => null,
                    'choice_list' => $options['drive_licence_choice_list'],
                    'attr' => array(
                        'class' => 'select-empty-value placeholder-entercharact',
                        'placeholder' => 'No selection',
                    )
                )
            )
            ->add('languageSkill', 'collection', array(
                    'required' => false,
                    'type' => 'tj_profile_language_skill',
                    'prototype' => true,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'by_reference' => false
                )
            )
            ->add($builder->create('profileSkill', 'text', array(
                'label' => false,
                'mapped' => true,
                'required' => false,
                'attr' => array(
                    'class' => 'select-empty-value placeholder-entercharact'
                )
            ))->addModelTransformer($transformer)
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\SkillSection',
            'drive_licence_choice_list' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_profilebundle_skillsection';
    }
}