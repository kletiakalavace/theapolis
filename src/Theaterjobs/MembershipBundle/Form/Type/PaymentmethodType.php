<?php

namespace Theaterjobs\MembershipBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * PaymentmethodType Form Type
 *
 * @category Form
 * @package  Theaterjobs\MembershipBundle\Form\Type
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\FormType
 */
class PaymentmethodType extends AbstractType {

    /** @DI\Inject("security.token_storage") */
    public $security;

    /**
     * (non-PHPdoc)
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options An arrray with options.
     *
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $user = $this->security->getToken()->getUser();
        $profile = $user->getProfile();

        $builder
                ->add(
                    'title',
                    'extended_entity',
                    [
                    'class' => 'TheaterjobsMembershipBundle:Paymentmethod',
                    'property' => 'title',
                    'label' => false,
                    'query_builder' => function(EntityRepository $er) use($profile) {
                        return $er->findByProfile($profile);
                    },
                    'expanded' => true,
                    'multiple' => false,
                    'required' => true,
                    'cascade_validation' => true,
                    'option_attributes' => array('price' => 'price', 'short' => 'short'),
                    ]
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
                    'data_class' => 'Theaterjobs\MembershipBundle\Entity\Paymentmethod',
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
        return 'theaterjobs_membership_paymentmethod';
    }

}
