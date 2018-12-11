<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DirectorType
 * @package Theaterjobs\ProfileBundle\Form\Type
 *
 */
class DirectorType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                    'label' => 'people.edit.label.directorName',
                    'mapped' => true,
                    'attr' => array('class' => 'tag-director-input',
                        'multiple' => true,
                        'placeholder' => "people.edit.placeholder.directorName"
                    )
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\Director',
            'translation_domain' => 'forms'
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_profilebundle_director';
    }
}
