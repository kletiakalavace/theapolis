<?php

namespace Theaterjobs\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Profile Form Type
 *
 * @category Form
 * @package  Theaterjobs\ProfileBundle\Form
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @DI\FormType
 */
class ProfileRegistrationFormType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('firstName', 'text', array(
                    'label' => 'registration.label.firstname',
                    'required' => true,
                ))
                ->add('lastName', 'text', array(
                    'label' => 'registration.label.lastname',
                    'required' => true,
                ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(
                array(
                    'translation_domain' => 'forms',
                    'data_class' => 'Theaterjobs\ProfileBundle\Entity\Profile',
                    'cascade_validation' => false,
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
        return 'theaterjobs_user_profile_registration';
    }

}
