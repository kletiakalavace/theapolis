<?php

namespace Theaterjobs\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class JobHuntType
 * @package Theaterjobs\AdminBundle\Form
 *
 */
class JobHuntType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['required' => true])
            ->add('url', UrlType::class, ['required' => true])
            ->add('intervalDays', IntegerType::class, ['required' => true])
            ->add('description', TextType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Theaterjobs\AdminBundle\Entity\JobHunt'
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_adminbundle_jobhunt';
    }
}
