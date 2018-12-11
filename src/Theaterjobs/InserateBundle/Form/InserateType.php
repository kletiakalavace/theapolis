<?php

namespace Theaterjobs\InserateBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Theaterjobs\InserateBundle\Entity\OrganizationRepository;
use Theaterjobs\InserateBundle\Form\DataTransformer\InserateToNumberTransformer;
use Theaterjobs\InserateBundle\Form\DataTransformer\OrganizationTransformer;
use Theaterjobs\MainBundle\Form\DataTransformer\ImageStringToFileTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Inserate Form Type
 *
 * @category Form
 * @package  Theaterjobs\InserateBundle\Form
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @DI\FormType
 */
class InserateType extends AbstractType
{

    /**
     * @DI\Inject("theaterjobs.inserate.organization.repository")
     * @var OrganizationRepository
     */
    public $organizationRepository;

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var EntityManager
     */
    public $em;

    /**
     * @DI\Inject("security.authorization_checker")
     * @var AuthorizationChecker $security
     */
    public $security;

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $security = $this->security;
        $transformer = new InserateToNumberTransformer($this->em);
        $transformerImg = new ImageStringToFileTransformer();
        $transformerOrg = new OrganizationTransformer($this->em);

        $builder
            ->add('title', 'text', array(
                    'label' => 'work.new.label.inserateTitle ',
                    'attr' => array(
                        'class'=>'placeholder-entercharact',
                    )
                )
            )
            ->add('description', 'textarea', array(
                    'label' => false,
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'form.placeholder.inserate.description',
                    )
                )
            )
            ->add('engagementStart', 'date', array(
                    'widget' => 'single_text',
                    'label' => 'work.new.label.engagementStart',
                    "required" => false,
                    'mapped' => true,
                    'format' => 'dd.MM.yyyy',
                    'attr' => array('class' => 'year startDate',
                        /* 'placeholder' => 'people.edit.placeholder.participation.startDate'*/
                    )
                )
            )
            ->add('engagementEnd', 'date', array(
                    'widget' => 'single_text',
                    'label' => 'work.new.label.engagementEnd',
                    'required' => false,
                    'mapped' => true,
                    'format' => 'dd.MM.yyyy',
                    'attr' => array('class' => 'year endDate',
                        /* 'placeholder' => 'people.edit.placeholder.participation.startDate'*/
                    )
                )
            )
            ->add('applicationEnd', 'date', array(
                    'label' => 'work.new.label.applicationEnd',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'attr' => array('class' => 'year endDate'),
                    'required' => false,
                )
            )
            ->add('publicationEnd', 'date', array(
                    'label' => 'work.new.label.publicationEnd',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'attr' => array('class' => 'year endDate'),
                    'required' => true,
                )
            )
            ->add('placeOfAction', 'theaterjobs_main_address_place_of_action', array(
                    'label' => false,
                    'required' => false,
                    'mapped' => true,
                )
            )
            ->add('path', 'file', [
                'label' => 'work.new.label.imageFile',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'hidden imgUpload', 'accept' => 'image/*']
            ])
            ->add('uploadFile', HiddenType::class, [
                'attr' => ['class' => 'uploadSrc']
            ])
            ->add('asap', 'checkbox', array(
                    'value' => 1,
                    'required' => false,
                    'label' => 'work.new.label.inserateAsap',
                )
            )
            ->add(
                $builder->create('parent', 'hidden')
                    ->addModelTransformer($transformer)
            );

        if ($security->isGranted("ROLE_ADMIN")) {
            $builder
                ->add('organization', 'text', array(
                    'mapped' => false,
                    'required' => false,
                    'label' => 'work.new.label.organization',
                    'attr' => [
                        'class' => 'placeholder-entercharact'
                    ]
                ));
        } else {
            $builder->add('organization', 'text', array(
                'mapped' => false,
                'required' => false,
                'label' => 'work.new.label.organization',
                'attr' => [
                    'class' => 'placeholder-entercharact'
                ]
            ));
        }
        $builder->add('mediaImage', 'collection', array(
            'required' => false,
            'type' => new MediaImageType(),
            'allow_delete' => true,
            'allow_add' => true,
            'prototype' => true,
            'by_reference' => false
        ))
            ->add('mediaPdf', 'collection', array(
                'required' => false,
                'type' => new MediaPdfType(),
                'allow_delete' => true,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false
            ))
            ->add('mediaAudio', 'collection', array(
                'required' => false,
                'type' => new MediaAudioType(),
                'allow_delete' => true,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false
            ))
            ->add('geolocation', 'text', array(
                'required' => false,
                'attr' => array('class' => 'map-input')
            ))
            ->add('videos', 'collection', array(
                'required' => false,
                'type' => new VideoType(),
                'allow_delete' => true,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false
            ))
            ->add('contact', 'text', array(
                'label' => 'form.label.inserate.contact',
                'mapped' => false,
                'required' => true
            ))
            ->add('email', 'text', array(
                'label' => 'form.label.inserate.email',
                'mapped' => false,
                'required' => false
            ));

        $builder->get('uploadFile')->addModelTransformer($transformerImg);
        $builder->get('organization')->addModelTransformer($transformerOrg);
    }

    /**
     * (non-PHPdoc)
     * @param OptionsResolver $resolver
     *
     * @see \Symfony\Component\Form\AbstractType::setDefaultOptions()
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array('category_choice_list'));
        $resolver->setAllowedTypes(array(
                'category_choice_list' => 'Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList',
            )
        );

        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'cascade_validation' => true,
            'user' => null,
            'category_choice_list' => null,
            'is_admin' => false,
        ));
    }

    /**
     *
     * @param array $options
     * @return array
     */
    public function getDefaultOptions(array $options)
    {
        return $options;
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_inserate';
    }

}
