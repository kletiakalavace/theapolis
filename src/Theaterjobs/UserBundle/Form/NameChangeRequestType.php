<?php

namespace Theaterjobs\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NameChangeRequestType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oldName')
            ->add('newName')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('status')
            ->add('updatedBy')
            ->add('createdBy')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Theaterjobs\UserBundle\Entity\NameChangeRequest'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_userbundle_namechangerequest';
    }
}
