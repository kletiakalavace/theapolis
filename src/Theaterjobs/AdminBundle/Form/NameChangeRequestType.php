<?php
/**
 * Created by PhpStorm.
 * User: IHoxha
 * Date: 02/03/2018
 * Time: 17:55
 */

namespace Theaterjobs\AdminBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NameChangeRequestType
 * @package Theaterjobs\AdminBundle\Form
 *
 */
class NameChangeRequestType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('order', HiddenType::class)
            ->add('orderCol', HiddenType::class)
            ->add('choices', ChoiceType::class, [
                'choices' => $options['choices'],
                'choices_as_values' => true,
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => 'Theaterjobs\AdminBundle\Model\NameChangeRequestSearch',
            'translation_domain' => 'messages',
            'choices' => null
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_name_change_search_type';
    }

}