<?php

namespace Theaterjobs\MainBundle\Utility\Traits;

use JMS\JobQueueBundle\Entity\Job as JobQueue;

/**
 * Trait EmailTrait
 * @package Theaterjobs\MainBundle\Utility
 */
trait EmailTrait
{
    use RenderedEmailTrait;

    /**
     * Send email if email belongs to a user and is valid or if it doesn't belong
     *
     * @param $subject
     * @param $body
     * @param $fromEmail
     * @param $toEmail
     * @param null|string $contentType
     * @return bool
     */
    public function sendEmailMessage($subject, $body, $fromEmail, $toEmail, $contentType = 'text/html')
    {
        //Check if email is working
        $user = $this->em->getRepository('TheaterjobsUserBundle:User')->findOneBy(['email' => $toEmail]);

        //Email doesn't belong any user
        if (!$user) {
            $this->sendEmail($subject, $body, $fromEmail, $toEmail, $contentType);
            return true;
        }

        //Email belong to user and is valid
        if (!$user->getProfile()->getProfileAllowedTo()->getEmailWarning()) {
            $this->sendEmail($subject, $body, $fromEmail, $toEmail, $contentType);
            return true;
        }
        return false;
    }

    /**
     * Sends a mail to the user/users
     *
     * @param $subject
     * @param $body
     * @param $fromEmail
     * @param $toEmail
     * @param $contentType string
     * @param null $bcc
     */
    protected function sendEmail($subject, $body, $fromEmail, $toEmail, $contentType = 'text/html', $bcc = null)
    {
        // Create Job Queue
        $sendEmailJob = new JobQueue(
            'app:send:email',
            [
                json_encode($subject),
                json_encode($body),
                json_encode($fromEmail),
                json_encode($toEmail),
                $contentType
            ],
            true,
            'app'
        );

        $this->em->persist($sendEmailJob);
        $this->em->flush($sendEmailJob);
    }
}