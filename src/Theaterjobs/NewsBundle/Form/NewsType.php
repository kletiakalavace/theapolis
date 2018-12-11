<?php

namespace Theaterjobs\NewsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Theaterjobs\MainBundle\Form\DataTransformer\ImageStringToFileTransformer;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraints as Assert;
use Theaterjobs\NewsBundle\Form\DataTransformer\ProfileTransformer;

/**
 * News Form Type
 *
 * @DI\FormType
 */
class NewsType extends AbstractType
{

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var Doctrine\ORM\EntityManager
     */
    public $em;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('title', TextType::class, array(
                    'label' => 'news.edit.label.title',
                    'required' => true,
                    'attr' => array(
                        'maxlength' => 90
                    ),
                    'constraints' => [
                        new Assert\NotBlank()
                    ]
                )
            )
            ->add('pretitle', TextType::class, array(
                    'label' => 'news.edit.label.pretitle',
                    'required' => true,
                    'attr' => array(
                        'maxlength' => 90
                    ),
                    'constraints' => [
                        new Assert\NotBlank()
                    ]
                )
            )
            ->add('shortDescription', TextareaType::class, array(
                    'label' => 'news.edit.label.shortDescription',
                    'required' => true,
                    'attr' => array(
                        'maxlength' => 520
                    ),
                    'constraints' => [
                        new Assert\NotBlank()
                    ]
                )
            )
            ->add('description', TextareaType::class, array(
                    'label' => 'news.edit.label.description',
                    'required' => true,
                    'attr' => array(),
                    'constraints' => [
                        new Assert\NotBlank()
                    ]
                )
            )
            ->add('organizations_helper', TextType::class, array(
                'label' => 'news.edit.label.organization',
                'mapped' => false,
                'required' => false,
                'attr' => array('class' => 'tag-input-style placeholder-entercharact',
                    'multiple' => true
                ),
            ))
             ->add('users', TextType::class, array(
                'label' => 'news.edit.label.users',
                'mapped' => true,
                'required' => false,
                'attr' => array('class' => 'tag-input-style placeholder-entercharact',
                    'multiple' => true
                ),
            ))
            ->add('uploadFile', 'vich_file', array(
                'label' => 'news.edit.label.image',
                'required' => false,
                'attr' => ['class' => 'uploadAudioImage', 'accept' => 'image/*'],
                'allow_delete' => false,
                'download_link' => false,

            ))
//            ->add('category', EntityType::class, array(
//                'required' => true,
//                'class' => 'TheaterjobsCategoryBundle:Category',
//                'query_builder' => function (\Theaterjobs\CategoryBundle\Entity\CategoryRepository $er) {
//                    return $er->createQueryBuilder('category')
//                        ->innerJoin('category.parent', 'parent')
//                        ->where('category.removedAt is NULL')
//                        ->andWhere('parent.title= :root')
//                        ->setParameter('root', "categories of news");
//                },
//                'constraints' => [
//                    new Assert\NotBlank()
//                ],
//                'property' => 'title',
//                'empty_value' => 'news.edit.placeholder.noChoice',
//                'empty_data' => null,
//                'label' => 'news.edit.label.category',
//                'expanded' => false,
//                'multiple' => false
//            ))
            ->add('tags_helper', TextType::class, array(
                'label' => 'news.edit.label.tags',
                'mapped' => false,
                'required' => false,
                'attr' => array('class' => 'tag-input-style placeholder-entercharact',
                    'multiple' => true
                ),
            ))
            ->add('imageDescription', TextType::class, array(
                'label' => 'news.edit.label.imageDescription',
                'required' => false,
                 'attr' => array(
                    'maxlength' => 35
                ),
            ))
            ->add('geolocation', TextType::class, array(
                'required' => false,
                'attr' => array('class' => 'map-input')
            ));
        $builder->get('users')
            ->addModelTransformer(new ProfileTransformer($this->em));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'cascade_validation' => false,
//            'category_choice_list' => null,
            'data_class' => 'Theaterjobs\NewsBundle\Entity\News',
            'crsf_protection' => false
        ));
    }
}
