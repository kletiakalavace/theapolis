<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TeamMembershipApplicationType
 * @package Theaterjobs\InserateBundle\Form
 */
class TeamMembershipApplicationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('applicationText', TextareaType::class, array(
                    'label' => false,
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
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\InserateBundle\Entity\TeamMembershipApplication'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tj_inserate_form_organization_new_team_membership_application';
    }
}
