<?php

namespace Theaterjobs\InserateBundle\Command\CronJob;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Translator;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\InserateBundle\Model\JobSearch;
use Theaterjobs\MainBundle\Entity\SaveSearch;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Notifies users that have not updated profile for more than 100 days
 * Class SaveSearchNotificationsCommand
 * @package Theaterjobs\InserateBundle\Command\CronJob
 * @DI\Service
 * @DI\Tag("console.command")
 */
class SaveSearchNotificationsCommand extends ContainerAwareCommand
{
    use ContainerTrait;

    /**
     * @var Translator
     * @DI\Inject("translator")
     */
    public $trans;

    /**
     * @DI\Inject("%from_email_address%")
     */
    public $fromEmail;

    /** @var  OutputInterface */
    protected $output;


    protected function configure()
    {
        $this->setName('theaterjobs:cron:saved-search:notifications')
            ->addArgument('userIds', InputArgument::REQUIRED, 'User Ids to process')
            ->setDescription('Notify users for new created jobs from their saved search');
    }

    /**
     * Notifies users that have not updated profile for more than 100 days
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $ids = $input->getArgument('userIds');

        // Get Ids from param
        $saveSearchesIds = json_decode($ids);
        $em = $this->get('doctrine.orm.entity_manager');
        $saveSearches = $em->getRepository(SaveSearch::class)->findById($saveSearchesIds);
        // Process Save searches
        $this->sendNotifications($saveSearches);
    }

    /**
     * Send notification to users
     *
     * @param SaveSearch[] $saveSearches
     *
     * @return void
     */
    protected function sendNotifications($saveSearches)
    {
        foreach ($saveSearches as $saveSearch) {
            $newJobs = $this->getJobSearchResults($saveSearch->getParams());;
            $count = count($newJobs);
            if (!$count) {
                continue;
            }

            $jobTitles = "";
            // List all job titles and their link
            foreach ($newJobs as $result) {
                $jobTitles .= "<br> $result->title - " . $this->getJobUrl($result->slug) . "\n";
            }
            // Send Email
            $this->sendEmail($saveSearch, $jobTitles, $count);
        }
    }


    /**
     * Get job results from save Search
     * @param $urlParams
     * @return array
     */
    private function getJobSearchResults($urlParams)
    {
        $jobSearch = new JobSearch();
        $jobSearch->setSavedSearch(true);
        foreach ($urlParams as $key => $value) {
            $jobSearch->setVar($key, $value);
        }

        $query = $this->get('fos_elastica.manager')->getRepository(Job::class)->search($jobSearch);
        return $this->get('fos_elastica.index.theaterjobs.job')->search($query, 1000);
    }

    /**
     * @param $slug
     * @return string
     */
    private function getJobUrl($slug)
    {
        return $this->get('router')->generate('tj_inserate_job_route_show', ['slug' => $slug], true);
    }

    /**
     * @param $saveSearch
     * @return string
     */
    private function getSearchTags($saveSearch)
    {
        $tags = $this->get('theaterjobs.main_bundle.save_search')->getParamsArr($saveSearch);
        unset($tags['location']);
        foreach ($tags as $i => $tag) {
            if ($tag === 1 && !in_array($i, ['searchPhrase', 'page'])) {
                $tags[$i] = $i;
            }
        }
        return implode(', ', $tags);
    }

    /**
     * @param $saveSearch
     * @param $count
     * @param $jobTitles
     */
    private function sendEmail($saveSearch, $count, $jobTitles)
    {
        $profile = $saveSearch->getProfile();

        $searchTags = $this->getSearchTags($saveSearch);
        $transKey = $count ? 'Multiple' : 'single';
       $countTitle = $this->trans->trans("savedSearches.newJobs.email.resultCount$transKey", ['%count%' => $count]);

        $emailBody = "<br>$countTitle<br>$searchTags : $jobTitles<br>";
        $body = $this->get('twig')->render('TheaterjobsInserateBundle:Job/email:savedSearchesEmail.html.twig', [
            'profile' => $profile,
            'body' => $emailBody
        ]);
        // Send Email
        $this->get('base_mailer')->sendRenderedEmailMessage($body, $this->fromEmail, $profile->getUser()->getEmail());
    }
}