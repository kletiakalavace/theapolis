<?php

namespace Theaterjobs\MainBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\InserateBundle\Model\JobSearch;
use Theaterjobs\InserateBundle\Model\OrganizationSearch;
use Theaterjobs\MainBundle\Entity\SaveSearch;
use Theaterjobs\MainBundle\Form\DataTransformer\SaveSearchEntityTransformer;
use Theaterjobs\NewsBundle\Entity\News;
use Theaterjobs\NewsBundle\Model\NewsSearch;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\ProfileBundle\Model\PeopleSearch;
use Theaterjobs\UserBundle\Entity\User;

/**
 * Class SaveSearchType
 * @package Theaterjobs\MainBundle\Form\Type
 * @DI\FormType
 */
class SaveSearchType extends AbstractType
{

    /**
     * @var Translator
     * @DI\Inject("translator")
     */
    public $trans;

    /**
     * @var Router $router
     * @DI\Inject("router")
     */
    public $router;

    /**
     * @var EntityManager $em
     * @DI\Inject("doctrine.orm.entity_manager")
     */
    public $em;

    /**
     * @var Profile $profile
     */
    public $profile;

    /** @DI\Inject("theaterjobs.main_bundle.save_search") */
    public $saveSearch;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->profile = $options['profile'];
        $builder
            ->add('params', 'text', [
                'required' => true,
                'constraints' => [
                    new Assert\Callback([$this, 'validateJson']),
                    new Assert\Callback([$this, 'validateFields']),
                    new Assert\Callback([$this, 'isUnique'])
                ]
            ])
            ->add('entity', 'text', [
                'required' => true,
                'constraints' => [
                    new Assert\Callback([$this, 'validateEntity'])
                ]
            ])
            ->add('routeName', 'text', [
                'required' => true,
                'constraints' => [
                    new Assert\Callback([$this, 'validateRouteName'])
                ]
            ])
            ->add('categorySlug', 'text', [
                'required' => false,
                'constraints' => [
                    new Assert\Callback([$this, 'validateCategory'])
                ]
            ]);

        $builder->get('entity')->addModelTransformer(new SaveSearchEntityTransformer());
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\MainBundle\Entity\SaveSearch',
            'csrf_protection' => false,
            'profile' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_main_saveSearch';
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function validateJson($value, ExecutionContextInterface $context)
    {
        if (!$this->isJson($value)) {
            $context->buildViolation($this->trans->trans('saveSearch.invalid.json'))->addViolation();
        }
    }

    /**
     * Check if save search is unique based on attributes
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function isUnique($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        /** @var SaveSearch $entity */
        $entity = $form->getData();
        $saveSearch = $this->em->getRepository(SaveSearch::class)->findBy([
            'params' => $this->saveSearch->removeWhiteListed($entity->getParams()),
            'entity' => $entity->getEntity(),
            'routeName' => $entity->getRouteName(),
            'categorySlug' => $entity->getCategorySlug(),
            'profile' => $this->profile
        ]);
        if ($saveSearch) {
            $context->buildViolation($this->trans->trans('saveSearch.not.unique.saveSearch'))->addViolation();
        }
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function validateFields($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $saveSearch = $form->getData();
        /** @var SaveSearch $entity */
        $entity = $saveSearch->getEntity();

        // Check what type of entity we are saving search
        switch ($entity) {
            case Job::class:
                $class = JobSearch::class;
                break;
            case Organization::class:
                $class = OrganizationSearch::class;
                break;
            case News::class:
                $class = NewsSearch::class;
                break;
            case Profile::class:
                $class = PeopleSearch::class;
                break;
            default:
                $class = null;
        }
        // If no valid class then build error
        if (!$class) {
            $context->buildViolation($this->trans->trans('saveSearch.invalid.entity'))->addViolation();
            return;
        }
        // For every field of saveSearch check if valid according to "Entity"Search $class
        $fields = json_decode($value);
        foreach ($fields as $property => $value) {
            if (!property_exists($class, $property)) {
                $context->buildViolation($this->trans->trans('saveSearch.invalid.fields.' . $property))->addViolation();
                break;
            }
            if (is_array($value) && $this->checkIfDuplicateValues($value)) {
                $context->buildViolation($this->trans->trans('saveSearch.duplicate.fields.in.array'))->addViolation();
                break;
            }
        }
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */

    public function validateRouteName($value, ExecutionContextInterface $context)
    {
        $route = $this->router->getRouteCollection()->get($value);
        if (!$route) {
            $context->buildViolation($this->trans->trans('saveSearch.invalid.routeName'))->addViolation();
        }
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function validateCategory($value, ExecutionContextInterface $context)
    {
        if (empty($value)) {
            return;
        }
        // Check if category is valid
        $category = $this->em->getRepository('TheaterjobsCategoryBundle:Category')->findOneBySlug($value);
        if (!$category) {
            $context->buildViolation($this->trans->trans('saveSearch.invalid.category'))->addViolation();

        }
    }
    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function validateEntity($value, ExecutionContextInterface $context)
    {
        if (!array_search($value, SaveSearch::VALID_ENTITIES)) {
            $context->buildViolation($this->trans->trans('saveSearch.invalid.Entity'))->addViolation();

        }
    }

    /**
     * Check if string is json or not
     * © STACKOVERFLOW
     * @param $string
     * @return bool
     */
    private function isJson($string)
    {
        // 1. Speed up the checking & prevent exception throw when non string is passed
        if (is_numeric($string) || !is_string($string) || !$string) {
            return false;
        }

        $cleaned_str = trim($string);
        if (!$cleaned_str || !in_array($cleaned_str[0], ['{', '['])) {
            return false;
        }

        // 2. Actual checking
        $str = json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE) && $str && $str != $string;
    }

    /**
     * Checks if there are duplicate values in array
     * @param array $value
     * @return bool
     *
     */
    private function checkIfDuplicateValues($value)
    {
        return count(array_unique($value))<count($value);
    }
}