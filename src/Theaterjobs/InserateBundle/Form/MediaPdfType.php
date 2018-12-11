<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MediaPdfType
 * @package Theaterjobs\InserateBundle\Form
 */
class MediaPdfType extends AbstractType
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
                'attr' => ['class' => 'uploadPdf hidden', 'accept' => 'application/pdf,application/x-pdf'],
                'allow_delete'  => false, // not mandatory, default is true
                'download_link' => false,
            ))
            ->add('title', 'text', array(
                'label' => 'people.edit.label.titlePdf',
                'required' => true
            ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Theaterjobs\InserateBundle\Entity\MediaPdf'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_inseratebundle_media_pdf';
    }

}
