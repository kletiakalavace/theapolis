<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class MediaAudioType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uploadFile', VichFileType::class, [
                'label' => false,
                'required' => isset($options['attr']['isEdit']) ? $options['attr']['isEdit'] ? false : true : true,
                'attr' => ['class' => 'uploadAudio', 'accept' => 'audio/*'],
                'allow_delete' => false, // not mandatory, default is true
                'download_link' => false,
            ])
            ->add('title', 'text', array(
                'label' => 'people.edit.label.titleAudio',
                'required' => true
            ))
            ->add('uploadFileImage', VichFileType::class, [
                    'label' => false,
                    'required' => false,
                    'attr' => ['class' => 'uploadAudioImage', 'accept' => 'image/*'],
                    'allow_delete' => false, // not mandatory, default is true
                    'download_link' => false,
                ]
            )
            ->add('copyrightText', 'text', array(
                'label' => false,
                'attr' => array('class' => 'year startDate',
                    'placeholder' => 'people.edit.label.audioImage.copyright'
                ),
                'required' => false
            ));


    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\MediaAudio'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_profilebundle_media_audio';
    }

}
