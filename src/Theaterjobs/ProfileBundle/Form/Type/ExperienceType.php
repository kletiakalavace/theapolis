<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Theaterjobs\CategoryBundle\Form\Type\CategoryType;
use Theaterjobs\InserateBundle\Form\DataTransformer\OrganizationTransformer;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ExperienceType
 * @package Theaterjobs\ProfileBundle\Form\Type
 * @DI\FormType
 */
class ExperienceType extends AbstractType
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
            ->add('organization', TextType::class, array(
                    'label' => 'people.edit.label.organization',
                    "required" => true,
                    'mapped' => true,
                    'attr' => array('class' => 'tag-orga-input placeholder-entercharact',
                        'multiple' => true
                    ),
                    'constraints' => [
                        new Assert\NotBlank()
                    ]
                )
            )
            ->add('occupation', CategoryType::class, array(
                    'label' => 'experience.edit.label.Occupation-category',
                    'choice_list' => $options['category_choice_list'],
                    'required' => true,
                    'expanded' => false,
                    'multiple' => false,
                    'choices_as_values' => true,
                    'empty_value' => 'people.edit.placeholder.noOccupation',
                    'attr' => array('class' => 'experience_occupation',
                        'placeholder' => "people.edit.placeholder.noOccupation"
                    ),
                    'choice_attr' => function ($allChoices, $currentChoiceKey) {
                        if ($allChoices->getIsPerformanceCategory()) {
                            return ['data-performance' => 'true'];
                        } else {
                            return ['data-performance' => 'false'];
                        }
                    },
                    'constraints' => [
                        new Assert\NotBlank()
                    ]
                )
            )
            ->add('assistant', CheckboxType::class, array(
                    'label' => 'people.edit.label.occupationAssistant',
                    'required' => false,
                    'mapped' => true,
                    'value' => false,
                )
            )
            ->add('management', CheckboxType::class, array(
                    'label' => 'people.edit.label.occupationManagement',
                    'required' => false,
                    'mapped' => true,
                    'value' => false,
                )
            )
            ->add('description', TextType::class, array(
                    'label' => 'experience.edit.label.nameProfession',
                    'required' => true,
                    'attr' => array('class' => "description-modalproduction",
                        'maxlength' => 100,
                    ),
                    'constraints' => [
                        new Assert\NotBlank()
                    ]
                )
            )
            ->add('start', DateType::class, array(
                    'widget' => 'single_text',
                    'label' => 'people.edit.label.participation.startDateExperience',
                    "required" => true,
                    'mapped' => true,
                    'format' => 'dd.MM.yyyy',
                    'attr' => array('class' => 'year startDate'),
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Date()
                    ]
                )
            )
            ->add('end', DateType::class, array(
                    'widget' => 'single_text',
                    'label' => 'people.edit.label.participation.endDateExperience',
                    'required' => true,
                    'mapped' => true,
                    'format' => 'dd.MM.yyyy',
                    'attr' => array(
                        'class' => 'year endDate',
                    ),
                    'constraints' => [
                        new Assert\Date()
                    ]
                )
            )
            ->add('ongoing', CheckboxType::class, array(
                    'label' => 'people.edit.label.participation.ongoingExperience',
                    'required' => false,
                    'mapped' => true,
                    'value' => false,
                )
            )->add('usedNameCheck', CheckboxType::class, array(
                    'label' => 'people.edit.label.participation.usedNameCheck',
                    'required' => false,
                    'mapped' => true,
                    'value' => false,
                )
            );
        $opts = [
            'label' => 'people.edit.label.usedName',
            'required' => true,
            'attr' => [ 'maxlength' => 50, 'minlength' => 3],
        ];
        if ($options['profile']) {
            $opts['data'] = $options['profile']->defaultName();
        }
        $builder->add('usedName', 'text', $opts);

        $builder->get('organization')->addModelTransformer(new OrganizationTransformer($this->em));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\Experience',
            'category_choice_list' => null,
            'profile' => null,
            'translation_domain' => 'forms'
        ));

        $resolver->setRequired(array('category_choice_list'));
        $resolver->setAllowedTypes('category_choice_list', 'Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_profilebundle_experience';
    }
}
