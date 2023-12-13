<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Export;
use App\Entity\Podcast;
use App\Entity\Season;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Twig\Environment;
use ZipArchive;

class ExportService {
    public function __construct(
        protected EntityManagerInterface $em,
        protected Filesystem $filesystem,
        protected Environment $twig,
        protected ParameterBagInterface $parameterBagInterface,
        protected HtmlSanitizerInterface $exportContentSanitizer,
        protected ?OutputInterface $output = null,
        protected ?Podcast $podcast = null,
        protected ?Export $export = null,
        protected ?string $exportTmpRootDir = null,
        protected ?string $zipFilePath = null,
        protected int $totalEpisodes = 0,
        protected int $totalSteps = 1,
        protected int $stepsCompleted = 0,
    ) {}

    protected function getContributorPersonAndRoles(array $allContributions) : array {
        $contributions = [];
        foreach ($allContributions as $contribution) {
            $person = $contribution->getPerson();
            if ( ! array_key_exists($person->getId(), $contributions)) {
                $contributions[$person->getId()] = [
                    'person' => $person,
                    'roles' => [],
                ];
            }
            $contributions[$person->getId()]['roles'] = array_merge($contribution->getRoles(), $contributions[$person->getId()]['roles']);
        }

        return $contributions;
    }

    protected function getPodcastContributorPersonAndRoles(Podcast $podcast) : array {
        $allContributions = $podcast->getContributions()->toArray();

        return $this->getContributorPersonAndRoles($allContributions);
    }

    protected function getSeasonContributorPersonAndRoles(Season $season) : array {
        $allContributions = array_merge(
            $season->getContributions()->toArray(),
            $season->getPodcast()->getContributions()->toArray(),
        );

        return $this->getContributorPersonAndRoles($allContributions);
    }

    protected function getEpisodeContributorPersonAndRoles(Episode $episode) : array {
        $allContributions = array_merge(
            $episode->getContributions()->toArray(),
            $episode->getSeason()->getContributions()->toArray(),
            $episode->getPodcast()->getContributions()->toArray(),
        );

        return $this->getContributorPersonAndRoles($allContributions);
    }

    protected function updateMessage(string $message) : void {
        $this->output->writeln($message);
        $this->export->setMessage($message);
        $this->em->persist($this->export);
        $this->em->flush();
    }

    protected function updateProgress(int $step) : void {
        $this->export->setProgress((int) ($step * 100 / $this->totalSteps));
        $this->em->persist($this->export);
        $this->em->flush();
    }

    protected function prepare() : void {
        $this->stepsCompleted = 0;
        $this->totalEpisodes = 0;
        foreach ($this->podcast->getSeasons() as $season) {
            $this->totalEpisodes += count($season->getEpisodes());
        }
        $this->totalSteps = (int) ($this->totalEpisodes * 2) + 10;

        $this->exportTmpRootDir = sys_get_temp_dir() . "/exports/{$this->export->getId()}";
        // remove folder if already exists
        if ($this->filesystem->exists($this->exportTmpRootDir)) {
            $this->filesystem->remove($this->exportTmpRootDir);
        }
        $this->filesystem->mkdir($this->exportTmpRootDir, 0o777);
        $this->zipFilePath = "{$this->exportTmpRootDir}.zip";
    }

    protected function generate() : void {
        throw new Exception('override generateExport function');
    }

    protected function zip() : void {
        // zip step
        $this->updateMessage('Compressing export files.');
        $zip = new ZipArchive();
        if ( ! $zip->open($this->zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new Exception('There was a problem creating the zip file');
        }

        $finder = new Finder();
        $finder->files()->in("{$this->exportTmpRootDir}/");
        $currentFile = 0;
        foreach ($finder as $file) {
            $currentFile++;
            $zip->addFile($file->getRealpath(), $file->getRelativePathname());
            $zip->setCompressionName('bar.jpg', ZipArchive::CM_DEFLATE, 9);
        }
        $zip->registerProgressCallback(0.01, function ($r) : void {
            // we don't know how many files there are beforehand so we approximate the increase by
            // file completion fraction multiplied by the total episodes (why we do *2 episodes steps previously)
            $percent = (int) ($r * 100);
            $this->updateMessage("Compressing export files ({$percent}%)");
            $tempCurrentStep = $this->stepsCompleted + ($r * $this->totalEpisodes);
            $this->updateProgress((int) $tempCurrentStep);
        });
        if ( ! $zip->close()) {
            throw new Exception('There was a problem saving the zip file');
        }
        $this->updateProgress($this->stepsCompleted += $this->totalEpisodes);
    }

    protected function move() : void {
        // move zip file to project folder and update export
        $this->updateMessage('Preparing export for download.');
        $relativePath = "{$this->export->getId()}.zip";
        $this->filesystem->mkdir($this->parameterBagInterface->get('export_root_dir'), 0o777);
        $appExportFilePath = $this->parameterBagInterface->get('export_root_dir') . "/{$relativePath}";
        $this->filesystem->rename($this->zipFilePath, $appExportFilePath, true);

        $this->export->setPath($relativePath);
        $this->em->persist($this->export);
        $this->em->flush();
        $this->updateProgress($this->stepsCompleted += 5);
    }

    protected function cleanup() : void {
        // cleanup step
        $this->updateMessage('Cleaning up temporary system files.');
        $this->filesystem->remove($this->exportTmpRootDir);
        $this->updateProgress($this->stepsCompleted += 5);
    }

    public function exportPodcast(OutputInterface $output, Podcast $podcast, Export $export) : void {
        $this->output = $output;
        $this->podcast = $podcast;
        $this->export = $export;

        $this->prepare();
        $this->generate(); // this should be overridden
        $this->zip();
        $this->move();
        $this->cleanup();
    }
}
