<?php

namespace Theaterjobs\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AdminBillingType
 * @package Theaterjobs\AdminBundle\Form
 *
 */
class AdminBillingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('billingNr', TextType::class)
            ->add('billingCreationFrom', TextType::class)
            ->add('billingCreationTo', TextType::class)
            ->add('input', TextType::class)
            ->add('user', TextType::class)
            ->add('billingIban', TextType::class)
            ->add('billingPayment', TextType::class)
            ->add('billingCountry', TextType::class)
            ->add('order', HiddenType::class)
            ->add('orderCol', HiddenType::class)
            ->add('choices', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Billing No' => 'billingNr',
                    'All' => 'input',
                    'User' => 'user',
                    'Creation' => 'billingCreation',
                    'Iban' => 'billingIban',
                    'Payment Method' => 'billingPayment',
                    'Country' => 'billingCountry',
                ],
                'choices_as_values' => true,
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // avoid to pass the csrf token in the url (but it's not protected anymore)
            'csrf_protection' => false,
            'data_class' => 'Theaterjobs\AdminBundle\Model\AdminBillingSearch',
            'translation_domain' => 'forms',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_billing_search_type';
    }
}