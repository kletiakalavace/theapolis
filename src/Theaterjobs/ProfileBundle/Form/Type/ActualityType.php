<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\Translator;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class ActualityType
 * @package Theaterjobs\ProfileBundle\Form\Type
 *
 * @DI\FormType
 */
class ActualityType extends AbstractType
{
    /**
     * @DI\Inject("translator")
     * @var $trans Translator
     */
    public $trans;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profileActualityText', TextareaType::class, [
                'label' => 'form.profile.label.actualityText.description',
                'constraints' => [
                    new Assert\Length([
                        'min' => 0
                    ])
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\Profile'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_profile_actuality_type';
    }
}
