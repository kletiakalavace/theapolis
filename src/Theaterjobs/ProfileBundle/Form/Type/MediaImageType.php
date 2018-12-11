<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Theaterjobs\MainBundle\Form\DataTransformer\ImageStringToFileTransformer;

/**
 * MediaImageType Form Type
 *
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

        $builder
            ->add('path', 'file', [
                'label' => 'form.profile.label.media.image.file',
                'mapped' => false,
                'required' => true,
                'attr' => ['class' => 'profileUpload note-image-input', 'accept' => 'image/*'],
            ])
            ->add('uploadFile', HiddenType::class, [
                'attr' => ['class' => 'imageSrc'],
            ])
            ->add('isProfilePhoto', HiddenType::class)
            ->add('title', 'text', [
                'label' => 'people.edit.label.titleImage',
                'required' => true
            ])
            ->add('filter', HiddenType::class, [
                'attr' => ['class' => 'filter'],
            ])
            ->add('copyrightText', HiddenType::class);

        $builder->get('uploadFile')->addModelTransformer($transformer);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\MediaImage'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_profilebundle_media_image';
    }

}
