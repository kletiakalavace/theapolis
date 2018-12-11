<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Profile Form Type
 *
 * @category Form
 * @package  Theaterjobs\ProfileBundle\Form
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class ProfileDataType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('availableLocations', 'text', array('label' => 'people.edit.label.livingWay',
                'required' => false,
                'attr' => array(
                    'maxlength' => 250,
                ),
            ))
            ->add('personalData', PersonalDataType::class, [
                'label' => false,
                'voice_category_choice_list' => $options['voice_category_choice_list']
            ])
            ->add('skillSection', SkillSectionType::class, [
                'label' => false,
                'drive_licence_choice_list' => $options['drive_licence_choice_list']
            ])
            ->add('mediaPdf', 'collection', array(
                    'required' => true,
                    'type' => MediaPdfType::class,
                    'prototype' => true,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'by_reference' => false,
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\Profile',
            'drive_licence_choice_list' => null,
            'voice_category_choice_list' => null
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_profile_data';
    }

}
