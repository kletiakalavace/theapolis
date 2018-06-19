<?php

namespace Theaterjobs\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\InserateBundle\Form\DataTransformer\OrganizationTransformer;

/**
 * @DI\FormType
 */
class OrganizationAdminCommentsType extends AbstractType {

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var \Doctrine\ORM\EntityManager
     */
    public $em;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $transformerOrg = new OrganizationTransformer($this->em);
        $builder
                ->add('description', 'textarea', array(
                    'label' => false,
                    'attr' => array(
                        /*'placeholder' => 'news.show.placeholder.leaveComment',*/
                    )
                        )
                )
            ->add('organization', 'text', array(
                'label' => false,
                'required' => false,
                'attr' => array('class' => 'hidden'),
            ));
        $builder->get('organization')->addModelTransformer($transformerOrg);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\InserateBundle\Entity\AdminComments'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'tj_admin_admin_comments_create_orga';
    }

}
