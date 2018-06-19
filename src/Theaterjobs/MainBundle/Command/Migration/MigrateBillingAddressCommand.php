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
use Theaterjobs\MembershipBundle\Entity\BillingAddress;
use Theaterjobs\MembershipBundle\Entity\DebitAccount;
use Theaterjobs\MembershipBundle\Entity\Membership;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\ProgressBar;
use FOS\UserBundle\Doctrine\UserManager;
use Doctrine\Common\Collections\ArrayCollection;


class MigrateBillingAddressCommand extends MigrateCommand
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
        $this->setName('theaterjobs:migrate-billing-address')
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
SELECT u.email,u.id,
country.name as country_name,
addr.firma as company,
addr.ust_id as vat_id,
addr.ust_id_validiert as vat_is_validated,
addr.vorname as firstname,
addr.nachname as lastname,
addr.strasse as street,
addr.hausnummer as street_number,
addr.plz as zip,
addr.ort as city,
addr.telefon as phone 
FROM users u 
INNER JOIN userkontoeinstellungen uke 
ON u.userkontoeinstellungen_id = uke.id 
Inner Join rechnungsadresse addr 
ON uke.rechnungsadresse_id = addr.id
LEFT JOIN tm_land_tree country on addr.land_id = country.id
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
        $this->output->writeln('Migrating memberships');
        $progress = new ProgressBar($this->output, $num_rows);
        $progress->start();
        $progress->setFormat('very_verbose');
        gc_enable();
        $i = 0;

        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        while ($row = mysql_fetch_array($result)) {
            
            $className = 'Theaterjobs\MembershipBundle\Entity\BillingAddress';
            $this->em->getClassMetadata($className)->setLifecycleCallbacks(array());
            $billingAddress = new BillingAddress();
            $billingAddress->setCreatedAt(new \DateTime());
            $billingAddress->setUpdatedAt(new \DateTime());
            $billingAddress->setCity($row['city']);
            $billingAddress->setCountry($row['country_name']);
            $billingAddress->setCompany($row['company']);
            $billingAddress->setEmail($row['email']);
            $billingAddress->setFirstname($row['firstname']);
            $billingAddress->setLastname($row['lastname']);
            $billingAddress->setStreet($row['street'] . ' ' . $row['street_number']);
            $billingAddress->setZip($row['zip']);
            $billingAddress->setVatId($row['vat_id']);
            $billingAddress->setProfile($this->getProfile($row['email']));
            $this->em->persist($billingAddress);
            $progress->advance();
            $batchNum++;
            if ($batchNum % 2000 == 0) {
                $this->em->flush();
                $this->em->clear();
                gc_collect_cycles();
            }
            //$this->output->writeln('booking id successfull implemented: ' . $row['id']);

        }
        $this->em->flush();
        $this->em->clear();
        gc_collect_cycles();
        $this->output->writeln('Bolling Address Migration succesfull');
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