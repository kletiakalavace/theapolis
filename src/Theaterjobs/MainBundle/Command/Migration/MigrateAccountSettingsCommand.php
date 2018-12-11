<?php

namespace Theaterjobs\MainBundle\Command\Migration;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\MembershipBundle\Entity\BillingAddress;
use Theaterjobs\MembershipBundle\Entity\Booking;
use Theaterjobs\MembershipBundle\Entity\DebitAccount;
use Theaterjobs\MembershipBundle\Entity\Membership;
use Theaterjobs\MembershipBundle\Entity\Paymentmethod;
use Theaterjobs\ProfileBundle\Entity\ProfileAllowedTo;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Console\Helper\ProgressBar;
use FOS\UserBundle\Doctrine\UserManager;
use Doctrine\Common\Collections\ArrayCollection;

class MigrateAccountSettingsCommand extends MigrateCommand
{
    protected $em;
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('theaterjobs:migrate-settings')
            ->addArgument('limit', InputArgument::REQUIRED, 'The number of records per table to migrate')
            ->setDescription('Migrate from old');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $limit = $this->input->getArgument('limit');
        $host = $this->getContainer()->getParameter('old_database_host');
        $db = $this->getContainer()->getParameter('old_database_name');
        $user = $this->getContainer()->getParameter('old_database_user');
        $pasw = $this->getContainer()->getParameter('old_database_password');
        $this->em = $this->getContainer()->get("doctrine.orm.entity_manager");
        $this->userManager = $this->getContainer()->get("fos_user.user_manager");

        $link = mysql_connect($host, $user, $pasw);
        mysql_set_charset("utf8");
        mysql_select_db($db);

        $queryString = "SELECT u.id, u.email as uEmail, uke.agg_akzeptiert as jobRules, uke.agb_akzeptiert as terms, 
uke.email_warnung as warning, uke.comment_alias as forumAlias, uke.forumalias_akzeptiert as writeForum, uke.gekuendigt as contractStatus, uke.lastschrift_ausschluss as debitAccountAllow,
uke.is_jobmail_active as jobMailStatus, uke.locked_delete as lockProfileDelete
FROM userkontoeinstellungen uke INNER JOIN users u ON uke.id = u.userkontoeinstellungen_id";
//WHERE u.id = 25105
        $query = str_replace(array("\r\n", "\r", "\n", "\t",), ' ', $queryString);
        // echo $query;
        $result = mysql_query($query);
        if ($result === FALSE) {
            die(mysql_error($link));
        }

        $num = 0;
        $num_rows = mysql_num_rows($result);
        $this->output->writeln($num_rows);
        $this->output->writeln('Migrating Account Settings');
        $progress = new ProgressBar($this->output, $num_rows);
        $progress->start();
        $progress->setFormat('very_verbose');
        gc_enable();

        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        /**
         * @var Profile $profile
         * @var ProfileAllowedTo $wrights
         */
        while ($row = mysql_fetch_array($result)) {

            $profile = $this->getProfile($row['uEmail']);
            $user = $profile->getUser();
            $wrights = $profile->getProfileAllowedTo();
            
            if ($row['debitAccountAllow']){
                $debit = $this->em->getRepository('TheaterjobsMembershipBundle:Paymentmethod')->findOneBy(array('short' => 'direct'));
                if (is_array($debit) && !empty($debit)) {
                    $profile->addBlockedPaymentmethod($debit[0]);
                }
            }
            //contractStatus, jobMailStatus
            //$profile->setForumAlias($row['forumAlias']);
            $user->setDisabledDeleteAccount($row['lockProfileDelete']);
            $wrights->setEmailWarning($row['warning']);
            $wrights->setPublishJob($row['jobRules']);
            //$wrights->setWriteToForum($row['writeForum']);

            //$this->getSession()->set('jobrules_accepted', $row['terms']);
            $progress->advance();
            $num++;
            if ($num % 2000 == 0) {
                $this->em->flush();
                $this->em->clear();
                gc_collect_cycles();
            }
            if ($num > $limit)
                break;
        }
        $this->output->writeln('Account Settings Migration succesfull');

    }

    public function getProfile($email = null){
        if(!$email || $email==''){
            return null;
        }
        else{
            $user = $this->userManager->findUserByEmail($email);
            return $user->getProfile();
        }
    }
}