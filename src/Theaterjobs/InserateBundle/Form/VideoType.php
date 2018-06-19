<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VideoType
 * @package Theaterjobs\InserateBundle\Form
 */
class VideoType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('url', 'url',array('label'=>'form.label.video.url'));

        
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\InserateBundle\Entity\EmbededVideos'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'theaterjobs_inseratebundle_videos';
    }

}
