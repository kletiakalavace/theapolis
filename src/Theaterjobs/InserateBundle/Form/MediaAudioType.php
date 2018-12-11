<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MediaAudioType
 * @package Theaterjobs\InserateBundle\Form
 */
class MediaAudioType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('uploadFile', 'vich_file', array(
                'required' => true,
                'attr' => ['class' => 'uploadAudio hidden', 'accept' => 'audio/*'],
                'allow_delete'  => false, // not mandatory, default is true
                'download_link' => false,
            ))
            ->add('title', 'text', array(
                'label' => 'form.profile.label.media.audio.title',
                'required' => true
            ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Theaterjobs\InserateBundle\Entity\MediaAudio'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_inseratebundle_media_audio';
    }

}
