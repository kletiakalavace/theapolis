<?php

namespace Theaterjobs\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Password Reset Form Type
 *
 * @category Form
 * @package  Theaterjobs\UserBundle\Form
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @DI\FormType
 */
class PasswordResetType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('password', 'password')
                ->add(
                    'password', 'password', array(
                        'label' => 'account.edit.password',
                    )
                )
                ->add('plainPassword', 'repeated', array(
                    'type' => 'password',
                    /*'invalid_message' => 'tj.error.password.fields.do.not.match',*/
                    'options' => array('attr' => array('class' => 'password-field')),
                    'required' => true,
                    'first_options' => array('label' => 'account.edit.passwordNew'),
                    'second_options' => array('label' => 'account.edit.passwordNewRepeat'),
                ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'tj_user_form_change_password';
    }

}
