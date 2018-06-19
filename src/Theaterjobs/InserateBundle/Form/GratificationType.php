<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\Mapping\Entity;

/**
 * Gratification Form Type
 *
 * @category Form
 * @package  Theaterjobs\MainBundle\Form
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class GratificationType extends AbstractType
{

    /**
     * (non-PHPdoc)
     * @param OptionsResolver $resolver
     *
     * @see \Symfony\Component\Form\AbstractType::setDefaultOptions()
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'translation_domain' => 'forms',
                'class' => 'TheaterjobsInserateBundle:Gratification',
                'property' => 'name',
                'empty_value' => false,
                'empty_data' => null,
                'label' => 'work.new.label.gratification',
                'expanded' => true,
                'multiple' => false,
            )
        );
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\AbstractType::getParent()
     *
     * @return Entity()
     */
    public function getParent()
    {
        return 'entity';
    }

    /** (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'tj_inserate_form_gratification';
    }
}
