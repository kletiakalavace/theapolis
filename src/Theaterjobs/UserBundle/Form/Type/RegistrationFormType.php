<?php

namespace Theaterjobs\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Validator\Constraints\True as TrueValansid;

/**
 * Registration Form Type
 *
 * @category Form
 * @package  Theaterjobs\UserBundle\Form\Type
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class RegistrationFormType extends BaseType
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
        // remove username from form builder since we are not using it for the registration
        $builder->remove('username');
        $builder
            ->add('profile', 'theaterjobs_user_profile_registration', [
                'required' => true,
                'label' => false,
            ])
            ->add(
                'email', 'email', [
                'label' => "registration.label.email",
                'trim' => true,
            ])
            ->add(
                'plainPassword', 'repeated', [
                    'type' => 'password',
                    'first_options' => [
                        'label' => "registration.label.password",
                    ],
                    'second_options' => [
                        'label' => 'registration.label.passwordConfirmation',
                    ],
                    'invalid_message' => 'registration.error.passwordMismatch',
                ]
            )
            ->add(
                'terms_and_trades', 'checkbox', [
                    'label' => 'registration.label.termsAndTrades',
                    'label_attr' => [
                        'id' => 'terms',
                        'class' => 'checkbox-label hidden'
                    ],
                    'attr' => [
                        'align_with_widget' => true,
                        'data-toggle' => 'modal',
                        'data-target' => 'gtcModal',
                    ],
                    'mapped' => false,
                    'constraints' => [new TrueValansid()],
                ]
            );

        $builder->add(
            'submit', 'submit', [
                'label' => 'register.button.registrationFree',
                'attr' => ['class' => 'save button button-primary'],
            ]
        );
    }

    /**
     * (non-PHPdoc)
     * @see \FOS\UserBundle\Form\Type\RegistrationFormType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(['translation_domain' => 'forms']);
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_user_registration';
    }

}
