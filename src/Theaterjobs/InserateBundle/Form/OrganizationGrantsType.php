<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class OrganizationGrantsType
 * @package Theaterjobs\InserateBundle\Form
 * @DI\FormType
 */
class OrganizationGrantsType extends AbstractType
{
    /**
     * @DI\Inject("translator")
     * @var Translator $translator
     */
    public $translator;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('season', 'season_choice', array(
                'label' => 'organization.edit.label.season',
                'attr' => array('class' => 'visitors_season')
            ))
            ->add('budget', MoneyType::class, array(
                'label' => 'organization.edit.label.budget',
                'grouping' => true,
                'scale' => 0,
                'currency' => '',
                'required' => false,
                'attr' => array(
                    'placeholder' => false,
                    'class' => 'budgetInput'
                ),
                'constraints' => [
                    new Assert\Range(['min' => 0]),
                    new Assert\Callback([$this, 'validate'])
                ]))
            ->add('grants', MoneyType::class, array(
                'label' => 'organization.edit.label.grants',
                'grouping' => true,
                'scale' => 0,
                'currency' => '',
                'required' => false,
                'attr' => array(
                    'placeholder' => false,
                    'class' => 'grantsInput'
                ),
                'constraints' => [
                    new Assert\Range(['min' => 0])
                ]))
            ->add('moreInfo', 'text', array(
                'label' => 'organization.edit.label.moreInfo',
                'attr' => array(
                    'placeholder' => false,
                    'maxlength' => 100,
                )
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\InserateBundle\Entity\OrganizationGrants'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tj_inserate_organization_grants_type';
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function validate($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $data = $form->getData();
        $orgaGrants = $data->getOrganizationGrants();
        foreach($orgaGrants as $orgaGrant) {
            if (null === $orgaGrant->getBudget() && null === $orgaGrant->getGrants()) {
                $context->buildViolation($this->translator->trans('organization.grants.required.at.least.one'))
                    ->addViolation();
            }
        }
    }
}
