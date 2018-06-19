<?php

namespace Theaterjobs\InserateBundle\Controller;

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Theaterjobs\InserateBundle\Entity\ApplicationTrack;
use Theaterjobs\InserateBundle\Entity\Inserate;
use Theaterjobs\InserateBundle\Form\ApplicationTrackType;
use Theaterjobs\MainBundle\Controller\BaseController;

/**
 * ApplicationTrack controller.
 *
 * @Route("/application-request")
 */
class ApplicationTrackController extends BaseController
{

    /**
     * Creates a new ApplicationTrack entity.
     *
     * @Route("/create/{slug}", name="applicationRequest_create")
     * @Method("POST")
     * @param Request $request
     * @param Inserate $job
     * @return Response
     */
    public function createAction(Request $request, Inserate $job = null)
    {
        $em = $this->getEM();
        $entity = new ApplicationTrack();
        $form = $this->createCreateForm(ApplicationTrackType::class, $entity, [], 'applicationRequest_create', ['slug' => $job->getSlug()]);
        $form->handleRequest($request);
        $userProfile = $this->getProfile();

        if (!$form->isValid()) {
            return new JsonResponse(['error' => true, 'errors' => $this->getErrorMessages($form)]);
        }
        //Valid email
        if ($this->checkFalseEmail($entity->getEmail())) {
            return new JsonResponse(['error' => true, 'message' => $this->getTranslator()->trans('applicationtrack.invalid.email')]);
        }
        //Already sent
        $identicalApplications = $em->getRepository("TheaterjobsInserateBundle:ApplicationTrack")->findIdenticalApplication($userProfile, $job);
        if (count($identicalApplications)) {
            return new JsonResponse(['error' => true, 'message' => $this->getTranslator()->trans('applicationtrack.already.applied')]);
        }
        //If job or job's organization has email
        $jobOrga = $job->getOrganization();
        if (!$job->getEmail() && !$jobOrga && $jobOrga->getContactSection() && $jobOrga->getContactSection()->getEmail()) {
            return new JsonResponse(['error' => true, 'message' => 'This job does not have any email.']);
        } else {
            $receivingEmail = $job->getEmail() ? $job->getEmail() : $jobOrga->getContactSection()->getEmail();
        }

        $this->sendJobApplEmail($userProfile, $entity, $job, $receivingEmail);
        //Persist
        $entity->setJob($job);
        $entity->setProfile($userProfile);
        $entity->setCreatedAt(Carbon::now());
        $em->persist($entity);

        if (!$userProfile->getJobFavourite()->contains($job)) {
            $userProfile->addJobFavourite($job);
            $em->persist($userProfile);
        }

        $em->flush();
        $date = $this->render('TheaterjobsInserateBundle:Partial:date_formatted.html.twig', ['date' => $entity->getCreatedAt()])->getContent();

        $appliedInfo = '<small> ' . $this->get('translator')->trans('jobshow.succes.application.appliedOn', [], 'messages') . '<span class="color-grey"> ' . $date . ' </span><br> ' . $this->get('translator')->trans('jobshow.succes.application.with', [], 'messages') . ' <span class="color-red">' . $entity->getEmail() . '</span></small>';

        return new JsonResponse([
            'error' => false,
            'message' => $this->get('translator')->trans('inserate.job.success.successfullyAppliedForJob'),
            'appliedInfo' => $appliedInfo
        ]);
    }

    /**
     * Displays a form to create a new ApplicationTrack entity.
     *
     * @Route("/new/{slug}", name="new_application_request" , condition="request.isXmlHttpRequest()")
     * @Method("GET")
     * @param $slug
     * @return Response
     */
    public function newAction($slug)
    {
        $entity = new ApplicationTrack();
        $job = $this->getEM()->getRepository('TheaterjobsInserateBundle:Inserate')->findOneBySlug($slug);
        $form = $this->createCreateForm(ApplicationTrackType::class, $entity, [], 'applicationRequest_create', ['slug' => $job->getSlug()]);

        return $this->render('TheaterjobsInserateBundle:ApplicationTrack:new.html.twig', [
            'job' => $job,
            'profile' => $this->getProfile(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Send email to job owner and applicant
     *
     * @param $userProfile
     * @param $entity
     * @param $job
     * @param $receivingEmail
     */
    private function sendJobApplEmail($userProfile, $entity, $job, $receivingEmail)
    {
        //Email content
        $emailContent = $this->render('TheaterjobsInserateBundle:Job/email:jobApplicationEmail.html.twig', [
            'name' => $userProfile->getSubtitle(),
            'profile' => $userProfile,
            'slug' => $userProfile->getSlug(),
            'content' => $entity->getContent()
        ])->getContent();

        //To applicant
        $for = $this->get('translator')->trans('email.jobApplication.subject.for', [], 'messages');
        $from = $this->get('translator')->trans('email.jobApplication.from', [], 'messages');

        if ($job->getOrganization()) {
            $subject = $for . ' "' . $job->getTitle() . '" , "' . $job->getOrganization()->getName() . '" ' . $from . ' ' . $userProfile->getSubtitle();
        } else {
            $subject = $for . ' "' . $job->getTitle() . '" ' . $from . ' ' . $userProfile->getSubtitle();
        }

        $subject1 = $this->getTranslator()->trans('email.jobApplication.copyOf', [], 'emails') . ' ' . $subject;
        $this->get('base_mailer')
            ->sendEmailMessage(
                $subject1,
                $emailContent,
                $this->getParameter('noreply_email'),
                $entity->getEmail(),
                'text/html'
            );
        //To job owner
        $this->get('base_mailer')
            ->sendEmailMessage(
                $subject,
                $emailContent,
                $entity->getEmail(),
                $receivingEmail,
                'text/html'
            );
    }
}
