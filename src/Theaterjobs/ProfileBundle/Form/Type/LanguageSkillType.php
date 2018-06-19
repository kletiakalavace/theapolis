<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Theaterjobs\ProfileBundle\Form\DataTransformer\SkillTransformer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class LanguageSkillType
 * @package Theaterjobs\ProfileBundle\Form\Type
 * @DI\FormType
 */
class LanguageSkillType extends AbstractType
{

    /** @DI\Inject("doctrine.orm.entity_manager") */
    public $om;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new SkillTransformer($this->om);
        $builder->add(
            $builder->create(
                'skill', 'text', array(
                'label' => false,
                'required' => true,
                'attr' => array('class' => 'skillTitle placeholder-entercharact',
                    'autocomplete' => "off",
                    'spellcheck' => "false",
                    'dir' => "auto",
                    'multiple' => true
                )))
                ->addModelTransformer($transformer)
        )
            ->add('rating', 'choice', array(
                    'choices' => array(
                        1 => 'A1',
                        2 => 'A2',
                        3 => 'B1',
                        4 => 'B2',
                        5 => 'C1',
                        6 => 'C2'
                    ),
                    'placeholder' => "people.edit.lang.skill.select",
                    'multiple' => false,
                    'required' => true,
                    'label' => false,
                    'attr' => array(
                        'class'=> "skill-select-input",
                    ),
                )
            )
            ->add('skillType', 'hidden');

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\LanguageSkill'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tj_profile_language_skill';
    }

}
