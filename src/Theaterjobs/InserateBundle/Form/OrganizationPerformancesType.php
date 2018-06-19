<?php

namespace Theaterjobs\InserateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Organization Form Type
 *
 * @category Form
 * @package  Theaterjobs\MainBundle\Form\Type
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @DI\FormType
 */
class OrganizationPerformancesType extends AbstractType
{
    /**
     * @DI\Inject("translator")
     * @var Translator $translator
     */
    public $translator;

    /**
     * (non-PHPdoc)
     * @param FormBuilderInterface $builder The form builder.
     * @param array $options An arrray with options.
     *
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organizationVisitors', 'collection', array(
                'label' =>false,
                'required' => false,
                'type' => OrganizationVisitorsType::class,
                'allow_delete' => true,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false,
                'constraints' => [
                    new Assert\Callback([$this, 'validateVisitors'])
                ]
            ))
            ->add('organizationPerformance', 'collection', array(
                'label' => false,
                'required' => false,
                'type' => OrganizationPerformanceType::class,
                'allow_delete' => true,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false,
                'constraints' => [
                    new Assert\Callback([$this, 'validatePerformance'])
                ]
            ));

    }

    /**
     * (non-PHPdoc)
     * @param OptionsResolverInterface $resolver
     *
     * @see \Symfony\Component\Form\AbstractType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\InserateBundle\Entity\Organization',
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'tj_inserate_form_organization_performances';
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function validateVisitors($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $data = $form->getData();
        $orgaPer = $data->getOrganizationPerformance();
        $orgaVis = $data->getOrganizationVisitors();
        $emptyNr = true;

        foreach($orgaPer as $orga) {
            if ($orga->getPerformanceNumber()) {
                $emptyNr = false;
                break;
            }
        }
        if ($emptyNr) {
            foreach($orgaVis as $orga) {
                if ($orga->getVisitorsNumber()) {
                    $emptyNr = false;
                    break;
                };
            }
        }
        if ($emptyNr && count($orgaVis)) {

            $context->buildViolation($this->translator->trans('organization.performance.required.at.least.one'))
                ->atPath('[0][visitorsNumber]')
                ->addViolation();
        }
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function validatePerformance($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $data = $form->getData();
        $orgaPer = $data->getOrganizationPerformance();
        $orgaVis = $data->getOrganizationVisitors();
        $emptyNr = true;

        foreach($orgaPer as $orga) {
            if ($orga->getPerformanceNumber()) {
                $emptyNr = false;
                break;
            }
        }
        if ($emptyNr) {
            foreach($orgaVis as $orga) {
                if ($orga->getVisitorsNumber()) {
                    $emptyNr = false;
                    break;
                };
            }
        }
        if ($emptyNr && count($orgaPer)) {
            $context->buildViolation($this->translator->trans('organization.performance.required.at.least.one'))
                ->atPath('[0][performanceNumber]')
                ->addViolation();
        }
    }
}
