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
 *  - "doctrine:fixtures:load"
 */
class PrepareDbCommand extends ContainerAwareCommand {

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();

        $command = $this->getApplication()->find("theaterjobs:drop-tables");
        $arguments = array("command" => "theaterjobs:drop-tables");
        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        $command = $this->getApplication()->find("doctrine:schema:create");
        $dialog = $command->getHelper('dialog');
        $dialog->setInputStream($this->getInputStream('y\n'));
        $arguments = array("command" => "doctrine:schema:create");
        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        $command = $this->getApplication()->find("doctrine:fixtures:load");
        $arguments = array("command" => "doctrine:fixtures:load");
        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);
    }

    protected function configure() {
        $this
            ->setName('theaterjobs:PrepareDB')
            ->setDescription('Drops the schema, creates schema, and loads all fixtures.')
        ;
    }

    protected function getInputStream($input) {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);
        return $stream;
    }

}