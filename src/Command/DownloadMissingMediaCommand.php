<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\PodcastRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'app:media:missing:download')]
class DownloadMissingMediaCommand extends Command {
    public function __construct(
        private EntityManagerInterface $em,
        private PodcastRepository $podcastRepository,
        private ParameterBagInterface $parameterBagInterface,
        private Filesystem $filesystem,
        private Client $client,
        private ?OutputInterface $output = null,
    ) {
        parent::__construct();
    }

    private function downloadFile(string $url, string $path) : void {
        try {
            $this->client->request('GET', $url, [
                'sink' => $path,
                'headers' => [
                    'Accept-Encoding' => 'gzip, deflate, br',
                ],
            ]);
            $this->output->writeln("Successfully downloaded: {$url} to {$path}");
        } catch (RequestException $e) {
            $this->output->writeln("Failed to downloaded: {$url} to {$path}");
            if ($e->hasResponse()) {
                $this->output->writeln("HTTP Status Code: {$e->getResponse()->getStatusCode()}");
            }
            $this->output->writeln("Error: {$e->getMessage()}");
            // cleanup back files
            if ($this->filesystem->exists($path)) {
                $this->filesystem->remove($path);
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $this->output = $output;
        $audioDirPath = $this->parameterBagInterface->get('nines_media_audio_dir');
        $imageDirPath = $this->parameterBagInterface->get('nines_media_image_dir');
        $pdfDirPath = $this->parameterBagInterface->get('nines_media_pdf_dir');

        $podcasts = $this->podcastRepository->findAll();

        foreach ($podcasts as $podcast) {
            foreach ($podcast->getImages() as $image) {
                if ( ! $image->getFile() || ! $this->filesystem->exists($image->getFile()->getRealPath())) {
                    $this->output->writeln("Image Missing: Podcast {$podcast->getId()} Image {$image->getId()} {$image->getPath()}");
                    if ($image->getSourceUrl()) {
                        $this->downloadFile($image->getSourceUrl(), "{$imageDirPath}/{$image->getPath()}");
                    } else {
                        $this->output->writeln('Skipped: No Source Url for image');
                    }
                }
            }
            foreach ($podcast->getSeasons() as $season) {
                foreach ($season->getImages() as $image) {
                    if ( ! $image->getFile() || ! $this->filesystem->exists($image->getFile()->getRealPath())) {
                        $this->output->writeln("Image Missing: Podcast {$podcast->getId()} Season {$season->getId()} Image {$image->getId()} {$image->getPath()}");
                        if ($image->getSourceUrl()) {
                            $this->downloadFile($image->getSourceUrl(), "{$imageDirPath}/{$image->getPath()}");
                        } else {
                            $this->output->writeln('Skipped: No Source Url for image');
                        }
                    }
                }
            }
            foreach ($podcast->getEpisodes() as $episode) {
                foreach ($episode->getImages() as $image) {
                    if ( ! $image->getFile() || ! $this->filesystem->exists($image->getFile()->getRealPath())) {
                        $this->output->writeln("Image Missing: Podcast {$podcast->getId()} Episode {$episode->getId()} Image {$image->getId()} {$image->getPath()}");
                        if ($image->getSourceUrl()) {
                            $this->downloadFile($image->getSourceUrl(), "{$imageDirPath}/{$image->getPath()}");
                        } else {
                            $this->output->writeln('Skipped: No Source Url for image');
                        }
                    }
                }
                foreach ($episode->getPdfs() as $pdf) {
                    if ( ! $pdf->getFile() || ! $this->filesystem->exists($pdf->getFile()->getRealPath())) {
                        $this->output->writeln("PDF Missing: Podcast {$podcast->getId()} Episode {$episode->getId()} PDF {$pdf->getId()} {$pdf->getPath()}");
                        if ($pdf->getSourceUrl()) {
                            $this->downloadFile($pdf->getSourceUrl(), "{$pdfDirPath}/{$pdf->getPath()}");
                        } else {
                            $this->output->writeln('Skipped: No Source Url for pdf');
                        }
                    }
                }
                foreach ($episode->getAudios() as $audio) {
                    if ( ! $audio->getFile() || ! $this->filesystem->exists($audio->getFile()->getRealPath())) {
                        $this->output->writeln("Audio Missing: Podcast {$podcast->getId()} Episode {$episode->getId()} Audio {$audio->getId()} {$audio->getPath()}");
                        if ($audio->getSourceUrl()) {
                            $this->downloadFile($audio->getSourceUrl(), "{$audioDirPath}/{$audio->getPath()}");
                        } else {
                            $this->output->writeln('Skipped: No Source Url for audio');
                        }
                    }
                }
            }
        }

        return 1;
    }
}
