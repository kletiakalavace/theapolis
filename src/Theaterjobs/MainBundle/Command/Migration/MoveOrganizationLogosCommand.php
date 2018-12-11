<?php

namespace Theaterjobs\MainBundle\Command\Migration;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;


/**
 * Load the organizations in the database.
 *
 * @category Command
 * @package  Theaterjobs\MainBundle\Command
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class MoveOrganizationLogosCommand extends ContainerAwareCommand
{
    protected $_input;
    protected $_output;

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('theaterjobs:move-organization-logos')
            ->setDescription('moves the organization logos into the right place')
            ->addArgument(
                'src',
                InputArgument::REQUIRED,
                'path to the logos'
            );
    }

    /**
     * @param InputInterface  $input  The input interface.
     * @param OutputInterface $output The output interface.
     *
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $translatable = $this->getContainer()->get('gedmo.listener.translatable');
        $translatable->setTranslatableLocale('en');

        $output->writeln(
            "Starting...\n\n"
        );
        $em = $this->getContainer()->get('doctrine')->getManager();
        $progress = $this->getHelperSet()->get('progress');
        $src = $input->getArgument('src');
        $finder = new Finder();
        $finder->files()->in($src);

        $uploadDir = $this->getContainer()->get('kernel')->getRootDir() . "/../web/uploads/logos/organizations/";

        $progress->start($output, $finder->count());
        foreach ($finder as $file) {
            $orga = $em->getRepository("TheaterjobsInserateBundle:Organization")
                ->findOneByPath($file->getFileName());

            if ($orga) {
                $id = $orga->getId();
                $dest = $uploadDir . "$id/";
                if (!file_exists($dest)) {
                    if(!mkdir($dest, 0755, true)){
                        throw new \Symfony\Component\HttpFoundation\File\Exception\FileException(sprintf("could not create dir " . $dest));
                    }
                }
                $dest .= $file->getFileName();
                if (!file_exists($dest)) {
                    if(!copy($file->getRealPath(), $dest)){
                        throw new \Symfony\Component\HttpFoundation\File\Exception\FileException(sprintf("could not copy file " . $dest));
                    }
                }
            }
            $progress->advance();
        }

        $progress->finish();
        $output->writeln(
            "\ndone!"
        );
//         $kernel = $this->getContainer()->get('kernel');
//         $path = $kernel->locateResource(
//             '@TheaterjobsMainBundle/DataFixtures/SQL/partner.csv'
//         );
//         $em = $this->getContainer()->get('doctrine')->getManager();
    }
}
