<?php


namespace Theaterjobs\MainBundle\Command\Migration;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use FOS\UserBundle\Doctrine\UserManager;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\NewsBundle\Entity\News;

class MigrateApplicationInfosCommand extends MigrateCommand
{

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
        $this->setName('theaterjobs:migrate-application-infos')
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
        $em = $this->getContainer()->get('doctrine')->getManager();


        $link = mysql_connect($host, $user, $pasw);
        mysql_set_charset("utf8");
        mysql_select_db($db);

        $query = <<<EOT
SELECT * FROM `bericht` INNER JOIN partner ON bericht.partner_id = partner.id
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
        $this->output->writeln('Migrating application infos');
        $progress = new ProgressBar($this->output, $num_rows);
        $progress->start();
        $progress->setFormat('very_verbose');
        gc_enable();
        $i = 0;

        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        while ($row = mysql_fetch_array($result)) {
            // get the organization by name
            var_dump($row['institution']);
            $orga = $em->getRepository('TheaterjobsInserateBundle:Organization')->findOneBy(array('name' => $row['institution']));

            $tag = $em->getRepository('TheaterjobsNewsBundle:Tags')->findOneBy(array('title' => 'Vakanzenberichte'));
            if ($row['fazit'] != '') {
                $info = $row['beschreibung']. "</br></br>" .$row['fazit'];
            } else {
                $info = $row['beschreibung'];
            }

            if ($orga instanceof Organization) {
                $news = new News();
                $news->setTitle($orga->getName() . ' erteilt Auskunft über künstlerische Solovakanzen');
                $news->setPretitle('Interview');
                $news->setShortDescription($row['title']);
                $news->setDescription(nl2br($info));
                $news->setPublished(true);
                $news->setCreatedAt(new \DateTime($row['created_at']));
                $news->setUpdatedAt(new \DateTime($row['updated_at']));
                // we decided to add all vacancy reports to Karen (karen@theapolis.de), because we don't have any information about the creator in our current system
                // for local testing use admin
                $news->setCreatedBy($this->em->getRepository('TheaterjobsUserBundle:User')->findOneBy(array('email' => 'admin@admin.com'))->getProfile());
                // we did'nt get a publishAt date from our current system
                $news->setPublishAt(new \DateTime($row['created_at']));
                $news->setArchived(false);
                $news->addOrganization($orga);
                $news->addTag($tag);

                $batchNum++;


                if ($batchNum % 1 == 0) {
                    $this->em->persist($news);
                    $this->em->flush();
                    $this->em->clear();
                    gc_collect_cycles();
                }
            }
            //$this->output->writeln('fav id successfull migrated: ' . $row['idx']);

        }
//        $this->em->flush();
//        $this->em->clear();
//        gc_collect_cycles();
        $this->output->writeln('Application Info Migration succesfull');
    }
}