<?php

namespace Theaterjobs\MainBundle\Command\Migration;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\NewsBundle\Entity\News;
use Theaterjobs\StatsBundle\Entity\View;
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
class MigrateNewsCommand extends MigrateCommand {

    /** "%theaterjobs_news.category.news.root_slug%" */
    protected $newsCategoryRoot;
    protected $rootNode;
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
        $this->setName('theaterjobs:migrate-news')
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
        $this->newsCategoryRoot = $this->getContainer()->getParameter("theaterjobs_news.category.news.root_slug");
        $batchSize = 30;
        $batchNum = 0;

        $translatable = $this->getContainer()->get('gedmo.listener.translatable');
        $translatable->setTranslatableLocale('en');
//        $this->rootNode = $this->em->getRepository(
//            'TheaterjobsCategoryBundle:Category'
//        )->findOneBy(array('title' => $this->newsCategoryRoot));
//
//        if (!$this->rootNode) {
//            throw new \Exception("No Root Category found!");
//        }
        $host = $this->getContainer()->getParameter('old_database_host');
        $db = $this->getContainer()->getParameter('old_database_name');
        $user = $this->getContainer()->getParameter('old_database_user');
        $pasw = $this->getContainer()->getParameter('old_database_password');
        $link = mysql_connect($host,$user,$pasw);
        mysql_set_charset("utf8");
        mysql_select_db($db);
        // The query

        $query = <<<EOT
select * from news_eintraege
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
            $news = new News();
            $progress->advance();
            $batchNum++;

            $news->setTitle($row['haupttitel']);
            $news->setPretitle($row['uebertitel']);
            $news->setShortDescription($row['teaser']);
            $news->setDescription($row['text']);
            $news->setPublished($row['online']);
            $news->setCreatedAt(new \DateTime($row['created_at']));
            $news->setUpdatedAt(new \DateTime($row['updated_at']));
            // we did'nt get a publishAt date from our current system
            $news->setPublishAt(new \DateTime($row['created_at']));
            $news->addTag($this->getTag($row['kat_id']));
            // we can't use the real creator, because we have more the 1 link in text field it's to complicated, now we decided to leave it empty
            //$news->setCreatedBy($this->em->getRepository('TheaterjobsProfileBundle:Profile')->findOneBy(array('id' => 2)));
            $news->setArchived(false);

            if ($row['datei_name'] !== NULL) {
                $news->setPath($row['datei_name']);
                $news->setImageDescription($row['bild_text']);
            }


            if ($row['partner_id'] !== NULL) {
                $this->getOrganizations($row['partner_id'], null, $news);
            }

            if ($row['user_id'] !== NULL) {
                $this->getUsers($row['user_id'], null, $news);
            }

            //we don't migrate commments
            /*if ($this->EntryHasComment($row['id'])) {
                $this->addNewsComments($this->EntryHasComment($row['id']), $news);
            }*/
            $this->em->persist($news);
            $this->em->flush();

            // news statistic all
            if ($this->EntryHasStatistic($row['id'])) {
                $news->setTotalViews($this->EntryHasStatistic($row['id']));
            }

            if ($this->getLastMonthStatistic($row['id'])) {
                $this->addNewsStatistic($this->getLastMonthStatistic($row['id']), $news);
            }

            if($batchNum % 2000==0){
                $this->em->flush();
                $this->em->clear();
                gc_collect_cycles();
            }
            $this->output->writeln('news_id successfull implemented: ' . $row['id']);
        }
        $this->em->flush();
        $this->em->clear();
        gc_collect_cycles();

        $this->output->writeln('News Migration succesfull');
    }


    private function getTag($oldId = 0, $dbh = null) {
        if ($dbh === null) {
            $dbh = $this->getPDO();
        }

        $stmt = $dbh->prepare(
            "SELECT name AS parent FROM news_kategorien nk
                 WHERE nk.id = :kat_id"
        );
        $stmt->bindParam(':kat_id', $oldId, \PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
            $this->output->writeln('kat ID: ' . $row->parent);
            $tag = $this->em->getRepository('TheaterjobsNewsBundle:Tags')->findOneBy(array('title' => $row->parent));
            if ($tag instanceof \Theaterjobs\NewsBundle\Entity\Tags) {
                $stmt = null;
                return $tag;
            }
        }
    }

    /**
     * @param string $oldOrgaIds
     */
    private function getOrganizations($oldOrgaIds = 0, $dbh = null, $news) {

        $oldIds = new ArrayCollection();
        $organizations = new ArrayCollection();

        //we collected our organization_ids in one field, the values are comma seperated and havn't relation to another table
        if (!strpos($oldOrgaIds, ',')) {
            $oldIds[] = $oldOrgaIds;
        } else {
            $oldIds = explode(',', $oldOrgaIds);
        }

        if ($dbh === null) {
            $dbh = $this->getPDO();
        }

        foreach ($oldIds as $key => $value) {
            $stmt = $dbh->prepare(
                "SELECT institution AS name FROM partner p
                 WHERE p.id = :partner_id"
            );
            $stmt->bindParam(':partner_id', $value, \PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(\PDO::FETCH_OBJ);

            if ($row) {
                $organization = $this->em->getRepository('TheaterjobsInserateBundle:Organization')->findOneBy(array('name' => $row->name));

                if ($organization instanceof \Theaterjobs\InserateBundle\Entity\Organization) {
                    $news->addOrganization($organization);
                }
                $stmt = null;
            }
        }
    }

    /**
     * @param string $oldUserIds
     */
    private function getUsers($oldUserIds = 0, $dbh = null, $news) {

        $oldIds = new ArrayCollection();
        //$user = new ArrayCollection();
        //
        //we collected our user_ids in one field, the values are comma seperated and havn't relation to another table
        if (!strpos($oldUserIds, ',')) {
            $oldIds[] = $oldUserIds;
        } else {
            $oldIds = explode(',', $oldUserIds);
        }

        if ($dbh === null) {
            $dbh = $this->getPDO();
        }

        foreach ($oldIds as $key => $value) {
            $stmt = $dbh->prepare(
                "SELECT id, email AS email FROM users u
                 WHERE u.id = :user_id"
            );
            $stmt->bindParam(':user_id', $value, \PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(\PDO::FETCH_OBJ);

            if ($row) {
                $user = $this->em->getRepository('TheaterjobsUserBundle:User')->findOneBy(array('email' => $row->email));

                if ($user instanceof \Theaterjobs\UserBundle\Entity\User) {
                    $profile = $user->getProfile();
                    echo 'adding user';
                    $news->addUser($profile);
                }
                $stmt = null;
            }
        }
    }

    /**
     * Check whether an entry has a comment
     *
     * @param int $oldNewsId
     */
    /*private function EntryHasComment($oldNewsId, $dbh = null) {
        if ($dbh === null) {
            $dbh = $this->getPDO();
        }

        $stmt = $dbh->prepare(
            "SELECT * FROM users_news_comment unc
                 WHERE unc.news_entry_id = :news_id"
        );
        $stmt->bindParam(':news_id', $oldNewsId, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetchAll(\PDO::FETCH_OBJ);

        if (!$row) {
            return false;
        }

        return $row;
    }*/

    /**
     * @param string $oldUserIds
     */
    /*private function addNewsComments($results, $news) {

        foreach ($results as $result) {
            $replies = new \Theaterjobs\NewsBundle\Entity\Replies();
            $replies->setNews($news);
            $replies->setProfile($this->getProfile($result->users_id));
            // at the moment we use our admin, because our user migration isn't finished
            //$replies->setProfile($this->em->getRepository('TheaterjobsUserBundle:User')->findOneBy(array('id' => $result->users_id))->getProfile());
            $replies->setCreatedAt(new \DateTime($result->created_at));
            $replies->setCheckedAt(new \DateTime($result->acknowledged_at));
            // in our current system we don't have
            $replies->setDate($result->created_at);

            // why we use the profile in this case and not the user???
            $replies->setCheckedBy($this->getProfile($result->acknowledged_id));
            // at the moment we use our admin, because our user migration isn't finished
            //$replies->setCheckedBy($this->em->getRepository('TheaterjobsUserBundle:User')->findOneBy(array('id' => $result->users_id))->getProfile());

            $replies->setComment($result->comment);
            // in our current system we don't have
            $replies->setArchivedAt(NULL);

            // in our current system we don't have
            //$replies->setUseForumAlias($useForumAlias);

            $this->em->persist($replies);
            $this->em->flush();
        }
    }*/

    private function EntryHasStatistic($oldNewsId, $dbh = null) {
        if ($dbh === null) {
            $dbh = $this->getPDO();
        }

        $stmt = $dbh->prepare(
            "SELECT count(*) as amount FROM news_gelesen_von_users ngvu
                 WHERE ngvu.news_id = :news_id"
        );
        $stmt->bindParam(':news_id', $oldNewsId, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetchAll(\PDO::FETCH_OBJ);

        if (!$row) {
            return false;
        }

        foreach ($row as $value) {
            return $value->amount;
        }
    }

    private function getLastMonthStatistic($oldNewsId, $dbh = null) {
        if ($dbh === null) {
            $dbh = $this->getPDO();
        }

        $stmt = $dbh->prepare(
            "SELECT * FROM `news_gelesen_von_users` WHERE news_id = :news_id AND zugriff >= DATE_SUB( CURDATE(), INTERVAL 1 MONTH)"
        );
        $stmt->bindParam(':news_id', $oldNewsId, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetchAll(\PDO::FETCH_OBJ);

        if (!$row) {
            return false;
        }

        return $row;
    }




    private function addNewsStatistic($results, $news) {

        foreach ($results as $result) {
            $statistic = new View();
            $statistic->setUser($this->getUser($result->users_id));
            $statistic->setForeignKey($news->getId());
            $statistic->setObjectClass('Theaterjobs\NewsBundle\Entity\News');
            $statistic->setIp($result->ip);
            $statistic->setCreatedAt(new \DateTime($result->zugriff));

            $this->em->persist($statistic);
            $this->em->flush();
        }
    }

    private function getUser($userId, $dbh = null) {
        if ($dbh === null) {
            $dbh = $this->getPDO();
        }

        $stmt = $dbh->prepare(
            "SELECT id, email AS email FROM users u
                 WHERE u.id = :user_id"
        );
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_OBJ);

        if ($row) {
            $user = $this->em->getRepository('TheaterjobsUserBundle:User')->findOneBy(array('email' => $row->email));

            if ($user instanceof \Theaterjobs\UserBundle\Entity\User) {

                return $user;
            }

            return null;
        }
    }

    private function getProfile($userId, $dbh = null) {
        if ($dbh === null) {
            $dbh = $this->getPDO();
        }

        $stmt = $dbh->prepare(
            "SELECT id, email AS email FROM users u
                 WHERE u.id = :user_id"
        );
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_OBJ);

        if ($row) {
            $user = $this->em->getRepository('TheaterjobsUserBundle:User')->findOneBy(array('email' => $row->email));

            if ($user instanceof \Theaterjobs\UserBundle\Entity\User) {
                $profile = $user->getProfile();

                return $profile;
            }

            return null;
        }
    }

}