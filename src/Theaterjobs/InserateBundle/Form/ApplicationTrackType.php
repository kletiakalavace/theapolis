<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ApplicationTrackType
 * @package Theaterjobs\InserateBundle\Form
 */
class ApplicationTrackType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class, array(
                    'label' => 'application.new.label.email',
                    'required' => true,
                    'attr' => array(),
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Email()
                    ]
                )
            )
            ->add('content', TextareaType::class, array(
                    'label' => 'application.new.label.content',
                    'required' => true,
                    'attr' => array(),
                    'constraints' => [
                        new Assert\NotBlank()
                    ]
                )
            )
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Theaterjobs\InserateBundle\Entity\ApplicationTrack'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_inseratebundle_applicationtrack';
    }
}
