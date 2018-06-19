<?php

namespace Theaterjobs\ProfileBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Theaterjobs\CategoryBundle\Form\Type\CategoryType;
use Theaterjobs\ProfileBundle\Entity\Occupation;
use Theaterjobs\ProfileBundle\Entity\Production;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ProductionParticipationsType
 * @package Theaterjobs\ProfileBundle\Form\Type
 * @DI\FormType
 */
class ProductionParticipationsType extends AbstractType
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
        $mode = $options['intention'] == 'edit';
        $builder
            ->add('production', 'theaterjobs_profilebundle_production', array(
                    'label' => false,
                    'mapped' => true,
                    "required" => true,
                    'data_class' => Production::class,
                    'read_only' => $mode
                )
            )
            ->add('start', DateType::class, array(
                    'widget' => 'single_text',
                    'label' => 'people.edit.label.participation.startDateProduction',
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
                    'label' => 'people.edit.label.participation.endDateProduction',
                    'required' => true,
                    'mapped' => true,
                    'format' => 'dd.MM.yyyy',
                    'attr' => array('class' => 'year endDate'),
                    'constraints' => [
                        new Assert\Date()
                    ]
                )
            )
            ->add('ongoing', CheckboxType::class, array(
                    'label' => 'people.edit.label.participation.ongoingProduction',
                    'required' => false,
                    'mapped' => true,
                    'value' => false,
                )
            )
            ->add('occupation', CategoryType::class, array(
                    'label' => 'people.edit.label.Occupation',
                    'choice_list' => $options['category_choice_list'],
                    'required' => true,
                    'expanded' => false,
                    'multiple' => false,
                    'choices_as_values' => true,
                    'empty_value' => 'people.edit.placeholder.noOccupation',
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
            ->add('occupationDescription', OccupationType::class, array(
                    'label' => false,
                    'mapped' => true,
                    'data_class' => Occupation::class
                )
            )->add('usedNameCheck', CheckboxType::class, array(
                    'label' => 'people.edit.label.participation.usedNameCheck',
                    'required' => false,
                    'mapped' => true,
                    'value' => false,
                )
            )->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);;

        $opts = [
            'label' => 'people.edit.label.usedName',
            'attr' => [ 'maxlength' => 50, 'minlength' => 3 ],
            'required' => true,
        ];
        if ($options['profile']) {
            $opts['data'] = $options['profile']->defaultName();
        }
        $builder->add('usedName', 'text', $opts);
    }

    /**
     * Listener to handle the selecting of an existing Production
     */
    public function onPostSubmit(FormEvent $event) {

        $form = $event->getForm();
        $participation = $form->getData();
        $production = $participation->getProduction();
        if(null !== $production->getName() && is_numeric($production->getName())){
            $prod = $this->em->getRepository('TheaterjobsProfileBundle:Production')->find($production->getName());
            $participation->setProduction($prod);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Theaterjobs\ProfileBundle\Entity\ProductionParticipations',
            'category_choice_list' => null,
            'translation_domain' => 'forms',
            'intention' => 'new',
            'profile' => null
        ]);

        $resolver->setRequired(['category_choice_list']);
        $resolver->setAllowedTypes('category_choice_list', 'Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_profilebundle_productionparticipations';
    }
}
