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
use Theaterjobs\MembershipBundle\Entity\Membership;
use Theaterjobs\MembershipBundle\Entity\Paymentmethod;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Console\Helper\ProgressBar;
use FOS\UserBundle\Doctrine\UserManager;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of MigrateCommand
 *
 */
class MigrateMembershipsCommand extends MigrateCommand
{
    /**
     * @var EntityManager $em
     */
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
        $this->setName('theaterjobs:migrate-memberships')
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
select 
mb.id,mb.bezahlt_bis,mb.timestamp,mb.is_acknowledged,
u.email,u.id,
pm.bezahlmethode,
adr.*
from users_bestellung_mitgliedschaft mb 
inner join users u on mb.user_id = u.id
left join users_rechnungen adr on mb.id = adr.users_id
left join bezahlmethode pm on mb.bezahlmethode_id = pm.id
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
        $finishedBillingAddress = true;
        while ($row = mysql_fetch_array($result)) {

            $booking = new Booking();
            $booking->setCreatedAt(new \DateTime($row['timestamp']));
            $booking->setUpdatedAt(new \DateTime($row['timestamp']));
            $this->getMembershipType($booking);
            $this->getPaymentMethod($row['bezahlmethode'], $booking);
            $this->setProfile($row['email'], $booking);
            if ($finishedBillingAddress) {
                if (!$booking->getProfile()->getBillingAddress()) {
                    $this->billingAddress($row);
                }
            }
            $progress->advance();
            $batchNum++;

            $this->em->persist($booking);

            if ($batchNum % 2000 == 0) {
                $this->em->flush();
                $this->em->clear();
                gc_collect_cycles();
            }
            $this->output->writeln('booking id successfull implemented: ' . $row['id']);

        }
        $this->em->flush();
        $this->em->clear();
        gc_collect_cycles();
        $this->output->writeln('Membership Migration succesfull');
    }

    private function getPaymentMethod($pmName, Booking $booking)
    {
        /**
         * @var Paymentmethod $entity
         */
        $entity = null;

        switch ($pmName) {
            case 'vorkasse':
                $entity = $this->em->getRepository('TheaterjobsMembershipBundle:Paymentmethod')->findOneBy(array('short' => 'prepay'));
                break;
            case 'paypal':
                $entity = $this->em->getRepository('TheaterjobsMembershipBundle:Paymentmethod')->findOneBy(array('short' => 'paypal'));
                break;
            case 'lastschrift':
                $entity = $this->em->getRepository('TheaterjobsMembershipBundle:Paymentmethod')->findOneBy(array('short' => 'direct'));
                break;
            default:
                $entity = null;

        }
        $booking->setPaymentmethod($entity);
        $entity->addBooking($booking);
        $this->em->persist($entity);
    }

    public function getMembershipType(Booking $booking, $membership = 'membership-for-one-year')
    {

        $membership = $this->em->getRepository('TheaterjobsMembershipBundle:Membership')->findOneBy(array('slug' => $membership));
        $booking->setMembership($membership);
        $membership->addBooking($booking);
        $this->em->persist($membership);
    }

    public function setProfile($email, Booking $booking)
    {
        $user = $this->em->getRepository('TheaterjobsUserBundle:User')->findOneBy(array('email' => $email));
        $profile = $user->getProfile();
        $booking->setProfile($profile);
        $profile->addBooking($booking);
        $this->em->persist($profile);
    }


    private function getPaymentMethodPrice($pmName)
    {
        /**
         * @var Paymentmethod $entity
         */
        $entity = null;

        switch ($pmName) {
            case 'vorkasse':
                $entity = $this->em->getRepository('TheaterjobsMembershipBundle:Paymentmethod')->findOneBy(array('short' => 'prepay'));
                break;
            case 'paypal':
                $entity = $this->em->getRepository('TheaterjobsMembershipBundle:Paymentmethod')->findOneBy(array('short' => 'paypal'));
                break;
            case 'lastschrift':
                $entity = $this->em->getRepository('TheaterjobsMembershipBundle:Paymentmethod')->findOneBy(array('short' => 'direct'));
                break;
            default:
                $entity = null;

        }
        return $entity->getPrice();
    }

    public function billingAddress($row)
    {
        
    }

    public function billing($row, Booking $booking)
    {
        if($row['mandatsreferenz']) {
            $billing = new Billing();
            $className = 'Theaterjobs\MembershipBundle\Entity\Billing';
            $this->em->getClassMetadata($className)->setLifecycleCallbacks(array());
            $billing->setAccountHolder($row['vorname'] . ' ' . $row['nachname']);
            $billing->setBic($row['bic']);
            $billing->setBooking($booking);
            $billing->setIban($row['iban']);
            $billing->setNumber($row['mandatsreferenz']);
            $billing->setPath('test');
            $billing->setPaymentmethodPrice($this->getPaymentMethodPrice($row['bezahlmethode']));
            
            $statNum = intval($row['status_id'])+1;
            $status = $this->em->getRepository('TheaterjobsMembershipBundle:BillingStatus')->find($statNum);

            $billing->setBillingStatus($status);
            $billing->setSumGross($row['brutto']);
            $billing->setSumNet($row['netto']);
            $billing->setSumVat($row['steuerbetrag']);
            $billing->setTaxRate($row['steuersatz']);
            $billing->setTotal($row['brutto']);
            $billing->setCreatedAt(new \DateTime($row['created_at']));
            $billing->setUpdatedAt(new \DateTime($row['updated_at']));
            $billing->setSequence('FRST');
            $this->em->persist($billing);
            $booking->addBilling($billing);
            $this->em->persist($booking);
        }
    }

}
