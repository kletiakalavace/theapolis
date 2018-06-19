<?php

namespace Theaterjobs\MainBundle\Command;

use DOMDocument;
use JMS\JobQueueBundle\Console\CronCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Carbon\Carbon;
use Theaterjobs\MainBundle\Utility\Traits\Command\ScheduleEveryNight;

/**
 *  Generates all active organization url for side-map
 *
 * @package Theaterjobs\MainBundle\Command
 *
 * @author Igli Hoxha <igliihoxhan@gmail.com>
 */
class OrganizationSiteMapsCommand extends ContainerAwareCommand implements CronCommand
{
    use ScheduleEveryNight;

    /**
     * Command configuration
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('theaterjobs:cron:organization:site:maps')
            ->setDescription('Command that creates site-map for all active organization urls');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get time in sec when the cron start
        $startScript = microtime(true);

        $fosOrganization = $this->getContainer()->get('fos_elastica.manager')->getRepository('TheaterjobsInserateBundle:Organization');
        $organizationIndex = $this->getContainer()->get('fos_elastica.index.theaterjobs.organization');
        $activeOrganizationsQuery = $fosOrganization->getActiveOrganizations();
        $activeOrganizations = $organizationIndex->search($activeOrganizationsQuery, 10000);
        $activeOrganizationsResults = $activeOrganizations->getResults();


        $xml = new DOMDocument('1.0', 'UTF-8');
        $xmlUrlSet = $xml->createElement("urlset");
        $xmlUrlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $today = Carbon::today();

        foreach ($activeOrganizationsResults as $organization) {
            $xmlUrl = $xml->createElement("url");
            $xmlLoc = $xml->createElement("loc");
            $loc = $this->getContainer()->get('router')->generate('tj_organization_show', ['slug' => $organization->slug], true);
            $xmlLocTextNode = $xml->createTextNode($loc);
            $xmlLoc->appendChild($xmlLocTextNode);
            $xmlLastMod = $xml->createElement("lastmod");
            $xmlLastModTextNode = $xml->createTextNode($today->format('Y-m-d'));
            $xmlLastMod->appendChild($xmlLastModTextNode);
            $xmlChangeFreq = $xml->createElement("changefreq");
            $xmlChangeFreqTextNode = $xml->createTextNode("daily");
            $xmlChangeFreq->appendChild($xmlChangeFreqTextNode);
            $xmlPriority = $xml->createElement("priority");
            $xmlPriorityTextNode = $xml->createTextNode("0.5");
            $xmlPriority->appendChild($xmlPriorityTextNode);
            $xmlUrl->appendChild($xmlLoc);
            $xmlUrl->appendChild($xmlLastMod);
            $xmlUrl->appendChild($xmlChangeFreq);
            $xmlUrl->appendChild($xmlPriority);
            $xmlUrlSet->appendChild($xmlUrl);
        }

        $xml->appendChild($xmlUrlSet);
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;

        $xml->save($this->getContainer()->get('kernel')->getRootDir() . '/../web/sitemaps/sitemap_organization.xml');
        $count = $activeOrganizations->getTotalHits();

        $output->writeln("Cron for job organization:site:maps took " . round((microtime(true) - $startScript), 3) . " sec for $count records");
    }
}
