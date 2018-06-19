<?php
/**
 * Created by PhpStorm.
 * User: rover
 * Date: 01/03/2018
 * Time: 15:15
 */

namespace Theaterjobs\AdminBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class JobRequestType
 * @package Theaterjobs\AdminBundle\Form
 *
 */
class JobRequestType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, [
                    'label' => 'admin.pendingJobPublications.statusChoices',
                    'attr' => ['class' => 'form-control'],
                    'choices' => [
                        'admin.pendingJobPublications.statusChoice.newlyPublished' => 'new',
                        'admin.pendingJobPublications.statusChoice.pendingAdminApproval' => 'admin',
                        'admin.pendingJobPublications.statusChoice.pendingEmailConfirmation' => 'email'
                    ],
                    'choices_as_values' => true,
                ]
            )
            ->add('order', HiddenType::class)
            ->add('orderCol', HiddenType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => 'Theaterjobs\AdminBundle\Model\JobRequestSearch',
            'translation_domain' => 'messages',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin_job_request_search_type';
    }

}