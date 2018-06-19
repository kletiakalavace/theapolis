<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 26/02/2018
 * Time: 20:53
 */

namespace Theaterjobs\AdminBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class JobListType
 * @package Theaterjobs\AdminBundle\Form
 *
 */
class JobListType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            'data_class' => 'Theaterjobs\AdminBundle\Model\JobListSearch',
            'translation_domain' => 'messages',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_job_search_type';
    }

}