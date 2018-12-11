<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Theaterjobs\ProfileBundle\Form\DataTransformer\CreatorTransformer;
use Theaterjobs\ProfileBundle\Form\DataTransformer\DirectorTransformer;
use Theaterjobs\InserateBundle\Form\DataTransformer\OrganizationTransformer;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductionType
 * @package Theaterjobs\ProfileBundle\Form\Type
 * @DI\FormType
 */
class ProductionType extends AbstractType
{
    /**
     * @var EntityManager
     * @DI\Inject("doctrine.orm.entity_manager")
     */
    public $em;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                    'mapped' => true,
                    'required' => true,
                    'label' => 'people.edit.label.productionName',
                    'attr' => array('class' => 'tag-input-style placeholder-entercharact',
                        'multiple' => true,
                    ),
                    'constraints' => [
                        new Assert\NotBlank()
                    ]
                )
            )
            ->add('organizationRelated', TextType::class, array(
                    'label' => 'people.edit.label.organizationRelatedProduction',
                    "required" => true,
                    'mapped' => true,
                    'attr' => array('class' => 'tag-orga-input placeholder-entercharact',
                        'multiple' => true
                    )
                )
            )
            ->add('year', TextType::class, array(
                    "required" => true,
                    'mapped' => true,
                    'label' => 'people.edit.label.productionYear',
                    'attr' => array('class' => 'yearProduction',
                        /* 'placeholder' => 'people.edit.placeholder.productionYear'*/
                    ),
                    'constraints' => [
                        new Assert\NotBlank()
                    ]
                )
            )
            ->add('creators', TextType::class, array(
                    'data_class' => null,
                    'label' => 'people.edit.label.productionCreators',
                    'attr' => array('class' => 'tag-creator-input placeholder-entercharact',
                        'multiple' => true
                    ),
                    'constraints' => [
                        new Assert\NotBlank()
                    ]
                )
            )
            ->add('directors', TextType::class, array(
                    'data_class' => null,
                    'label' => 'people.edit.label.productionDirectors',
                    'attr' => array('class' => 'tag-director-input placeholder-entercharact',
                        'multiple' => true
                    ),
                    'constraints' => [
                        new Assert\NotBlank()
                    ]
                )
            );

        $builder->get('organizationRelated')
            ->addModelTransformer(new OrganizationTransformer($this->em));
        $builder->get('creators')
            ->addModelTransformer(new CreatorTransformer($this->em));
        $builder->get('directors')
            ->addModelTransformer(new DirectorTransformer($this->em));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\Production',
            'translation_domain' => 'forms'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_profilebundle_production';
    }
}
