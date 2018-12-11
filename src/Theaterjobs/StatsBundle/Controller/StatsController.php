<?php

namespace Theaterjobs\StatsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Theaterjobs\MainBundle\Controller\BaseController;
use DateTime;
use Carbon\Carbon;

/**
 * Statistic  controller.
 *
 * @Route("")
 */
class StatsController extends BaseController
{

    /**
     * Lists all Profile entities.
     *
     * @Route("/", name="tj_stats_index")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $searchForm = $this->createSearchForm();
        $searchForm->handleRequest($request);
        if (!$searchForm['dateFrom']->getData()) {
            $date = new DateTime();
            $searchForm['dateFrom']->setData(Carbon::now()->subDays(30)->format('d.m.Y'));
        }
        if (!$searchForm['dateTo']->getData()) {
            $date = new DateTime();
            $searchForm['dateTo']->setData(Carbon::now()->format('d.m.Y'));
        }
        $entity = $searchForm['entity']->getData();
        $entities = [];
        switch ($entity) {
            case "job":
                $entities = $this->getEM()->getRepository("TheaterjobsInserateBundle:Job")->createJobQuery($searchForm);
                break;
            case "network":
                $entities = $this->getEM()->getRepository("TheaterjobsInserateBundle:Network")->createNetworkQuery($searchForm);
                break;
            case "education":
                $entities = $this->getEM()->getRepository("TheaterjobsInserateBundle:Education")->createEducationQuery($searchForm);
                break;
            case "user":
                $entities = $this->createMemberQuery($searchForm);
                break;
            default :
                break;
        }

        $qbj = $this->getEM()->createQueryBuilder();

        $start = Carbon::now()->subDays(30)->format('Y-m-d');
        $end = Carbon::now()->format('Y-m-d');

        $qbj->select('COUNT(j) as num')
            ->from('TheaterjobsInserateBundle:Job', 'j')
            ->andWhere('SUBSTRING(j.createdAt, 1, 10) >= :startDate')
            ->andWhere('SUBSTRING(j.createdAt, 1, 10) <= :endDate')
            ->setParameters(array('startDate' => $start, 'endDate' => $end));
        $jobs = $qbj->getQuery()->getResult();

        $qbe = $this->getEM()->createQueryBuilder();
        $qbe->select('COUNT(e) as num')
            ->from('TheaterjobsInserateBundle:Education', 'e')
            ->andWhere('SUBSTRING(e.createdAt, 1, 10) >= :startDate')
            ->andWhere('SUBSTRING(e.createdAt, 1, 10) <= :endDate')
            ->setParameters(array('startDate' => $start, 'endDate' => $end));
        $education = $qbe->getQuery()->getResult();

        $qbn = $this->getEM()->createQueryBuilder();
        $qbn->select('COUNT(n) as num')
            ->from('TheaterjobsInserateBundle:Network', 'n')
            ->andWhere('SUBSTRING(n.createdAt, 1, 10) >= :startDate')
            ->andWhere('SUBSTRING(n.createdAt, 1, 10) <= :endDate')
            ->setParameters(array('startDate' => $start, 'endDate' => $end));
        $network = $qbn->getQuery()->getResult();

        return $this->render("TheaterjobsStatsBundle:Stats:index.html.twig", array(
            'entities' => $entities,
            'jobs' => $jobs[0],
            'education' => $education[0],
            'network' => $network[0],
            'form' => $searchForm->createView(),
        ));
    }

    private function createSearchForm()
    {
        $form = $this->createFormBuilder()
            ->add('entity', 'choice', array(
                'choices' => array(
                    'user' => $this->getTranslator()->trans('form.stats.choice.entity.users', array(), 'forms'),
                    'job' => $this->getTranslator()->trans('form.stats.choice.entity.jobs', array(), 'forms'),
                    "network" => $this->getTranslator()->trans('form.stats.choice.entity.network', array(), 'forms'),
                    'education' => $this->getTranslator()->trans('form.stats.choice.entity.educations', array(), 'forms')
                ),
                'empty_value' => $this->getTranslator()->trans('form.stats.choice.entity.empty', array(), 'forms'),
                'data' => 'user',
                'label' => $this->getTranslator()->trans('form.stats.label.entity', array(), 'forms')
            ))
            ->add('dateFrom', 'text', array('label' => $this->getTranslator()->trans('form.stats.label.date.from', array(), 'forms')))
            ->add('dateTo', 'text', array('label' => $this->getTranslator()->trans('form.stats.label.date.from', array(), 'forms')))
            ->add('members', 'choice', array('choices' => array(
                null => $this->getTranslator()->trans('form.stats.choice.users.all', array(), 'forms'),
                'registered' => $this->getTranslator()->trans('form.stats.choice.users.registered', array(), 'forms'),
                'deleted' => $this->getTranslator()->trans('form.stats.choice.users.deleted', array(), 'forms'),
                'memberships' => $this->getTranslator()->trans('form.stats.choice.users.memberships', array(), 'forms'),
                'payments' => $this->getTranslator()->trans('form.stats.choice.users.payment.types', array(), 'forms'),
                'payingProfile' => $this->getTranslator()->trans('form.stats.choice.users.paying.members.with.profile', array(), 'forms')
            ),
                'data' => 'memberships',
                'required' => false,
                'label' => $this->getTranslator()->trans('form.stats.label.user', array(), 'forms')
            ))
            //registered
            ->add('registeredType', 'choice', array('choices' => array(
                null => $this->getTranslator()->trans('form.stats.choice.registered.all', array(), 'forms'),
                'nonMembers' => $this->getTranslator()->trans('form.stats.choice.registered.non.members', array(), 'forms'),
                'members' => $this->getTranslator()->trans('form.stats.choice.registered.members', array(), 'forms')
            ),
                'required' => false,
                'label' => $this->getTranslator()->trans('form.stats.label.registered', array(), 'forms')
            ))
            //payment type
            ->add('paymentsType', 'choice', array('choices' => array(
                null => $this->getTranslator()->trans('form.stats.choice.payments.type.all', array(), 'forms'),
                'direct' => $this->getTranslator()->trans('form.stats.choice.payments.type.direct.debit', array(), 'forms'),
                'paypal' => $this->getTranslator()->trans('form.stats.choice.payments.type.paypal', array(), 'forms'),
                'prepay' => $this->getTranslator()->trans('form.stats.choice.payments.type.prepayment', array(), 'forms')
            ),
                'required' => false,
                'label' => $this->getTranslator()->trans('form.stats.label.payments.type', array(), 'forms')
            ))
            ->add('paymentsTime', 'choice', array('choices' => array(
                null => $this->getTranslator()->trans('form.stats.choice.payments.time.all', array(), 'forms'),
                'first' => $this->getTranslator()->trans('form.stats.choice.payments.time.first', array(), 'forms'),
                'recurring' => $this->getTranslator()->trans('form.stats.choice.payments.time.recurring', array(), 'forms'),
                'renewals' => $this->getTranslator()->trans('form.stats.choice.payments.time.renewals', array(), 'forms')
            ),
                'required' => false,
                'label' => $this->getTranslator()->trans('form.stats.label.payments.time', array(), 'forms')
            ))
            //memberships
            ->add('membershipsType', 'choice', array('choices' => array(
                null => $this->getTranslator()->trans('form.stats.choice.memberships.type.all', array(), 'forms'),
                'new' => $this->getTranslator()->trans('form.stats.choice.memberships.type.new', array(), 'forms'),
                'lost' => $this->getTranslator()->trans('form.stats.choice.memberships.type.lost', array(), 'forms')
            ),
                'required' => false,
                'label' => $this->getTranslator()->trans('form.stats.label.memberships.type', array(), 'forms')
            ))
            //members type
            ->add('membersType', 'choice', array('choices' => array(
                null => $this->getTranslator()->trans('form.stats.choice.members.type.all', array(), 'forms'),
                'month' => $this->getTranslator()->trans('form.stats.choice.members.type.3_months', array(), 'forms'),
                'year' => $this->getTranslator()->trans('form.stats.choice.members.type.1_year', array(), 'forms')
            ),
                'required' => false,
                'label' => $this->getTranslator()->trans('form.stats.label.members.type', array(), 'forms')
            ))
            ->add('status', 'choice', array('choices' => array(
                    null => $this->getTranslator()->trans('form.stats.choice.status.all', array(), 'forms'),
                    'published' => $this->getTranslator()->trans('form.stats.choice.status.published', array(), 'forms'),
                    'archived' => $this->getTranslator()->trans('form.stats.choice.status.archived', array(), 'forms')
                ),
                    'required' => false,
                    'label' => $this->getTranslator()->trans('form.stats.label.status', array(), 'forms')
                )
            )
            ->add('users', 'choice', array('choices' => array(
                null => $this->getTranslator()->trans('form.stats.choice.users.jobs.all_users', array(), 'forms'),
                'admins' => $this->getTranslator()->trans('form.stats.choice.users.jobs.admins', array(), 'forms'),
                'others' => $this->getTranslator()->trans('form.stats.choice.users.jobs.others', array(), 'forms')
            ),
                'required' => false,
                'label' => $this->getTranslator()->trans('form.stats.label.job.users', array(), 'forms')
            ))
            ->add('save', 'submit', array('label' => $this->getTranslator()->trans('form.stats.button.statistics', array(), 'forms')))
            ->getForm();

        return $form;
    }

    private function createMemberQuery($form)
    {

        if ($form->getData()['dateFrom']) {
            $dateFrom = new DateTime($form->getData()['dateFrom']);
        }
        if ($form->getData()['dateTo']) {
            $dateTo = new DateTime($form->getData()['dateTo']);
            $dateTo->modify('+1 day');
        }

        if ($form->getData()['members'] !== null) {
            $members = $form->getData()['members'];
        } else {
            $members = 'memberships';
        }

        $paymentsType = $form->getData()['paymentsType'];
        $paymentsTime = $form->getData()['paymentsTime'];
        $membershipsType = $form->getData()['membershipsType'];
        $registeredType = $form->getData()['registeredType'];
        $membersType = $form->getData()['membersType'];

        if (($members !== 'memberships') || ($members != 'payments') || (($members != 'registered') && ($registeredType != 'members') && (($membersType != '') || ($paymentsType != '') || ($paymentsTime != '')))) {
            $date = 'SUBSTRING(profile.createdAt,1,10) as dt';
        } else {
            if (($members === 'memberships') && ($membershipsType == 'lost')) {
                $date = 'SUBSTRING(user.quitContractDate,1,10) as dt';
            } else {
                $date = 'SUBSTRING(bookings.createdAt,1,10) as dt';
            }
        }

        $mQb = $this->getEM()->createQueryBuilder()->select('COUNT(user.id) as num, ' . $date)
            ->from("TheaterjobsUserBundle:User", 'user')->innerJoin('user.profile', 'profile');

        $params = array();


        if ($members === 'deleted') {

            $mQb->andWhere('user.enabled = false');
        } else if ($members === 'payingProfile') {

            $params['roles'] = '%"ROLE_MEMBER"%';
            $mQb->andWhere('user.roles LIKE :roles')
                ->andWhere('profile.isPublished = true');
        } else if ($members === 'memberships') {

            $mQb->innerJoin('profile.bookings', 'bookings');

            if ($membershipsType == 'new') {

                if (isset($dateFrom)) {
                    $mQb->andWhere('bookings.createdAt >= :startBdate');
                    $params['startBdate'] = $dateFrom;
                }
                if (isset($dateTo)) {
                    $mQb->andWhere('bookings.createdAt <= :endBdate');
                    $params['endBdate'] = $dateTo;
                }
            } elseif ($membershipsType == 'lost') {

                if (isset($dateFrom)) {
                    $mQb->andWhere('user.quitContractDate >= :startQdate');
                    $params['startQdate'] = $dateFrom;
                }
                if (isset($dateTo)) {
                    $mQb->andWhere('user.quitContractDate <= :endQdate');
                    $params['endQdate'] = $dateTo;
                }
            }
        } else if ($members === 'registered') {

            $mQb->andWhere('user.enabled = true');

            if ($registeredType == 'nonMembers') {
                $params['roles'] = '%"ROLE_MEMBER"%';
                $mQb->andWhere('user.roles NOT LIKE :roles');
            } elseif ($registeredType == 'members') {
                $params['roles'] = '%"ROLE_MEMBER"%';
                $mQb->andWhere('user.roles LIKE :roles');

                if (($membersType != '') || ($paymentsType != '') || ($paymentsTime != '')) {
                    $memberStats = $this->memberStats($membersType, $paymentsType, $paymentsTime, $mQb, $dateFrom, $dateTo);
                    $params = array_merge($params, $memberStats);
                }
            }
        } elseif ($members === 'payments') {

            $memberStats = $this->memberStats($membersType, $paymentsType, $paymentsTime, $mQb, $dateFrom, $dateTo);
            $params = array_merge($params, $memberStats);
        }

        if (($members !== 'memberships') || ($members != 'payments') || (($members != 'registered') && ($registeredType != 'members') && (($membersType != '') || ($paymentsType != '') || ($paymentsTime != '')))) {
            if (isset($dateFrom)) {
                $mQb->andWhere('profile.createdAt >= :startDate');
                $params['startDate'] = $dateFrom;
            }
            if (isset($dateTo)) {
                $mQb->andWhere('profile.createdAt <= :endDate');
                $params['endDate'] = $dateTo;
            }
        }

        $mQb->groupBy('dt')->setParameters($params);

        $membersResults = $mQb->getQuery()->getResult();


        return $membersResults;
    }

    private function memberStats($membersType, $paymentsType, $paymentsTime, $mQb, $dateFrom, $dateTo)
    {

        $params = array();

        $paymentMethod = $this->getEM()->getRepository('TheaterjobsMembershipBundle:Paymentmethod')->findOneBy(array('short' => $paymentsType));

        $mQb->innerJoin('profile.bookings', 'bookings');
        $mQb->innerJoin('bookings.billing', 'billing');
        $mQb->innerJoin('bookings.membership', 'membership');

        if (isset($dateFrom)) {
            $mQb->andWhere('bookings.createdAt >= :startBdate');
            $params['startBdate'] = $dateFrom;
        }
        if (isset($dateTo)) {
            $mQb->andWhere('bookings.createdAt <= :endBdate');
            $params['endBdate'] = $dateTo;
        }

        if ($paymentMethod !== null) {
            $mQb->andWhere('bookings.paymentmethod = :paymentmethod');
            $params['paymentmethod'] = $paymentMethod;
        }

        if ($paymentsTime == 'first') {
            $mQb->andWhere('billing.sequence = :sequence');
            $params['sequence'] = 'FRST';
        } elseif ($paymentsTime == 'recurring') {
            $mQb->andWhere('billing.sequence = :sequence');
            $params['sequence'] = 'RCUR';
        } elseif ($paymentsTime == 'renewals') {
            $mQb->andWhere('user.extendMembership = true');
        }

        if ($membersType == 'month') {
            $mQb->andWhere('membership.duration = :duration');
            $params['duration'] = 'P3M';
        } elseif ($membersType == 'year') {
            $mQb->andWhere('membership.duration = :duration');
            $params['duration'] = 'P1Y';
        }

        return $params;
    }

}
