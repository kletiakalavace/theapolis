<?php

namespace Theaterjobs\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\AdminBundle\Form\AdminPeopleType;
use Theaterjobs\AdminBundle\Model\AdminPeopleSearch;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\ProfileBundle\Entity\Profile;

/**
 * People controller.
 *
 * @Route("/people")
 */
class PeopleController extends BaseController
{
    /**
     * Lists all People entities.
     *
     * @Route("/index", name="admin_people_index")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $peopleSearch = new AdminPeopleSearch();
        $adminPeopleSearchForm = $this->createGeneralSearchForm(AdminPeopleType::class,
            $peopleSearch,
            [],
            'admin_people_load'
        );
        return $this->render('TheaterjobsAdminBundle:People:index.html.twig', [
            'form' => $adminPeopleSearchForm->createView()
        ]);
    }

    /**
     * Load people async.
     *
     * @Route("/load_admin_people", name="admin_people_load", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadPeople(Request $request)
    {
        $adminPeopleSearch = new AdminPeopleSearch();

        $adminPeopleSearchForm = $this->createGeneralSearchForm(AdminPeopleType::class,
            $adminPeopleSearch,
            [],
            'admin_people_load'
        );

        $paginator = $this->getPaginator();

        $adminPeopleSearchForm->handleRequest($request);
        $adminPeopleSearch = $adminPeopleSearchForm->getData();

        $pageNr = $request->query->getInt('page');
        $rows = $request->query->getInt('rows');

        $finder = $this->container->get('fos_elastica.finder.theaterjobs.profile');
        $query = $this->container->get('fos_elastica.manager')->getRepository(Profile::class)->adminPeopleSearch($adminPeopleSearch);

        $results = $finder->createPaginatorAdapter($query);
        $paginationPeople = $paginator->paginate($results, $pageNr, $rows);

        $iTotalRecords = $paginationPeople->getTotalItemCount();

        $records = [];
        $records["data"] = [];

        foreach ($paginationPeople as $people) {
            $profileLink = $this->generateUrl('tj_profile_profile_show', ['slug' => $people->getSlug()]);
            $userColumn = sprintf("<a href='$profileLink' >%s</a>", $people->getFullName());
            $accountLink = $this->generateUrl('tj_user_account_settings', ['slug' => $people->getSlug()]);
            $user = $people->getUser();
            $emailColumn = sprintf("<a href=%s >%s</a>", $accountLink, $user->getEmail());
            $registrationColumn = $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', ['date' => $people->getCreatedAt()])->getContent();
            $lastLoginColumn = $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', ['date' => $user->getLastLogin()])->getContent();
            $role = '';

            if ($user->hasRole('ROLE_ADMIN')) {
                $role = 'ADMIN';
            } elseif ($user->hasRole('ROLE_MEMBER')) {
                $role = 'MEMBER';
            } elseif ($user->hasRole('ROLE_USER')) {
                $role = 'USER';
            }

            $records["data"][] = [
                $userColumn,
                $emailColumn,
                $registrationColumn,
                $lastLoginColumn,
                $role
            ];
        }


        $records["totalPages"] = ceil($iTotalRecords / $rows);
        $records["page"] = $pageNr;
        $records["recordsTotal"] = $iTotalRecords;
        $records["draw"] = $rows;

        return new JsonResponse($records);
    }
}
