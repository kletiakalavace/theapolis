<?php

namespace Theaterjobs\MembershipBundle\Command;

use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Theaterjobs\AdminBundle\Entity\SepaXmlBilling;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;
use Theaterjobs\MembershipBundle\Entity\Billing;
use Theaterjobs\MembershipBundle\Entity\BillingStatus;

/**
 * Class GenerateXmlCommand
 * @package Theaterjobs\MembershipBundle\Command
 * @author Jurgen Rexhmati
 */
class GenerateXmlCommand extends ContainerAwareCommand implements CronCommand
{
    use ScheduleEveryNight, ContainerTrait;

    /** @var  EntityManager */
    protected $em;
    /** @var  \SEPA */
    private $sepa;
    /** @var  OutputInterface */
    private $output;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('theaterjobs:cron:mmembership:generate-xml')
            ->setDescription('Generate sepa-xml for new users that bought membership');
    }

    /**
     * Generates a sepa-xml for billings that have downloadedSepa flag false
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $i = 0;
            $this->output = $output;
            $this->em = $this->get("doctrine.orm.entity_manager");
            $this->_initSepa();

            $billingStatusComplete = $this->em->getRepository(BillingStatus::class)->findOneByName(BillingStatus::COMPLETE);
            $noSepaBillings = $this->em->getRepository(Billing::class)->noSepaBillings();

            // Generate sepa for new billings
            foreach ($noSepaBillings as $billing) {
                try {
                    $this->generateSepaXML($billing, $billingStatusComplete);
                } catch (\Exception $e) {
                    $output->writeln("Failed generating xml for billing id " . $billing->getId());
                    $output->writeln('Error: ' . $e->getMessage());
                    $output->writeln('Trace: \n' . $e->getTraceAsString());
                }
                $i++;
            }
            // Save xml file
            $this->_saveXml();
            $this->em->flush();
            $output->writeln(sprintf("Command executed successfully for %d users", $i));
        } catch (\Exception $e) {
            $this->output->writeln($e->getMessage());
        }
    }


    /**
     * Generates the sepa xml and adds it on $this->sepa
     *
     * @param $billing Billing
     * @param $billingStatusComplete
     */
    private function generateSepaXML($billing, $billingStatusComplete)
    {
        $profile = $billing->getBooking()->getProfile();
        $mandatRef = $profile->getLastSepaMandate()->getMandateReference();
        $mdate = $profile->getLastSepaMandate()->getCreatedAt()->format('d.m.Y');

        $tx = [
            // Seq type
            'seq' => $billing->getSequence() ?: 'RCUR',
            // Transaction ID
            'id' => $billing->getNumber(),
            // Debtor's name
            'name' => iconv('utf-8', 'ASCII//TRANSLIT//IGNORE', $billing->getAccountHolder()),
            // Mandate reference
            'mref' => $mandatRef,
            // Signature date of the mandate
            'mdate' => $mdate,
            // amount to be deducted
            'amount' => $billing->getTotal(),
            // IBAN of the payer
            'iban' => $billing->getIban(),
            'ref' => 'Theapolis Rechnung ' . $billing->getNumber() . ' vom ' . $billing->getCreatedAt()->format('d.m.Y')
        ];

        $this->sepa->add($tx);
        $billing->setDownloadedSepa(true);
        $billing->setBillingStatus($billingStatusComplete);
        $this->output->writeln(sprintf("Generated Sepa for %s", $profile->getFullName()));
    }

    /**
     * Sepa initialize
     */
    private function _initSepa()
    {
        $container = $this->getContainer();

        //licensed username
        \SEPA::init(SEPA_INIT_LICUSER, $container->getParameter('sepaapi_username'));
        \SEPA::init(SEPA_INIT_LICCODE, $container->getParameter('sepaapi_code'));
        $bookDate = date('Y-m-d', strtotime('+2 days', strtotime(date("Y-m-d"))));

        $this->sepa = new \SEPA(SEPA_MSGTYPE_DDI);
        // IBAN of the payee
        $this->sepa->setIBAN($container->getParameter('company_iban'));
        // Name of the payee
        $this->sepa->setName($container->getParameter('company_name'));
        // BIC of the payee
        $this->sepa->setBIC($container->getParameter('company_bic'));
        // Creditor Identifier
        $this->sepa->setCreditorIdentifier($container->getParameter('sepa_creditor_id'));
        $this->sepa->setDate($bookDate);
    }

    /**
     * Save xml
     */
    private function _saveXml()
    {
        $timestamp = date("Y-m-d", time());
        $filename = "All-Users-$timestamp.xml";
        $fileDir = $this->get('kernel')->getRootDir() . SepaXmlBilling::PATHDIR;
        $fs = new Filesystem();
        $xml = $this->sepa->toXML();
        $fs->dumpFile($fileDir . $filename, $xml);

        $sepaXmlBilling = new SepaXmlBilling();
        $sepaXmlBilling->setFileName($filename);
        $this->em->persist($sepaXmlBilling);

        $this->output->writeln('Generated Sepa File.');
    }
}
