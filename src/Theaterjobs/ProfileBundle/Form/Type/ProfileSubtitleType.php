<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * Master Data Form Type
 *
 * @category Form
 * @package  Theaterjobs\UserBundle\Form
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class ProfileSubtitleType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subtitle2', 'text', array(
                'label' => 'people.profile.label.undertitle',
                'required' => false,
                'attr' => array('placeholder' => 'people.show.placeholder.underTitle', 'maxlength' => 40)
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
                'validation_groups' => false,
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
        return 'tj_profile_undertitle';
    }

}
