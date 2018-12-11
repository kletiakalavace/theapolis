<?php

namespace Theaterjobs\MainBundle\Command\Migration;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *  Batch command that executes following commands:
 *  - "doctrine:schema:drop --force"
 *  - "doctrine:schema:create"
 *  - "doctrine:fixtures:load --fixtures=/path/to/fixtures1"
 */
class PrepareDbMigrationCommand extends ContainerAwareCommand {

    protected function execute(InputInterface $input, OutputInterface $output) {

        $output = new \Symfony\Component\Console\Output\ConsoleOutput();

        $command = $this->getApplication()->find("theaterjobs:drop-tables");
        $arguments = array("command" => "theaterjobs:drop-tables");
        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        $command = $this->getApplication()->find("doctrine:schema:create");
        $arguments = array("command" => "doctrine:schema:create");
        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        $command = $this->getApplication()->find("doctrine:fixtures:load");
        $arguments = array(
            // Inserate Bundle
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/InserateBundle/DataFixtures/ORM/LoadJobCategoryData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/InserateBundle/DataFixtures/ORM/LoadFormOfOrganizationData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/IserateBundle/DataFixtures/ORM/LoadTagsStageData.php",
                "--append" => true,
            ),
            //we are using some of them, we must clean up
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/InserateBundle/DataFixtures/ORM/LoadTypeOfNotificationsData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/InserateBundle/DataFixtures/ORM/LoadGratificationData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/InserateBundle/DataFixtures/ORM/LoadOrganizationKindData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/InserateBundle/DataFixtures/ORM/LoadOrganizationScheduleData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/InserateBundle/DataFixtures/ORM/LoadOrganizationSections.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/InserateBundle/DataFixtures/ORM/LoadJobTitleData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/InserateBundle/DataFixtures/ORM/LoadProductionData.php",
                "--append" => true,
            ),
            // Profile Bundle
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/ProfileBundle/DataFixtures/ORM/LoadCreatorsData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/ProfileBundle/DataFixtures/ORM/LoadDirectorsData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/ProfileBundle/DataFixtures/ORM/LoadSkillData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/ProfileBundle/DataFixtures/ORM/LoadLanguageData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/ProfileBundle/DataFixtures/ORM/LoadVoiceCategoryData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/ProfileBundle/DataFixtures/ORM/LoadEyeColorData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/ProfileBundle/DataFixtures/ORM/LoadHairColorData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/ProfileBundle/DataFixtures/ORM/LoadProfileCategoryData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/ProfileBundle/DataFixtures/ORM/LoadLicenceData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/ProfileBundle/DataFixtures/ORM/LoadTypeOfCategoryData.php",
                "--append" => true,
            ),
            // Main Bundle
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/MainBundle/DataFixtures/ORM/LoadCountryData.php",
                "--append" => true,
            ),
            // Membership Bundle
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/MembershipBundle/DataFixtures/ORM/LoadBillingStatiData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/MembershipBundle/DataFixtures/ORM/LoadPaymentmethodData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/MembershipBundle/DataFixtures/ORM/LoadCountryTaxRateData.php",
                "--append" => true,
            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/MembershipBundle/DataFixtures/ORM/LoadMembershipData.php",
                "--append" => true,
            ),
            // News Bundle
// we don't use categories in news anymore
//            array("command" => "doctrine:fixtures:load",
//                "--fixtures" => "src/Theaterjobs/NewsBundle/DataFixtures/ORM/LoadNewsCategoryData.php",
//                "--append" => true,
//            ),
            array("command" => "doctrine:fixtures:load",
                "--fixtures" => "src/Theaterjobs/NewsBundle/DataFixtures/ORM/LoadTagsNewsData.php",
                "--append" => true,
            ),
        );

        foreach($arguments as $argument) {
            $input = new ArrayInput($argument);
            $returnCode = $command->run($input, $output);
        }

    }

    protected function configure() {
        $this
            ->setName('theaterjobs:MigrationPrepareDB')
            ->setDescription('Drops the schema, creates schema, and loads only necessary fixtures for migration.')
        ;
    }

}
