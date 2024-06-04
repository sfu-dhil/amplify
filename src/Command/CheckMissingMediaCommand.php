<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\PodcastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'app:media:missing')]
class CheckMissingMediaCommand extends Command {
    public function __construct(
        private EntityManagerInterface $em,
        private PodcastRepository $podcastRepository,
        private Filesystem $filesystem,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $podcasts = $this->podcastRepository->findAll();

        foreach ($podcasts as $podcast) {
            foreach ($podcast->getImages() as $image) {
                if ( ! $image->getFile() || ! $this->filesystem->exists($image->getFile()->getRealPath())) {
                    $output->writeln("Image Missing: Podcast {$podcast->getId()} Image {$image->getId()} {$image->getPath()}");
                }
            }
            foreach ($podcast->getSeasons() as $season) {
                foreach ($season->getImages() as $image) {
                    if ( ! $image->getFile() || ! $this->filesystem->exists($image->getFile()->getRealPath())) {
                        $output->writeln("Image Missing: Podcast {$podcast->getId()} Season {$season->getId()} Image {$image->getId()} {$image->getPath()}");
                    }
                }
            }
            foreach ($podcast->getEpisodes() as $episode) {
                foreach ($episode->getImages() as $image) {
                    if ( ! $image->getFile() || ! $this->filesystem->exists($image->getFile()->getRealPath())) {
                        $output->writeln("Image Missing: Podcast {$podcast->getId()} Episode {$episode->getId()} Image {$image->getId()} {$image->getPath()}");
                    }
                }
                foreach ($episode->getPdfs() as $pdf) {
                    if ( ! $pdf->getFile() || ! $this->filesystem->exists($pdf->getFile()->getRealPath())) {
                        $output->writeln("PDF Missing: Podcast {$podcast->getId()} Episode {$episode->getId()} PDF {$pdf->getId()} {$pdf->getPath()}");
                    }
                }
                foreach ($episode->getAudios() as $audio) {
                    if ( ! $audio->getFile() || ! $this->filesystem->exists($audio->getFile()->getRealPath())) {
                        $output->writeln("Audio Missing: Podcast {$podcast->getId()} Episode {$episode->getId()} Audio {$audio->getId()} {$audio->getPath()}");
                    }
                }
            }
        }

        return 1;
    }
}
