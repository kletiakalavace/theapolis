<?php

namespace Theaterjobs\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Address Form Type
 *
 * @category Form
 * @package  Theaterjobs\MainBundle\Form\Type
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class AddressType extends AbstractType {

    /**
     * (non-PHPdoc)
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options An arrray with options.
     *
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add(
                        'firstname', 'text', array(
                    'label' => 'form.label.billing_address.firstname',
                    'attr' => array(
                        'placeholder' => false,
                    )
                        )
                )->add(
                        'lastname', 'text', array(
                    'label' => 'membership.new.lastname',
                    'attr' => array(
                        'placeholder' => false,
                    )
                        )
                )->add(
                        'company', 'text', array(
                    'label' => 'membership.new.companyName',
                    'required' => false,
                    'attr' => array(
                        'placeholder' => false,
                    )
                        )
                )
                ->add(
                        'street', 'text', array(
                    'label' => 'form.label.billing_address.street',
                    'attr' => array(
                        'placeholder' => false,
                    )
                        )
                )
                ->add(
                        'zip', 'text', array(
                    'label' => 'form.label.billing_address.zip',
                    'attr' => array(
                        'placeholder' => 'form.placeholder.billing_address.zip',
                    )
                        )
                )
                ->add(
                        'city', 'text', array(
                    'label' => 'form.label.billing_address.city',
                    'attr' => array(
                        'placeholder' => 'form.placeholder.billing_address.city',
                    )
                        )
                )
                ->add(
                        'country', 'country', array(
                    'label' => 'form.label.billing_address.country',
                    'cascade_validation' => true,
                    'empty_value' => 'form.label.country',
                    'preferred_choices' => array('DE', 'AT', 'CH')
                        )
                )
                ->add(
                        'phone', 'text', array(
                    'label' => 'form.label.billing_address.phone',
                    'attr' => array(
                        'placeholder' => 'form.placeholder.billing_address.phone',
                    )
                        )
                )->add(
                'fax', 'text', array(
            'label' => 'form.label.address.fax',
            'attr' => array(
                'placeholder' => 'form.placeholder.address.fax',
            )
                )
        );
    }

    /**
     * (non-PHPdoc)
     * @param OptionsResolverInterface $resolver
     *
     * @see \Symfony\Component\Form\AbstractType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(
                array(
                    'translation_domain' => 'forms',
                    'data_class' => 'Theaterjobs\MainBundle\Entity\Address',
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
    public function getName() {
        return 'theaterjobs_main_address';
    }

}
