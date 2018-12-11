<?php

namespace Theaterjobs\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\MembershipBundle\Entity\BillingAddress;
use Theaterjobs\MembershipBundle\Form\Type\BillingAddressType;

/**
 * Master Data Form Type
 *
 * @category Form
 * @package  Theaterjobs\UserBundle\Form
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @DI\FormType
 */
class MasterDataFormType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', 'text', array(
                'label' => 'account.edit.label.newFirstName',
                'required' => true,
                'attr' => array('placeholder' => false),
                'label_attr' =>['class' => 'col-md-4 no-padding']
            ))
            ->add('lastName', 'text', array(
                'label' => 'account.edit.label.newLastName',
                'required' => true,
                'attr' => array('placeholder' => false)
            ))
            ->add('subtitle', 'text', array(
                'label' => 'account.edit.label.artistName',
                'required' => false,
                'attr' => array('placeholder' => false, 'maxlength' => 40)
            ))
            ->add('doNotTrackViews', 'checkbox', array(
                'label' => 'account.edit.label.surfAnonymously',
                'required' => false,
                'attr' => array('class' => 'col-lg-5')
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'forms',
                'data_class' => 'Theaterjobs\ProfileBundle\Entity\Profile',
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
        return 'tj_user_form_master_data';
    }

}
