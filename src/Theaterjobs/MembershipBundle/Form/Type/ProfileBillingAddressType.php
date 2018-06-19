<?php

namespace Theaterjobs\MembershipBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of ProfileBillingAddressType
 *
 * @category Type
 * @package  Theaterjobs\MembershipBundle\Form\Type
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class ProfileBillingAddressType extends AbstractType {

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('billingAddress', BillingAddressType::class);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(
                array(
                    'translation_domain' => 'forms',
                    'data_class' => 'Theaterjobs\MembershipBundle\Model\ProfileInterface',
                    'cascade_validation' => true,
                )
        );
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'theaterjobs_membership_profile_billing_address_type';
    }

}
