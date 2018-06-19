<?php

namespace Theaterjobs\MembershipBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\InserateBundle\Entity\Education;
use Theaterjobs\InserateBundle\Entity\Job;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Achieve educations job
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 * Class AchieveEducationsCommand
 * @package Theaterjobs\MembershipBundle\Command
 * @DI\Service
 * @DI\Tag("console.command")
 */
class AchieveEducationsCommand extends ContainerAwareCommand
{
    /** @DI\Inject("doctrine.orm.entity_manager") */
    public $em;

    /** @DI\Inject("fos_elastica.manager") */
    public $esm;

    /** @DI\Inject("fos_elastica.finder.theaterjobs.job") */
    public $jobFinder;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('app:archive:educations')
            ->addArgument('userId', InputArgument::REQUIRED, 'User Id')
            ->addArgument('organizationName', InputArgument::OPTIONAL, 'Archive jobs of user also in organization name.')
            ->setDescription('Command to archive published educations of a user');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batchSize = 50;
        $userId = $input->getArgument('userId');
        $organizationName = (boolean) $input->getArgument('organizationName');

        //Get all published educations of this user
        $query = $this->esm->getRepository(Job::class)->getPublishedEducationsByUser($userId,$organizationName);
        $publishedEducations = $this->jobFinder->find($query, 1000);

        $i = 1;
        foreach ($publishedEducations as $education) {
            $education->setStatus(Education::STATUS_ARCHIVED);

            if (($i % $batchSize) === 0) {
                $this->em->flush(); // Executes all updates.
                $this->em->clear(); // Detaches all objects from Doctrine!
            }
            ++$i;
        }
        $this->em->flush();
        $output->writeln(sprintf("Archived %d educations", --$i));
    }
}