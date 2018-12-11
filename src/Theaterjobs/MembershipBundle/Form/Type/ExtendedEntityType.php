<?php

namespace Theaterjobs\MembershipBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * ExtendedEntityType
 *
 * @category Form
 * @package  Theaterjobs\MembershipBundle\Form\Type
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\FormType
 */
class ExtendedEntityType extends AbstractType {

    /** @DI\Inject("property_accessor") */
    public $propertyAccessor;

    /** @DI\Inject("doctrine.orm.entity_manager") */
    public $em;

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options) {
        parent::finishView($view, $form, $options);
        foreach ($view->vars['choices'] as $choice) {
            $additionalAttributes = array();
            foreach ($options['option_attributes'] as $attributeName => $choicePath) {
                $additionalAttributes[$attributeName] = $this->propertyAccessor->getValue($choice->data, $choicePath);
            }

            $choice->attr = array_replace(isset($choice->attr) ? $choice->attr : array(), $additionalAttributes);
        }

        if ($options['expanded']) {
            foreach ($view as $childView) {
                $additionalAttributes = array();
                foreach ($options['option_attributes'] as $attributeName => $choicePath) {
                    $entityID = $childView->vars['value'];
                    $entity = $view->vars['choices'][$entityID]->data;
                    $additionalAttributes[$attributeName] = $this->propertyAccessor->getValue($entity, $choicePath);
                }

                $childView->vars['attr'] = array_replace(isset($childView->attr) ? $childView->attr : array(), $additionalAttributes);
            }
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(
                array(
                    'option_attributes' => array(),
                )
        );
    }

    /**
     * @return string
     */
    public function getParent() {
        return 'entity';
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName() {
        return 'extended_entity';
    }

}
