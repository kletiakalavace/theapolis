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
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Console\Helper\ProgressBar;
use FOS\UserBundle\Doctrine\UserManager;
use Doctrine\Common\Collections\ArrayCollection;

class MigrateProfileFavoritesCommand extends MigrateCommand
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
        $this->setName('theaterjobs:migrate-profile-fav')
            ->setDescription('Migrate from old')
            ->addArgument('limit', InputArgument::REQUIRED, 'The number of records per table to migrate');

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
        $batchNum = 0;


        $link = mysql_connect($host, $user, $pasw);
        mysql_set_charset("utf8");
        mysql_select_db($db);

        $query = <<<EOT
SELECT 
u.email, u.id, pv.person_id, pv.id as idx
FROM `users` u 
INNER JOIN person_favoriten pv 
ON pv.users_id = u.id
EOT;
        //WHERE u.id = 25105
        $query = str_replace(array("\r\n", "\r", "\n", "\t",), ' ', $query);
        // echo $query;
        $result = mysql_query($query);
        if ($result === FALSE) {
            die(mysql_error($link));
        }
        $num = 0;

        $num_rows = mysql_num_rows($result);
        $this->output->writeln($num_rows);
        $this->output->writeln('Migrating profile favorites');
        $progress = new ProgressBar($this->output, $num_rows);
        $progress->start();
        $progress->setFormat('very_verbose');
        gc_enable();
        $i = 0;

        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        
        while ($row = mysql_fetch_array($result)) {
            // get the profile from the user
            //var_dump($row);
            $profile = $this->getProfile($row['email']);
            // get the profile from the user favorite
            $queryFavProfile = "SELECT email FROM users WHERE person_id = " . $row['person_id'];
            $resultFavProfile = mysql_query($queryFavProfile);
            $rowEmailFav = mysql_fetch_array($resultFavProfile);
            //var_dump($rowEmailFav);
            $profile->addUserFavourite($this->getProfile($rowEmailFav['email']));
            $progress->advance();
            $batchNum++;
            $this->em->persist($profile);

            if ($batchNum % 1 == 0) {
                $this->em->flush();
                $this->em->clear();
                gc_collect_cycles();
            }
            $this->output->writeln('fav id successfull migrated: ' . $row['idx']);

        }
        $this->em->flush();
        $this->em->clear();
        gc_collect_cycles();
        $this->output->writeln('Profile favorite Migration succesfull');
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