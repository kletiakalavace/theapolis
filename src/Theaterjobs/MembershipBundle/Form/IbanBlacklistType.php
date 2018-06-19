<?php

namespace Theaterjobs\MembershipBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IbanBlacklistType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('iban', 'text', array(
                    'label' => 'form.label.iban_blacklist.iban',
                    'attr' => array(
                        'placeholder' => 'form.placeholder.iban_blacklist.iban',
                    )
                        )
                )
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Theaterjobs\MembershipBundle\Entity\IbanBlacklist'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'theaterjobs_membershipbundle_ibanblacklist';
    }

}
