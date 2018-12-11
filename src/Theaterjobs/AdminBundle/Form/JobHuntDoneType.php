<?php

namespace Theaterjobs\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class JobHuntDoneType
 * @package Theaterjobs\AdminBundle\Form
 *
 * @DI\FormType
 */
class JobHuntDoneType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('comment')
            ->add('createdAt')
            ->add('profile')
            ->add('jobHunt');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Theaterjobs\AdminBundle\Entity\JobHuntDone'
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_adminbundle_jobhuntdone';
    }
}
