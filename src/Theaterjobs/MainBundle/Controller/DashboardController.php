<?php

namespace Theaterjobs\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use DateTime;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\InserateBundle\Entity\Job;
use Carbon\Carbon;
use Theaterjobs\InserateBundle\Entity\ApplicationTrack;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\StatsBundle\Utility\Traits\StatisticsTrait;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\NewsBundle\Entity\News;

/**
 * The Dashboard Controller.
 *
 * It provides the dashboard of the user
 *
 * @category Controller
 * @package  Theaterjobs\MainBundle\Controller
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @Route("dashboard", options={"i18n": false})
 */
class DashboardController extends BaseController
{
    use StatisticsTrait;

    /** @DI\Inject("sonata.seo.page") */
    private $seo;

    /**
     * The index action.
     * @param null $choice
     * @return \Symfony\Component\HttpFoundation\Response $array
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @Route("/{choice}", name="tj_main_dashboard_index", options={"expose"=true}, defaults={"choice" = null})
     */
    public function indexAction($choice)
    {
        $title = $this->getTranslator()->trans("dashboard.title.dashboardTheapolis", [], 'messages');
        $this->seo->setTitle($title);
        $fes = $this->get('fos_elastica.manager');
        //Get user
        $user = $this->getUser();
        $profile = $user->getProfile();
        $isAdmin = $user->hasRole("ROLE_ADMIN");

        //Membership Expire Difference
        $now = new DateTime();
        $expireDiff = $user->getMembershipExpiresAt() ? $now->diff($user->getMembershipExpiresAt()) : null;

        //Last profile update period
        $profileCreateDiff = $profile->getUpdatedAt()->diff($now);

        //Get Payment Method
        $paymentMethod = $this->getEM()->getRepository("TheaterjobsMembershipBundle:Paymentmethod")->paymentMethodByProfile($profile);

        //Get Dashboard Info
        $dashboardBoxes = $this->getDashboardBoxes($user);

        //Get latest news
        $recentNews = $this->getLatestNews(3);

        //Total jobs of a specified user
        $query = $fes->getRepository('TheaterjobsInserateBundle:Job')->userJobs($this->getUser()->getId());
        $query->setSize(0);
        $userJobs = $this->get('fos_elastica.index.theaterjobs.job')->search($query)->getTotalHits();

        // count applied jobs
        $query = $fes->getRepository(ApplicationTrack::class)->countAppliedJobs($this->getProfile()->getId());

        $nrAppliedJobs = $this->get('fos_elastica.index.theaterjobs.application_track')->search($query)->getTotalHits();

        $query = $fes->getRepository('TheaterjobsUserBundle:Notification')->allUnseenNotifications($user->getId());
        $unseenNotifications = $this->container->get('fos_elastica.index.events.notification')->search($query)->getTotalHits();

        $parameters = [
            "profile" => $profile,
            "isProfileFilled" => $this->checkCanBePublish($profile, true),
            'notifications' => $this->getUserNotifications($user),
            "choice" => $choice,
            "dateDiff" => ($expireDiff != null) ? $expireDiff->format('%R%a') : null,
            'profileRenewDiff' => ($profileCreateDiff != null) ? $profileCreateDiff->format('%R%a') : null,
            'paymentMethod' => ($paymentMethod != null) ? $paymentMethod->getShort() : null,
            'userOrganizations' => $this->getUserOrganizations($user, true),
            'allNotifications' => $unseenNotifications,
            'dashboardBox' => $dashboardBoxes,
            'recentNews' => $recentNews,
            'userJobs' => $userJobs,
            'nrAppliedJobs' => $nrAppliedJobs
        ];
        if ($isAdmin) {
            $fosProfile = $fes->getRepository('TheaterjobsProfileBundle:Profile');
            $profileIndex = $this->container->get('fos_elastica.index.theaterjobs.profile');
            $registeredUsersQuery = $fosProfile->getRegisteredUsers();
            $registeredUsersCount = $profileIndex->search($registeredUsersQuery)->getTotalHits();
            $parameters["registeredUsersCount"] = $registeredUsersCount;

            $membersQuery = $fosProfile->getUserByRole('ROLE_MEMBER', 0);

            $membersCount = $profileIndex->search($membersQuery)->getTotalHits();
            $parameters["membersCount"] = $membersCount;

            $publishedProfilesQuery = $fosProfile->getPublishedProfiles();
            $publishedProfilesCount = $profileIndex->search($publishedProfilesQuery)->getTotalHits();
            $parameters["publishedProfilesCount"] = $publishedProfilesCount;

            $publishedJobsCount = $fes->getRepository('TheaterjobsInserateBundle:Job')->getPublishedJobs();
            $publishedJobsCount = $this->container->get('fos_elastica.index.theaterjobs.job')->search($publishedJobsCount, 0)->getTotalHits();
            $parameters["publishedJobsCount"] = $publishedJobsCount;

            $onlineMembersQuery = $fosProfile->getUserByRole('ROLE_MEMBER', true, 0);
            $onlineMembersCount = $profileIndex->search($onlineMembersQuery)->getTotalHits();
            $parameters["onlineMmembersCount"] = $onlineMembersCount;

            $onlineAdminsQuery = $fosProfile->getUserByRole('ROLE_ADMIN', true, 10);

            $onlineAdminsCount = $profileIndex->search($onlineAdminsQuery);
            $parameters["onlineAdmins"] = $onlineAdminsCount;

        }

        return $this->render('TheaterjobsMainBundle:Dashboard:index.html.twig', $parameters);

    }

    /**
     * Get user notifications NI/NRA
     *
     * @param $user
     * @return Notification[]
     */
    private function getUserNotifications($user)
    {

        //All notification with required action
        $finder = $this->container->get('fos_elastica.finder.events.notification');
        $query = $this->container->get('fos_elastica.manager')
            ->getRepository(Notification::class)->unseenNRA($user->getId());
        $actionNotifications = $finder->find($query, 3);

        //All notification with informative purpose
        $infoNotification = [];
        $count = count($actionNotifications);
        if ($count < Notification::NR_NRA) {
            $maxResults = Notification::NR_NRA - $count;
            $maxResults = ($maxResults < 0) ? 0 : $maxResults;
            $query = $this->container->get('fos_elastica.manager')
                ->getRepository(Notification::class)->unseenNI($user->getId());
            $infoNotification = $finder->find($query, $maxResults);
        }

        return array_merge($actionNotifications, $infoNotification);
    }

    /**
     * Return dashboard boxes
     *
     * @param $user User
     *
     * @return array
     */
    private function getDashboardBoxes($user)
    {
        $dashboardBox = $this->getDashboardStruct();
        //Get New Last Jobs
        $this->getLatestJobs($dashboardBox);
        //Get profileVisits/Profiles
        $this->getProfileVisits($user, $dashboardBox);

        return $dashboardBox;
    }

    /**
     * Get last 10 day jobs
     *
     * @param $dashboardBox
     */
    private function getLatestJobs(&$dashboardBox)
    {
        $now = Carbon::now();
        $range = $now->subDays(10);
        $publishedJobsCount = $this->container->get('fos_elastica.manager')->getRepository('TheaterjobsInserateBundle:Job')->getPublishedJobs($range);
        $publishedJobsCount = $this->container->get('fos_elastica.index.theaterjobs.job')->search($publishedJobsCount, 0)->getTotalHits();

        $dashboardBox[0]['kind'] = 'newJobs';
        $dashboardBox[0]['data']['count'] = $publishedJobsCount;
    }

    /**
     * Get all profile visits/ all profiles
     *
     * @param User $user
     * @param $dashboardBox
     */
    private function getProfileVisits($user, &$dashboardBox)
    {
        $profile = $user->getProfile();
        if (($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_MEMBER')) && $profile->getIsPublished()) {
            //get profile visits
            $dashboardBox[1]['kind'] = 'profile_visits';
            $dashboardBox[1]['data']['count'] = $this->countAllViews(Profile::class, $user->getProfile()->getId());

        } else {
            $dashboardBox[1]['kind'] = 'profile_visits';
            $dashboardBox[1]['data']['count'] = 0;
        }
    }

    /**
     * Dashboard structure
     *
     * @return array
     */
    private function getDashboardStruct()
    {
        //Dashboard Boxes data Structure
        $dashboardBox = [
            [
                'kind' => '',
                'data' => [
                    'count' => 0,
                ]
            ],
            [
                'kind' => '',
                'data' => [
                    'count' => 0,
                ]
            ]
        ];

        return $dashboardBox;
    }

    /**
     * get $nr latest news
     * @param $nr
     * @return array
     */
    public function getLatestNews($nr)
    {
        $finder = $this->container->get('fos_elastica.finder.theaterjobs.news');
        $query = $this->container->get('fos_elastica.manager')->getRepository(News::class)->latestNews();
        return $finder->find($query, $nr);
    }
}
