<?php


namespace Theaterjobs\MembershipBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of BookingType
 *
 * @category Type
 * @package  Theaterjobs\MembershipBundle\Form\Type
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class BookingType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('membership', 'entity', array(
                    'class' => 'TheaterjobsMembershipBundle:Membership',
                    'property' => 'title',
                    'required' => true,
                    'expanded' => true,
                ))
                ->add('debitAccount', 'theaterjobs_membership_debit_account_type')
                ->add('profile', ProfileBillingAddressType::class)
                ->add('paymentmethod', 'theaterjobs_membership_paymentmethod');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(
                array(
                    'translation_domain' => 'forms',
                    'membership' => null,
                    'profile' => null,
                    'data_class' => 'Theaterjobs\MembershipBundle\Entity\Booking',
                    'cascade_validation' => true,
                )
        );
    }

    /**
     * (non-PHPdoc) @see \Symfony\Component\Form\FormTypeInterface::getName()
     */
    public function getName() {
        return 'theaterjobs_membership_booking_type';
    }

}
