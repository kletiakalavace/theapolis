<?php

namespace Theaterjobs\MainBundle\Command\Migration;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\AdminBundle\Entity\JobHunt;
use Theaterjobs\NewsBundle\Entity\News;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Console\Helper\ProgressBar;
use Theaterjobs\ProfileBundle\Entity\MediaImage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use FOS\UserBundle\Doctrine\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use Theaterjobs\NewsBundle\Entity\Replies;

/**
 * Description of MigrateCommand
 *
 */
class MigrateJobboersenCommand extends MigrateCommand {

    protected $em;
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure() {
        $this->setName('theaterjobs:migrate-jobboersen')
            ->setDescription('Migrate from old')
            ->addArgument('limit', InputArgument::REQUIRED, 'The number of records per table to migrate')
            ->addOption(
                'db', null, InputOption::VALUE_REQUIRED, 'The database to use', 'db159502_27'
            )
            ->addOption(
                'host', null, InputOption::VALUE_REQUIRED, 'The host of the database', 'localhost'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->input = $input;
        $this->output = $output;
        $limit = $this->input->getArgument('limit');
        $this->em = $this->getContainer()->get("doctrine.orm.entity_manager");
        $this->userManager = $this->getContainer()->get("fos_user.user_manager");
        $batchSize = 30;
        $batchNum = 0;

        $host = $this->getContainer()->getParameter('old_database_host');
        $db = $this->getContainer()->getParameter('old_database_name');
        $user = $this->getContainer()->getParameter('old_database_user');
        $pasw = $this->getContainer()->getParameter('old_database_password');
        $link = mysql_connect($host,$user,$pasw);
        mysql_set_charset("utf8");
        mysql_select_db($db);
        // The query

        $query = <<<EOT
select * from jobboersen
EOT;

        $result = mysql_query($query);
        if ($result === FALSE) {
            die('No results');
        }
        $num = 0;

        $num_rows = mysql_num_rows($result);
        $this->output->writeln('');
        $this->output->writeln('Migrating News');
        $progress = new ProgressBar($this->output, $num_rows);
        $progress->start();
        $progress->setFormat('very_verbose');
        gc_enable();
        while ($row = mysql_fetch_array($result)) {
            $jobhunt = new JobHunt();
            $progress->advance();
            $batchNum++;

            $jobhunt->setName($row['name']);
            $jobhunt->setUrl('http://'.$row['url']);
            $jobhunt->setPriority($row['priority']);
            $jobhunt->setIntervalDays($row['frequency']);
            $jobhunt->setDescription($row['remark']);

            $this->em->persist($jobhunt);
            if($batchNum % 2000==0){
                $this->em->flush();
                $this->em->clear();
                gc_collect_cycles();
            }
            //$this->output->writeln('news_id successfull implemented: ' . $row['id']);
        }
        $this->em->flush();
        $this->em->clear();
        gc_collect_cycles();

        $this->output->writeln('Jobboeresen Migration succesfull');
    }
}