<?php

namespace Theaterjobs\MainBundle\Controller;

use Carbon\Carbon;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Form;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Theaterjobs\CategoryBundle\Entity\Category;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\MainBundle\Utility\Traits\ReadNotificationTrait;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\User;

/**
 * The Base Controller.
 *
 * It provides a lot of functions to use in in other controllers.
 *
 * @category Controller
 * @package  Theaterjobs\MainBundle\Controller
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class BaseController extends Controller
{
    use ReadNotificationTrait;

    /**
     * @return ObjectManager
     */
    protected function getEM()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return \FOS\ElasticaBundle\Manager\RepositoryManager
     */
    protected function getESM()
    {
        return $this->get('fos_elastica.manager');
    }

    /**
     * Get the doctrine repository.
     *
     * @param string $repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($repository)
    {
        return $this->getDoctrine()
            ->getRepository("$repository");
    }

    /**
     * extended function to get a profile from an user
     *
     * @return \Theaterjobs\ProfileBundle\Entity\Profile
     */
    protected function getProfile()
    {
        $user = $this->getUser();
        return $user ? $user->getProfile() : null;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return parent::getUser();
    }

    /**
     * Check an object has the right permisson.
     *
     * @param string $grant The right to checking.
     * @param string $obj The permission.
     *
     * @return $this
     */
    protected function isGranted($grant, $obj = null)
    {
        return $this->get('security.authorization_checker')->isGranted($grant, $obj);
    }

    /**
     * Get the session.
     *
     * @param type $param
     *
     * @return object|\Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSession($param = null)
    {
        if (isset($param)) {
            return $this->get('session')->get($param);
        }

        return $this->get('session');
    }

    /**
     * @param string $type
     * @param array|string $message
     */
    protected function addFlash($type, $message)
    {
        $this->getSession()->getFlashBag()->add($type, $message);
    }

    /**
     * Get the dependency injection.
     *
     * @param string $id
     *
     * @return \Symfony\Component\DependencyInjection\mixed|string
     */
    protected function getParameter($id)
    {
        $tmp = $this->container->getParameter($id);
        if ($tmp) {
            return $tmp;
        }

        return '';
    }

    /**
     * Creates a form to create an entity.
     *
     * @param string $formName The service name of the form
     * @param mixed $entity The entity
     * @param array $options an array with options
     * @param string $routeName The route name from the action
     *
     * @param array $routeParams
     * @return Form The form
     */
    protected function createCreateForm($formName, $entity, $options, $routeName, $routeParams = [])
    {
        $action = $this->generateUrl($routeName, $routeParams);
        $optionsM = array_merge($options, [
            'action' => $action,
            'method' => 'POST',
        ]);

        $form = $this->createForm($formName, $entity, $optionsM);
        $label = $this->getTranslator()->trans('button.create', [], 'forms');
        $form->add('submit', 'submit', ['label' => $label]);

        return $form;
    }

    /**
     * Creates a form to edit an entity.
     *
     * @param string $formName The service name of the form
     * @param $entity
     * @param array $options an array with options
     * @param string $routeName The route name from the action
     * @param array $routeParams The route params from the action
     * @param array $submitOpt Submit button options
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createEditForm($formName, $entity, $options, $routeName, $routeParams = [], $submitOpt = [])
    {
        $action = $this->generateUrl($routeName, $routeParams);
        $optionsM = array_merge($options, [
            'action' => $action,
            'method' => 'PUT',
        ]);

        $form = $this->createForm($formName, $entity, $optionsM);
        if (!isset($submitOpt['label'])) {
            $submitOpt['label'] = $this->getTranslator()->trans('button.update', [], 'forms');
        }
        $form->add('submit', 'submit', $submitOpt);
        return $form;
    }

    /**
     * Creates a form to delete an entity by slug.
     *
     * @param string $slug The entity slug
     *
     * @return Form
     */
    protected function createDeleteForm($slug)
    {
        return $this->createFormBuilder(array('slug' => $slug))
            ->setMethod('GET')
            ->add('slug', 'hidden')
            ->getForm();
    }

    /**
     * Creates a form to delete an entity
     *
     * @param $routeName
     * @param array $routeParams
     * @return Form
     */
    protected function createGeneralDeleteForm($routeName, $routeParams = [])
    {
        $action = $this->generateUrl($routeName, $routeParams);
        $label = $this->getTranslator()->trans('button.delete', [], 'forms');

        return $this->createFormBuilder()
            ->setAction($action)
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => $label])
            ->getForm();
    }

    /**
     * Returns the Paginator.
     *
     * @return \Knp\Component\Pager\Paginator|object
     */
    protected function getPaginator()
    {
        return $this->get('knp_paginator');
    }

    /**
     * Returns the Translator.
     *
     * @return object|\Symfony\Component\Translation\DataCollectorTranslator|\Symfony\Component\Translation\IdentityTranslator
     */
    protected function getTranslator()
    {
        return $this->get('translator');
    }

    /**
     * Get the upload root dir.
     *
     * @return string
     */
    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web';
    }

    // Generate an array contains a key -> value with the errors where the key is the name of the form field
    protected function getErrorMessages(Form $form)
    {
        $errors = [];

        foreach ($form->getErrors(true) as $key => $err) {
            $cause = $err->getCause()->getPropertyPath();
            $cause = str_replace(['children', '.', 'data'], '', $cause);
            $message = $err->getMessage();
            $key = $form->getName() . $cause;
            $errors[$key] = $message;
        }

        return $errors;
    }

    // Generate an array contains a key -> value with the errors where the key is the name of the form field
    protected function getErrorMessagesAJAX(Form $form)
    {
        $errors = [];

        foreach ($form->getErrors(true) as $key => $err) {
            $cause = '\[' . $err->getOrigin()->getConfig()->getName() . '\]';
            $cause = str_replace(['children', '.', 'data'], '', $cause);
            $message = $err->getMessage();
            $key = $form->getName() . $cause;
            $errors[] = ['field' => $key, 'message' => $message];
        }
        return $errors;
    }

    /**
     * Manages response headers related to CacheControlDirective.
     *
     * @param $response_properties (Object)
     *
     * @return $response
     */

    protected function generalCustomCacheControlDirective($response_properties)
    {
        $response = new JsonResponse($response_properties);

        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('max-age', 0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);

        return $response;
    }


    /**
     * Creates general search form.
     *
     * @param $formName
     * @param $entity
     * @param array $options
     * @param $routeName
     * @param array $routeParams
     * @return Form
     * @internal param $entitySpecificData (Object)
     */

    protected function createGeneralSearchForm($formName, $entity, array $options = [], $routeName, array $routeParams = [])
    {
        $action = $this->generateUrl($routeName, $routeParams);

        $options = array_merge([
            'action' => $action,
            'method' => 'GET'
        ], $options);


        return $this->get('form.factory')
            ->createNamed(
                '',
                $formName,
                $entity,
                $options
            );
    }

    public function prepareAggSet($aggs, $subcategories)
    {
        if (isset($aggs['aggregations']['subcategories'])) {
            foreach ($aggs['aggregations']['subcategories']['buckets'] as $key => $agg) { // Array1 where buckets only have key 'doc_count'
                foreach ($subcategories as $k => $v) { // Array2 where we have subcategory name and id
                    $agg += ['key' => $k];
                    $aggs['aggregations']['subcategories']['buckets'][$key] = $agg;
                }
                unset($subcategories[current(array_keys($subcategories))]);
            }
        } else if (isset($aggs['aggregations']['categories'])) {
            foreach ($aggs['aggregations']['categories']['buckets'] as $key => $agg) { // Array1 where buckets only have key 'doc_count'
                foreach ($subcategories as $k => $v) { // Array2 where we have subcategory name and id
                    $agg += ['key' => $v, 'id' => $k];
                    $aggs['aggregations']['categories']['buckets'][$key] = $agg;
                }
                unset($subcategories[current(array_keys($subcategories))]);
            }
        }
        return $aggs;
    }

    /**
     * Order the elastic search aggregation set
     * we need a param to sort the aggregation array, elastic search hasn't any opportunities to do that
     * I added a categories array in our app.yml file to add the id and then we can sort the array key
     *
     * @param $aggregations
     * @param $baseCategories
     * @return array
     */
    public function orderAggSet($aggregations, $baseCategories)
    {
        $categorySlugs = [];
        // collect all categories slugs
        foreach ($aggregations['categories']['buckets'] as $bucket) {
            $categorySlugs [] = $bucket['key'];
        }
        // categories results [slug,title]
        $categories = $this->getEM()->getRepository(Category::class)->getTitlesBySlugs($categorySlugs);

        foreach ($aggregations['categories']['buckets'] as $key => $bucket) {
            // set category title
            $bucket['title'] = current(array_filter(
                $categories, function ($category) use ($bucket) {
                return $category['slug'] === $bucket['key'];
            }
            ))['title'];

            foreach ($baseCategories as $k => $v) {
                // get only the part before -
                $key = strtok($bucket['key'], "-");
                if ($key == $v['key']) {
                    $bucket['img'] = $v['img'];
                    $aggregationsNew['categories']['buckets'][$k] = $bucket;
                }
                unset($aggregations[current(array_keys($aggregations))]);
            }
        }

        if (isset($aggregationsNew)) {
            ksort($aggregationsNew['categories']['buckets']);
            return $aggregationsNew;
        }

        return $aggregations;
    }

    /**
     * Check if email is temporary or false.
     * Old functions compatibility
     * @param $email
     * @return mixed
     *
     */
    public function checkFalseEmail($email)
    {
        return $this->get('base_mailer')->checkFalseEmail($email);
    }

    /**
     * @TODO Please specify all use cases MIRELA
     * @param User $user
     * @param boolean $entity
     * @return array
     */
    public function getUserOrganizations(User $user, $entity = null)
    {
        $result = null;

        $orgs = $this->getEM()->getRepository('TheaterjobsUserBundle:UserOrganization')->getUserOrganizations($user);

        if ($entity) {
            $result = $orgs;
        } else {
            if (count($orgs) > 0) {
                foreach ($orgs as $orga) {
                    $result[] = ['id' => $orga->getOrganization()->getId(), 'slug' => $orga->getOrganization()->getSlug()];
                }
            }
        }
        return $result;
    }

    /**
     * Check if a notification exists
     *
     * @param $user User
     * @param $code string
     * @return null|object
     */
    function checkNotification($user, $code)
    {
        $em = $this->getEM();
        $type = $em->getRepository('TheaterjobsUserBundle:TypeOfNotification')->findOneBy(
            array(
                'code' => $code
            )
        );
        $exists = $em->getRepository('TheaterjobsUserBundle:Notification')->findOneBy(
            array(
                'user' => $user,
                'typeOfNotification' => $type
            )
        );
        return $exists;
    }

    /**
     * Updates profile field oldProfile to false
     *
     * @param Profile $profile
     * @param boolean $flush
     */
    protected function updateProfile(Profile $profile, $flush = true)
    {
        $em = $this->getEM();
        if ($profile->getOldProfile()) {
            $profile->setOldProfile(false);
        }
        // Set new update Profile date
        $profile->setLastUpdate(Carbon::now());
        $user = $this->getUser();
        // Mark notification as read
        $this->readNotification($user, 'profile_old_update', $user, null, $flush);
        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Check if user is authenticated or not
     *
     * @return bool
     */
    protected function isAnon()
    {
        $rememberLogin = $this->isGranted('IS_AUTHENTICATED_REMEMBERED');
        $fullLogin = $this->isGranted('IS_AUTHENTICATED_FULLY');
        return !($rememberLogin || $fullLogin);
    }


    /**
     * Checks if a published profile can delete profile field
     * Should exists at least one
     *
     * @param $profile
     * @return boolean
     */
    public function isAbleToDeleteSection(Profile $profile)
    {
        if ($profile->getIsPublished()) {
            $sum = 0;
            $qualifySect = $profile->getQualificationSection();
            if ($qualifySect) {
                $sum += count($qualifySect->getQualifications());
            }
            if (count($profile->getProductionParticipations())) {
                $sum += count($profile->getProductionParticipations());
            }
            if (count($profile->getExperiences())) {
                $sum += count($profile->getExperiences());
            }

            //gt 2 to delete a section
            return ($sum) > 1;
        } else {
            return true;
        }
    }

    /**
     * @TODO move this on profile entity and change occurrences on twig and php
     * @param $profile
     * @return string
     */
    public function defaultName($profile)
    {
        return $profile->getProfileName() ?
            $profile->getSubtitle() : $profile->getFirstName() . " " . $profile->getLastName();
    }

    /**
     * @param Profile $profile
     * @return bool
     */
    protected function checkFileAccess($profile)
    {
        $loggedInUser = $this->getUser();
        if (
            $profile->getIsPublished() ||
            $this->isGranted('ROLE_ADMIN') ||
            $loggedInUser && $loggedInUser->isEqual($profile->getUser())
        ) {
            return true;
        }

        throw new AccessDeniedException();
    }

    /**
     * On route generate if set at least one of all properties of the model search as query params the
     * $form->handleRequest($request) override the remaining properties default values (set them to null)
     * @param Request $request
     * @param $modelSearch
     */
    protected function fetchQueryParams(Request $request, $modelSearch)
    {
        // get all class properties and their default values
        $classVars = $modelSearch->getClassVars();

        // if the query params are not set symfony handles to set the default values
        // we exclude the XmlHttpRequest because the request is sent using form submit (which includes all the form inputs generated by form type)
        // we check even if the page params is not to detect if the form already has submitted once with defaults values( not the best implementation)
        if ($request->query->count() !== 0 && !$request->query->has('page') && !$request->isXmlHttpRequest()) {
            foreach ($classVars as $key => $value) {
                // check if the property is set as query param
                if (!$request->query->has($key)) {
                    // set property as query param with its default value
                    $request->query->set($key, $value);
                }
            }
        }
    }

    /**
     * Checks if profile can be published
     *
     * @param Profile $profile
     * @param bool $boolValue
     * @return array|bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function checkCanBePublish(Profile $profile, $boolValue = false)
    {
        $repo = $this->getEM()->getRepository(Profile::class);

        $hasProfilePhoto = $repo->countProfilePhoto($profile) > 0;
        $hasProfileEducation = $repo->countProfileQualifications($profile) > 0;
        $hasProfileProductionParticipators = $repo->countProfileProductionParticipators($profile) > 0;
        $hasProfileExperiences = $repo->countProfileExperiences($profile) > 0;

        $hasProfileUnderTitle = !empty($profile->getSubtitle2());
        $hasProfileContactSection = false;
        if ($profile->getContactSection()) {
            $hasProfileContactSection = !empty($profile->getContactSection()->getContact());
        }


        if ($boolValue) {
            return !$hasProfilePhoto || !$hasProfileEducation
                || !$hasProfileProductionParticipators || !$hasProfileExperiences
                || !$hasProfileUnderTitle || !$hasProfileContactSection;
        }

        return [
            'hasProfilePhoto' => $hasProfilePhoto,
            'hasProfileEducation' => $hasProfileEducation,
            'hasProfileProductionParticipators' => $hasProfileProductionParticipators,
            'hasProfileExperiences' => $hasProfileExperiences,
            'hasProfileUnderTitle' => $hasProfileUnderTitle,
            'hasProfileContactSection' => $hasProfileContactSection
        ];
    }
}
