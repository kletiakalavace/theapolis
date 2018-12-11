<?php

namespace Theaterjobs\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EmailChangeRequestType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('oldMail','email',array(
                    'attr'=>array('readonly'=>true),
                    'label' => 'account.label.oldMail',
                ))
                ->add('newMail', 'repeated', array(
                    'type' => 'email',
                    'invalid_message' => 'tj.error.email.fields.do.not.match',
                    'options' => array('attr' => array('class' => 'password-field')),
                    'required' => true,
                    'first_options' => array('label' => 'account.label.emailNew'),
                    'second_options' => array('label' => 'account.label.emailNewRepeat'),
                ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\UserBundle\Entity\EmailChangeRequest'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'theaterjobs_userbundle_emailchangerequest';
    }

}
