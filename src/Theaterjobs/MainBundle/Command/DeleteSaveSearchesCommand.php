<?php

namespace Theaterjobs\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\MainBundle\Entity\SaveSearch;

/**
 * Delete job save searches of a user
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 * Class DeleteSaveSearchesCommand
 * @package Theaterjobs\MembershipBundle\Command
 */
class DeleteSaveSearchesCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('app:delete:save-searches')
            ->addArgument('profileId', InputArgument::REQUIRED, 'Profile Id')
            ->setDescription('Command to archive published educations of a user');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $esm = $this->getContainer()->get('fos_elastica.manager');
        $saveSearchesFinder = $this->getContainer()->get('fos_elastica.finder.theaterjobs.searches');

        $i = 0;
        $profileId = $input->getArgument('profileId');

        //Get all save searches of this user
        $query = $esm->getRepository(SaveSearch::class)->jobSearchesByPeopleId($profileId);
        $saveSearches = $saveSearchesFinder->find($query, 1000);

        // Max 20 save searches
        foreach ($saveSearches as $saveSearch) {
            $em->remove($saveSearch);
            ++$i;
        }

        $em->flush();
        $output->writeln(sprintf("Deleted %d save searches", $i));
    }
}