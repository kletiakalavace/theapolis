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
 *  Generates all published news url for side-map
 *
 * @package Theaterjobs\MainBundle\Command
 *
 * @author Igli Hoxha <igliihoxhan@gmail.com>
 */
class NewsSiteMapsCommand extends ContainerAwareCommand implements CronCommand
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
            ->setName('theaterjobs:cron:news:site:maps')
            ->setDescription('Command that creates site-map for all published news urls');
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

        $fosNews = $this->getContainer()->get('fos_elastica.manager')->getRepository('TheaterjobsNewsBundle:News');
        $newsIndex = $this->getContainer()->get('fos_elastica.index.theaterjobs.news');
        $publishedNewsQuery = $fosNews->getPublishedNews();
        $publishedNews = $newsIndex->search($publishedNewsQuery, 10000);
        $publishedNewsResults = $publishedNews->getResults();


        $xml = new DOMDocument('1.0', 'UTF-8');
        $xmlUrlSet = $xml->createElement("urlset");
        $xmlUrlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $today = Carbon::today();

        foreach ($publishedNewsResults as $news) {
            $xmlUrl = $xml->createElement("url");
            $xmlLoc = $xml->createElement("loc");
            $loc = $this->getContainer()->get('router')->generate('tj_news_show', ['slug' => $news->slug], true);
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

        $xml->save($this->getContainer()->get('kernel')->getRootDir() . '/../web/sitemaps/sitemap_news.xml');
        $count = $publishedNews->getTotalHits();

        $output->writeln("Cron for job job:site:mapss took " . round((microtime(true) - $startScript), 3) . " sec for $count records");
    }
}
