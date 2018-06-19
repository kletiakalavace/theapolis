<?php

namespace Theaterjobs\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

/**
 * PlaceOfAction Form Type
 *
 * @category Form
 * @package  Theaterjobs\MainBundle\Form\Type
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class PlaceOfActionType extends AbstractType {

    /**
     * (non-PHPdoc)
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options An arrray with options.
     *
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add(
                    'is_localized', 'choice', array(
                'label' => false,
                'choices' => array(
                    0 => 'form.choice.job.place_of_action.mobile',
                    1 => 'form.choice.job.place_of_action.localized',
                ),
                'required' => false,
                'expanded' => true,
                'multiple' => false,
                'mapped' => false,
                'empty_value' => false,
                    )
            );
    }

    /**
      /* (non-PHPdoc)
     *
     * @param FormView      $view    The form view.
     * @param FormInterface $form    The form interface
     * @param array         $options An option array.
     *
     * @see \Symfony\Component\Form\AbstractType::finishView()
     */
    public function finishView(FormView $view, FormInterface $form, array $options) {
        $form->get('is_localized')->data = 1;
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
                    'data_class' => 'Theaterjobs\MainBundle\Entity\Address',
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
        return 'theaterjobs_main_address_place_of_action';
    }

}
