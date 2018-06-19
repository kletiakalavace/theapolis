<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Theaterjobs\InserateBundle\Form\DataTransformer\OrganizationTransformer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\FormType
 */
class QualificationsType extends AbstractType
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
        $profileCategories = $options['profile_category_choice_list'];
        $builder
            ->add('categories', 'theaterjobs_category_category', array(
                    'label' => 'people.edit.label.categories',
                    'choices_as_values' => true,
                    'empty_value' => 'people.education.categories.noSelection',
                    'required' => false,
                    'choice_list' => $profileCategories,
                    'multiple' => false,
                    "attr" => array(
                        "class" => "qualificationSectionCategory"
                    )
                )
            )
            ->add('educationChoice', 'choice', array(
                    'required' => false,
                    'label' => 'form.profile.label.education.select',
                    'choices' => array('1' => 'form.profile.education.choice.yes', '0' => 'form.profile.education.choice.no'),
                    'empty_value' => 'form.profile.education.choice.empty',
                )
            )
            ->add('qualificationChoice', 'choice', array(
                    'required' => false,
                    'mapped' => true,
                    'label' => 'form.profile.label.qualification.select',
                    'choices' => array('1' => 'form.profile.qualification.choice.yes', '0' => 'form.profile.qualification.choice.no'),
                    'empty_value' => 'form.profile.qualification.choice.empty',
                    'attr' => array('class' => 'qualificationChoice')
                )
            )
            ->add('educationtype', 'choice', array(
                'label' => 'people.edit.label.educationType',
                'choices_as_values' => true,
                'expanded' => false,
                'empty_value' => 'profile.education.educationtype.noSelection',
               'required' => true,
                'multiple' => false,
                'attr' => array(
                    'placeholder' => 'profile.education.educationtype.noSelection',
                ),
                'choices' => array(
                    'university' => 'profile.education.university',
                    'professionalTraining' => 'profile.education.professionalTraining',
                    'vocationalSchool' => 'profile.education.vocationalSchool',
                    'furtherEducation' => 'profile.education.furtherEducation',
                ),
            ))
            ->add('organizationRelated', 'text', array(
                    'label' => 'people.edit.label.organizationRelatedEducation',
                    "required" => true,
                    'mapped' => true,
                    'attr' => array('class' => 'tag-orga-input placeholder-entercharact',
                        'multiple' => true
                    )
                )
            )
            ->add('startDate', 'number', array(
                'label' => 'people.edit.label.startDate',
                "required" => true,
                'attr' => array('class' => 'year startDate')
            ))
            ->add('endDate', 'number', array(
                'label' => 'people.edit.label.endDate',
                'required' => true,
                'attr' => array('class' => 'year endDate')
            ))
            ->add('finished', 'checkbox', array(
                'label' => 'people.edit.label.finished',
                'required' => false
            ))
            ->add('profession', 'text', array(
                    'label' => 'people.edit.label.jobProffesion',
                    'required' => true,
                    'attr' => array(
                        'maxlength' => 50,
                    )
                )
            )
            ->add('experience', 'choice', array(
                'label' => 'people.edit.label.experience',
                'required' => false,
                'choices' => array(
                    'no_experience' => 'form.profile.experience.choice.no_experience',
                    'less_than_two_years' => 'form.profile.experience.choice.less_than_two_years',
                    'two_four_years' => 'form.profile.experience.choice.two_four_years',
                    'more_than_four_years' => 'form.profile.experience.choice.more_than_four_years',
                )
            ))
            ->add('managmentResponsibility', 'checkbox', array(
                'label' => 'people.edit.label.managementResponsibility',
                'required' => false
            ))
            ->add('usedNameCheck', CheckboxType::class, array(
                    'label' => 'people.edit.label.participation.usedNameCheck',
                    'required' => false,
                    'mapped' => true,
                    'value' => false,
                )
            );
            $opts = [
                'label' => 'people.edit.label.usedName',
                'attr' => [ 'maxlength' => 50, 'minlength' => 3],
                'required' => true
            ];
            if ($options['profile']) {
                $opts['data'] = $options['profile']->defaultName();
            }
            $builder->add('usedName', 'text', $opts);

        $builder->get('organizationRelated')->addModelTransformer(new OrganizationTransformer($this->em));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\Qualification',
            'profile' => null,
            'profile_category_choice_list' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tj_profile_qualifications';
    }

}