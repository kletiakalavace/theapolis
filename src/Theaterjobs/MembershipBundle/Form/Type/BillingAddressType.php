<?php

namespace Theaterjobs\MembershipBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BillingAddressType Form Type
 *
 * @category Form
 * @package  Theaterjobs\MembershipBundle\Form\Type
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class BillingAddressType extends AbstractType
{

    /**
     * (non-PHPdoc)
     * @param FormBuilderInterface $builder The form builder.
     * @param array $options An arrray with options.
     *
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add(
                'firstname', 'text', [
                    'required' => true,
                    'label' => 'membership.new.firstname',
                    'attr' => [ 'placeholder' => false ],
                    'constraints' => [ new Assert\NotBlank() ]
                ]
            )
            ->add(
                'lastname', 'text', [
                    'label' => 'membership.new.lastname',
                    'required' => true,
                    'attr' => [ 'placeholder' => false ],
                    'constraints' => [ new Assert\NotBlank() ]
                ]
            )->add(
                'company', 'text', [
                    'label' => 'membership.new.companyName',
                    'required' => false,
                    'attr' => [ 'placeholder' => false ]
                ]
            )
            ->add(
                'vatId', 'text', [
                    'label' => 'membership.new.VATNumber',
                    'required' => false,
                    'attr' => [ 'placeholder' => false ]
                ]
            )->add(
            'street', 'text', [
                'label' => 'membership.new.street',
                'required' => true,
                'attr' => [ 'placeholder' => false ],
                'constraints' => [ new Assert\NotBlank() ]
                ]
            )
            ->add(
                'zip', 'text', [
                    'label' => 'membership.new.zipCode',
                    'required' => true,
                    'attr' => [ 'placeholder' => false ],
                    'constraints' => [ new Assert\NotBlank() ]
                ]
            )
            ->add(
                'city', 'text', [
                    'label' => 'membership.new.city',
                    'required' => true,
                    'attr' => [ 'placeholder' => false ],
                    'constraints' => [ new Assert\NotBlank() ]
                ]
            )
            ->add(
                'country', 'country', [
                    'cascade_validation' => true,
                    'required' => true,
                    'constraints' => [ new Assert\NotBlank() ],
                    'label' => 'membership.new.country',
                    'empty_value' => 'membership.new.country',
                    'preferred_choices' => ['DE', 'AT', 'CH'],
                ]
            );
    }

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
                'data_class' => 'Theaterjobs\MembershipBundle\Entity\BillingAddress',
                'cascade_validation' => true,
            )
        );
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_membership_billing_address';
    }

}
