<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Theaterjobs\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\ResettingFormType as BaseType;

/**
 * Resetting Form Type
 *
 * @category Form
 * @package  Theaterjobs\UserBundle\Form\Type
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class ResettingFormType extends BaseType
{
    /**
     * (non-PHPdoc)
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options An arrray with options.
     *
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'plainPassword', 'repeated',
            array(
                'type' => 'password',
                'first_options' => array(
                        'label' => 'ResetPassword.modal.label.resettingPassword',
                        'attr' => array('class' => 'password_test')
                ),
                'second_options' => array(
                        'label' => 'ResetPassword.modal.label.resettingPasswordConfirmation',
                        'attr' => array('class' => 'password_test')
                ),
                /*'invalid_message' => 'tj.error.password.fields.do.not.match',*/
            )
        )->add(
            'email', 'hidden',
            array(
                'disabled' => true
            )
        );
    }

    /**
     * (non-PHPdoc)
     * @see \FOS\UserBundle\Form\Type\ResettingFormType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(
            array(
                'translation_domain' => 'forms',
                'data_class' =>'Theaterjobs\UserBundle\Entity\User',
                'intention'  => 'resetting',
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
        return 'theaterjobs_user_resetting';
    }

}
