<?php

namespace Theaterjobs\MainBundle\Utility\Traits\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JMS\JobQueueBundle\Entity\Job as JobQueue;

/**
 * Trait CommandExecuteTrait
 * @package Theaterjobs\MainBundle\Utility\Traits\Command
 */
trait CommandExecuteTrait
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $i = 1;
        $batchSize = 50;
        $chunkSize = $this->getChunkSize();
        $em = $this->get('doctrine.orm.entity_manager');
        $ids = $this->getIds();
        if (!$ids) {
            $output->writeln('No jobs to process');
            return ;
        }
        $chunks = array_chunk($ids, $chunkSize);

        foreach ($chunks as $chunk) {
            // Process users
            $job = new JobQueue($this->getJobName(), [json_encode($chunk)], true, "cron");
            $em->persist($job);
            if ($i % $batchSize === 0) {
                $em->flush();
                $em->clear(JobQueue::class);
            }
            ++$i;
        }
        $i--;
        $em->flush();
        $s = $i === 1 ? '' : 's';
        $output->writeln(sprintf("Added %d job$s to queue", $i));
        $output->writeln(sprintf("Total results %d", count($ids)));
    }

}