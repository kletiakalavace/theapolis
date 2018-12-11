<?php

namespace Theaterjobs\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\InserateBundle\Form\DataTransformer\JobTransformer;

/**
 * @DI\FormType
 */
class JobAdminCommentsType extends AbstractType
{
    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var \Doctrine\ORM\EntityManager
     */
    public $em;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description','textarea',array(
                'label' => false
            ))
            ->add('inserate', 'text', array(
                    'required'=>false,
                    'attr'=>array('class'=>'hidden'),
                    'label' => false
                )
            );

        $transformerjob = new JobTransformer($this->em);
        $builder->get('inserate')->addModelTransformer($transformerjob);
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\InserateBundle\Entity\AdminComments'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tj_admin_job_admin_comments';
    }
}
