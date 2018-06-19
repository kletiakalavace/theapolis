<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormOfOranizationType
 *
 * @category Form
 * @package  Theaterjobs\MainBundle\Form
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class FormOfOrganizationType extends AbstractType
{

    /**
     * (non-PHPdoc)
     * @param OptionsResolver $resolver
     *
     * @see \Symfony\Component\Form\AbstractType::setDefaultOptions()
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(
                array(
                    'translation_domain' => 'forms',
                    'class' => 'TheaterjobsInserateBundle:FormOfOrganization',
                    'property' => 'name',
                    'empty_value' => 'organization.edit.choice.form_of_organization.empty_value',
                    'empty_data' => null,
                    'label' => 'form.label.form_of_organization',
                )
        );
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\AbstractType::getParent()
     *
     * @return Entity()
     */
    public function getParent() {
        return 'entity';
    }

    /** (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     * @return string
     */
    public function getName() {
        return 'tj_inserate_form_form_of_organization';
    }

}
