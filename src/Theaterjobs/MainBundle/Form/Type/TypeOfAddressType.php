<?php

namespace Theaterjobs\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * TypeOfAddress Form Type
 *
 * @category Form
 * @package  Theaterjobs\MainBundle\Form\Type
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class TypeOfAddressType extends AbstractType
{
    /**
     * (non-PHPdoc)
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options An arrray with options.
     *
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name', 'entity', array(
                    'class' => 'TheaterjobsMainBundle:TypeOfAddress',
                    'property' => 'name',
                    'expanded' => false,
                    'multiple' => false,
                    'cascade_validation' => true,
                )
            );
    }

    /**
     * (non-PHPdoc)
     * @param OptionsResolverInterface $resolver
     *
     * @see \Symfony\Component\Form\AbstractType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'forms',
                'data_class' => 'Theaterjobs\MainBundle\Entity\TypeOfAddress',
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
        return 'theaterjobs_main_type_of_address';
    }
}

