<?php

namespace Theaterjobs\MainBundle\Command\Migration;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Symfony\Component\Console\Output\ConsoleOutput;
use Theaterjobs\MainBundle\Entity\Market;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Console\Helper\ProgressBar;
use Theaterjobs\ProfileBundle\Entity\MediaImage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Carbon\Carbon;

/**
 * Description of MigrateCommand
 *
 */
class MigrateCommand extends ContainerAwareCommand {

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure() {
        $this->setName('theaterjobs:migrate')
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
        $db = $input->getOption('db');
        $host = $input->getOption('host');

        $this->setDBSettings($host, $db, $limit);

        $commandOrga = $this->getApplication()->find('theaterjobs:migrate-orga');
        $commandOrga->run($input, $output);
        $commandUsers = $this->getApplication()->find('theaterjobs:migrate-users');
        $commandUsers->run($input, $output);
        $commandBillingAddress = $this->getApplication()->find('theaterjobs:migrate-billing-address');
        $commandBillingAddress->run($input, $output);
        $commandBankData = $this->getApplication()->find('theaterjobs:migrate-bank');
        $commandBankData->run($input, $output);
        $commandAccountSettings = $this->getApplication()->find('theaterjobs:migrate-settings');
        $commandAccountSettings->run($input, $output);
        $commandMemberships = $this->getApplication()->find('theaterjobs:migrate-memberships');
        $commandMemberships->run($input, $output);
        $commandNews = $this->getApplication()->find('theaterjobs:migrate-news');
        $commandNews->run($input, $output);
    }

    private function setDBSettings($host, $db, $limit) {

        $new_db = $this->getContainer()->getParameter('database_name');
        $new_host = $this->getContainer()->getParameter('database_host');
        $new_user = $this->getContainer()->getParameter('database_user');
        $new_pass = $this->getContainer()->getParameter('database_password');

        $old_db = $this->getContainer()->getParameter('old_database_name');
        $old_host = $this->getContainer()->getParameter('old_database_host');
        $old_user = $this->getContainer()->getParameter('old_database_user');
        $old_pass = $this->getContainer()->getParameter('old_database_password');

        $batchSize = 30;
        $batchNum = 0;

        $this->output->writeln('Starting Migrations | limit: ' . $limit);

        // Prepare connection to database
        $em = $this->getContainer()->get('doctrine')->getManager();
        $this->output->writeln('DB-Host: ' . $host . ' is used');
        $this->output->writeln('DB-Name: ' . $db . ' is used');
        $this->output->writeln('DB-User ' . $old_user . ' is used');

        $link = mysql_connect($host, $old_user, $old_pass);
        mysql_set_charset("utf8");
        if (!$link) {
            #throw \Doctrine\DBAL\Driver\PDOSqlsrv\Connection::ATTR_CONNECTION_STATUS;
            die('could not connect');
        }
        $this->output->writeln('Connection to ' . $host . ' was sucessful');

        // Set
        mysql_query("SET NAMES 'utf8'", $link);
        if (!mysql_select_db($db)) {
            #throw \Doctrine\DBAL\Driver\PDOSqlsrv\Connection::ATTR_FETCH_TABLE_NAMES;
            die('could not select '.$db);
        }
        $this->output->writeln('The selected database is ' . $db);
        $charset = mysql_client_encoding($link);
        $this->output->writeln('The actual charset is ' . $charset);
    }

    protected function getPDO() {
        $db = $this->getContainer()->getParameter('old_database_name');
        $host = $this->getContainer()->getParameter('old_database_host');
        $user = $this->getContainer()->getParameter('old_database_user');
        $pass = $this->getContainer()->getParameter('old_database_password');
        $dsn = "mysql:dbname=$db;host=$host";
        $dbh = new \PDO($dsn, $user, $pass);
        $dbh->exec("SET CHARACTER SET utf8");
        return $dbh;
    }

}