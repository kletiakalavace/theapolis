<?php

namespace Theaterjobs\AdminBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AdminPeopleType
 * @package Theaterjobs\AdminBundle\Form
 *
 */
class AdminPeopleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profileRegistration', TextType::class)
            ->add('user', TextType::class)
            ->add('input', TextType::class)
            ->add('userLastLogin', TextType::class)
            ->add('userEmail', TextType::class)
            ->add('order', HiddenType::class)
            ->add('orderCol', HiddenType::class)
            ->add('choices', ChoiceType::class, [
                    'label' => false,
                    'choices' => [
                        'All' => 'input',
                        'User' => 'user',
                        'Email' => 'userEmail',
                        'Last Login' => 'userLastLogin',
                        'Registration' => 'profileRegistration',
                    ],
                    'choices_as_values' => true,
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // avoid to pass the csrf token in the url (but it's not protected anymore)
            'csrf_protection' => false,
            'data_class' => 'Theaterjobs\AdminBundle\Model\AdminPeopleSearch',
            'translation_domain' => 'forms',
            'role' => null
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_people_search_type';
    }
}