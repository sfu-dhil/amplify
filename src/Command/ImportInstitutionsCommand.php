<?php

namespace App\Command;

use App\Entity\Institution;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportInstitutionsCommand extends Command {
    protected static $defaultName = 'app:import:institutions';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct(null);
    }

    protected function configure() {
        $this->setDescription('Import data');
        $this->addArgument('file', InputArgument::REQUIRED, 'CSV file to import.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $file = $input->getArgument('file');

        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(0);
        foreach ($csv->getRecords() as $record) {
            $institution = new Institution();
            $institution->setProvince($record['Province']);
            $institution->setName($record['Institution']);
            $this->em->persist($institution);
        }
        $this->em->flush();

        return 0;
    }
}
