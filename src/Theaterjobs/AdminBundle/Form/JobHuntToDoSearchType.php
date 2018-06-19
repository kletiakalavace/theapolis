<?php
/**
 * Created by PhpStorm.
 * User: IHoxha
 * Date: 25/02/2018
 * Time: 11:46
 */

namespace Theaterjobs\AdminBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class JobHuntToDoSearchType
 * @package Theaterjobs\AdminBundle\Form
 *
 */
class JobHuntToDoSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['required' => false])
            ->add('order', HiddenType::class)
            ->add('orderCol', HiddenType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => 'Theaterjobs\AdminBundle\Model\JobHuntToDoSearch',
            'translation_domain' => 'messages',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_job_hunt_todo_search_type';
    }


}