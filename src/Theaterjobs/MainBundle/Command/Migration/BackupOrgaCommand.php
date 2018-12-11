<?php

namespace Theaterjobs\MainBundle\Command\Migration;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create an backup from all tables connected with organisations from the database as csv file
 *
 * @category Command
 * @package  Theaterjobs\MainBundle\Command
 * @author   Jana Kaszas <jana@theapolis.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class BackupOrgaCommand extends ContainerAwareCommand
{

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('theaterjobs:backup-orga')
             ->setDescription('Create a back of organisations');
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
            $this->exportTables();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Drop the tables.
     */
    protected function exportTables()
    {
        ob_start();
        $this->output->write("Creating Backup from orga tables to create cvs fixtures... ");
        $host = $this->getContainer()->getParameter('database_host');
        $db = $this->getContainer()->getParameter('database_name');
        $user = $this->getContainer()->getParameter('database_user');
        $pasw = $this->getContainer()->getParameter('database_password');
        $link = mysql_connect($host,$user,$pasw);
        mysql_set_charset("utf8");
        mysql_select_db($db);
        // The query

        $csv_fields['tj_inserate_organizations'] = array('id', 'tj_inserate_contact_section_id', 'tj_inserate_form_of_organizations_id', 'organization_schedule', 'merge_to_id',
            'tj_inserate_addresses_id', 'path', 'name', 'description', 'slug', 'is_visible_in_list', 'is_vio', 'is_visible_in_register', 'created_at', 'updated_at',
            'destroyedAt', 'archived_at' , 'notReachableAt', 'wage_from', 'wage_to', 'organization_owner', 'OrchestraClass', 'staff', 'geolocation', 'application_info_text',
            'application_info_date', 'status');

        $csv_fields['tj_inserate_organizations_organization_kind'] = array('organization_id','organizationkind_id');

        $csv_fields['tj_inserate_organizations_organization_sections'] = array('organization_id','organizationsection_id');

        $csv_fields['tj_inserate_organizations_ensemble'] = array('id', 'organization_id','title');

        $csv_fields['tj_inserate_organizations_staff'] = array('id', 'organization_id','title', 'number');

        $csv_fields['tj_inserate_organizations_stage'] = array('id', 'organization_id','stageTitle', 'seats', 'stageWidth', 'stageDepth', 'portalWidth', 'portalDepth',
            'turntable', 'hubStages', 'more_infor');

        $csv_fields['tj_inserate_organization_grants'] = array('id', 'organization_id','budget', 'grants', 'season', 'more_infor');

        $csv_fields['tj_inserate_organization_performance'] = array('id', 'organization_id','performance_number', 'season', 'more_infor');

        $csv_fields['tj_inserate_organization_visitors'] = array('id', 'organization_id','visitors_number', 'season', 'more_infor');

        $csv_fields['tj_inserate_section_contact'] = array('id','email', 'contact');


        $tables = array('tj_inserate_organizations',
                        'tj_inserate_organizations_organization_kind',
                        'tj_inserate_organizations_organization_sections',
                        'tj_inserate_organizations_ensemble',
                        'tj_inserate_organizations_staff',
                        'tj_inserate_organizations_stage',
                        'tj_inserate_organization_grants',
                        'tj_inserate_organization_performance',
                        'tj_inserate_organization_visitors',
                        'tj_inserate_section_contact'
                        );

        foreach ($tables as $table) {
            $query = "SELECT * FROM " . $table;

            $result = mysql_query($query);

            if ($result !== false) {
                $fp = fopen($table.'.csv', 'w');
                fputcsv($fp, $csv_fields[$table]);
                while ($row = mysql_fetch_array($result)) {
                    fputcsv($fp, $row);

                }

                fclose($fp);

                $this->output->writeln("<info>$table.csv created!</info>");
            } else {
                $this->output->writeln("<info>no data available for $table backup</info>");
            }
        }

    }
}

