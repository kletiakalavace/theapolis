<?php

namespace Theaterjobs\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Theaterjobs\MainBundle\Utility\Traits\Command\ContainerTrait;
use Theaterjobs\MainBundle\Utility\Traits\ESIndexTypeConfTrait;

/**
 * Class UpdateESIndexCommand
 * @package Theaterjobs\MainBundle\Command
 */
class UpdateESIndexCommand extends ContainerAwareCommand
{
    use ContainerTrait, ESIndexTypeConfTrait;

    const UPDATE = 'update';
    const DELETE = 'delete';

    /**
     * @var EntityManager $em
     */
    public $em;

    /**
     * @var ObjectPersister $objectPersister
     */
    public $objectPersister;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('theaterjobs:cron:update:es:index')
            ->addArgument('action', InputArgument::REQUIRED, 'Action name Ex. 1. update, 2. delete')
            ->addArgument('class', InputArgument::REQUIRED, 'Class name')
            ->addArgument('ids', InputArgument::REQUIRED, 'JSON encoded array of ids')
            ->addArgument('dql', InputArgument::OPTIONAL, 'DQL of an update query')
            ->setDescription('Command to update es index for bulk [update, delete]');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get Command Arguments
        $class = $input->getArgument('class');
        $action = $input->getArgument('action');
        $ids = json_decode($input->getArgument('ids'));

        $this->em = $this->get('doctrine.orm.entity_manager');
        // Get ES index name based on class name
        $indexName = $this->getIndexName($class);
        $this->objectPersister = $this->get("fos_elastica.object_persister.$indexName");
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        switch ($action) {
            case self::UPDATE:
                $dql = $input->getArgument('dql');
                $this->updateIndex($class, $ids, $dql);
                break;
            case self::DELETE:
                $this->deleteIndex($class, $ids);
                break;
            default:
                throw new \Exception('Action not defined');
        }

        $output->writeln(sprintf("%s %d of class %s", $action, count($ids), $class));
    }

    /**
     * @param string $nameClass
     * @param array $ids
     * @param string $dql
     * @throws \Exception
     */
    private function updateIndex($nameClass, array $ids, $dql)
    {
        //Update query => mysql
        if (!empty($dql)) {
            $this->em->createQuery($dql)->getResult();
        }
        //Update index  => es
        $entities = $this->em->getRepository($nameClass)->findById($ids);
        if (!$entities) {
            throw new \Exception("Related id records are not found in Database");
        }
        $this->objectPersister->replaceMany($entities);
    }

    /**
     * @param string $nameClass
     * @param array $ids
     * @throws \Exception
     */
    private function deleteIndex($nameClass, array $ids)
    {
        //Update query => mysql
        $entities = $this->em->getRepository($nameClass)->findById($ids);
        if (!$entities) {
            throw new \Exception("Related id records are not found in Database");
        }
        //Update index  => es
        $this->objectPersister->deleteMany($entities);
        $this->deleteIds($nameClass, $ids);
    }

    /**
     * @param $nameClass
     * @param $ids
     */
    private function deleteIds($nameClass, $ids)
    {
        $this->em->createQuery("DELETE FROM $nameClass _table where _table.id in (:ids)")
            ->setParameter('ids', $ids)->execute();
    }
}