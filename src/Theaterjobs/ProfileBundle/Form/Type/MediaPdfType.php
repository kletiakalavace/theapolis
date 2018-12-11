<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichFileType;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class MediaPdfType
 * @package Theaterjobs\ProfileBundle\Form\Type
 * @DI\FormType
 */
class MediaPdfType extends AbstractType
{
    /**
    *
    * @DI\Inject("translator")
    * @var Translator
    */
    public $trans;

    /** @DI\Inject("%pdf_file_size%") */
    public $maxSize;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'label' => 'people.edit.label.titlePdf',
                'required' => true,
                'attr' => array(
                    'maxlength' => 30,
                ),
            ))

            ->add('uploadFile', VichFileType::class, array(
                'label' => false,
                'required' => isset($options['attr']['isEdit']) ? $options['attr']['isEdit'] ? false : true : true,
                'attr' => ['class' => 'uploadPdf custom-file-input', 'accept' => 'application/pdf,application/x-pdf'],
                'allow_delete' => false, // not mandatory, default is true
                'download_link' => false,
                'constraints' => [
                    new File([
                        'maxSize' => $this->maxSize,
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => $this->trans->trans('profile.mediaType.notValid.pdf.Format', [], 'forms'),
                    ])
                ]
            ));


    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\MediaPdf'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_profilebundle_media_pdf';
    }

}
