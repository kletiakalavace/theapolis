<?php

namespace Theaterjobs\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Send an email to user
 *
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 */
class SendEmailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:send:email')
            ->addArgument('subject', InputArgument::REQUIRED, 'Subject')
            ->addArgument('body', InputArgument::REQUIRED, 'Body of email')
            ->addArgument('from', InputArgument::REQUIRED, 'Sender of email')
            ->addArgument('to', InputArgument::REQUIRED, 'Recipient of email')
            ->addArgument('contentType', InputArgument::OPTIONAL, 'Content type of email', 'text/html')
            ->setDescription('Command to send an email');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $subject = json_decode($input->getArgument('subject'));
        $body = json_decode($input->getArgument('body'));
        $fromEmail = json_decode($input->getArgument('from'));
        $toEmail = json_decode($input->getArgument('to'));
        $contentType = $input->getArgument('contentType');

        $msg = $this->get('mailer')->createMessage('message');
        $msg->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($body)
            ->setContentType($contentType)
            ->setCharset('UTF-8');

        $this->get('mailer')->send($msg);
    }


    /**
     * Get the services
     *
     * @param $id
     * @return object
     */
    private function get($id)
    {
        return $this->getContainer()->get($id);
    }
}