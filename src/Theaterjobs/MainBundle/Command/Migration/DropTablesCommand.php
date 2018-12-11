<?php

namespace Theaterjobs\MainBundle\Command\Migration;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Drop the existing tables from the database
 *
 * @category Command
 * @package  Theaterjobs\MainBundle\Command
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class DropTablesCommand extends ContainerAwareCommand
{

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('theaterjobs:drop-tables')
             ->setDescription('Drops all tables in the database');
    }

    /**
     * @param InputInterface  $input  The input interface.
     * @param OutputInterface $output The output interface.
     *
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        try {
            $this->dropTables();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Drop the tables.
     */
    protected function dropTables()
    {
        $this->output->write("Dropping Database Tables... ");
        $con = $this->getContainer()->get('doctrine')->getConnection();
        $stmt = $con->query('SHOW TABLES');
        if ($stmt->rowCount()) {
            $dropsql =  "SET foreign_key_checks=0;SET unique_checks=0;";
            $dropsql .= "DROP TABLE ";
            while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                $dropsql .= "{$row[0]}, ";
            }

            $dropsql = preg_replace("#, $#", ";", $dropsql);
            $dropsql .= "SET foreign_key_checks=1;SET unique_checks=1;";
            $stmt = $con->query($dropsql);
            $this->output->writeln("<info>done!</info>");
        } else {
            $this->output->writeln("<info>empty. skipping!</info>");
        }

    }
}

