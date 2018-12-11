<?php

namespace Theaterjobs\MembershipBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Membership Form Type
 *
 * @category Form
 * @package  Theaterjobs\MembershipBundle\Form\Type
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\FormType
 */
class MembershipType extends AbstractType
{

    /**
     * (non-PHPdoc)
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options An arrray with options.
     *
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add(
                'title', 'extended_entity', array(
                'label' => 'form.label.membership',
                'class' => 'TheaterjobsMembershipBundle:Membership',
                'property' => 'title',
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'cascade_validation' => true,
                'option_attributes' => array('description' => 'description', 'price' => 'price', 'duration' => 'duration'),
                )
            )
        ;
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
                'data_class' => 'Theaterjobs\MembershipBundle\Entity\Membership',
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
        return 'theaterjobs_membership_membership';
    }

}
