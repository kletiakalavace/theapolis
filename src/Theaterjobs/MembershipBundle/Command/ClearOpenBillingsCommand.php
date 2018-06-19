<?php

namespace Theaterjobs\MembershipBundle\Command;

use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;
use Theaterjobs\MembershipBundle\Entity\Billing;

/**
 * Class ClearOpenBillingsCommand
 * @package Theaterjobs\MembershipBundle\Command
 */
class ClearOpenBillingsCommand extends ContainerAwareCommand implements CronCommand
{
    use ScheduleEveryNight, ContainerTrait;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('theaterjobs:cron:clear-open-billings')
            ->setDescription('Command to remove unpaid paypal/sofort bills');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->get("doctrine.orm.entity_manager");
        $billings = $em->getRepository(Billing::class)->findOpenPaypalSofortBilling();
        $i = 0;
        $batchSize = 20;
        foreach ($billings as $bill) {
            $em->remove($bill);
            if ($i % $batchSize === 0) {
                $em->flush();
                $em->clear();
            }
            ++$i;
        }
        $em->flush();
        $output->writeln(sprintf("Deleted %d open paypal billings", $i));
        $output->writeln("Command executed successfully");
    }
}
