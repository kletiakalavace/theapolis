<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Theaterjobs\MainBundle\Form\DataTransformer\ImageStringToFileTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class MediaImageType
 * @package Theaterjobs\InserateBundle\Form
 */
class MediaImageType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ImageStringToFileTransformer();

        $builder->add('path', 'file', [
            'required' => true,
            'mapped' => false,
            'attr' => ['class' => 'hidden imgUpload', 'accept' => 'image/*']
        ])
            ->add('uploadFile', HiddenType::class, [
                'attr' => ['class' => 'uploadSrc']
            ])
            ->add('title', 'text', array(
                    'label' => false,
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'form.placeholder.piece.img_title'
                    )
                )
            );
        $builder->get('uploadFile')->addModelTransformer($transformer);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Theaterjobs\InserateBundle\Entity\MediaImage'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_inseratebundle_media_image';
    }

}
