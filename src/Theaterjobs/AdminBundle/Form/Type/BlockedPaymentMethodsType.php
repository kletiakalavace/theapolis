<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of BlockedPaymentMethodsType
 *
 * @author malvin
 */
class BlockedPaymentMethodsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('PaymentMethod', 'entity', [
                    'class' => 'TheaterjobsMembershipBundle:PaymentMethod',
                    'property' => 'title',
                    'mapped' => false
                ]
            )
            ->add('description', 'text', [
                'mapped' => false
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */


    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_memmbership_blocked_payment_methods';
    }
}

