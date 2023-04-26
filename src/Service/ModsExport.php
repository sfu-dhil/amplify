<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Export;
use App\Entity\Podcast;
use Doctrine\ORM\EntityManagerInterface;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\Pdf;
use Soundasleep\Html2Text;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use ZipArchive;

class ModsExport {
    public function __construct(
        private EntityManagerInterface $em,
        private Filesystem $filesystem,
        private Environment $twig,
        private ParameterBagInterface $parameterBagInterface,
        private ?OutputInterface $output = null,
        private ?Podcast $podcast = null,
        private ?Export $export = null,
        private ?int $totalSteps = null,
    ) {
    }

    private function generateEpisodeMod(Episode $episode, string $destinationDir) : void {
        $content = $this->twig->render('export/format/mods/episode.xml.twig', [
            'episode' => $episode,
        ]);
        $this->filesystem->dumpFile("{$destinationDir}/MODS.xml", $content);
    }

    private function generateAudioMod(Episode $episode, string $destinationDir, Audio $audio) : void {
        $content = $this->twig->render('export/format/mods/audio.xml.twig', [
            'episode' => $episode,
            'audio' => $audio,
        ]);
        $this->filesystem->dumpFile("{$destinationDir}/MODS.xml", $content);
    }

    private function generateTranscriptMod(Episode $episode, string $destinationDir, Pdf $pdf) : void {
        $content = $this->twig->render('export/format/mods/transcript.xml.twig', [
            'episode' => $episode,
            'pdf' => $pdf,
        ]);
        $this->filesystem->dumpFile("{$destinationDir}/MODS.xml", $content);
    }

    private function generateImageMod(Episode $episode, string $destinationDir, Image $image) : void {
        $content = $this->twig->render('export/format/mods/image.xml.twig', [
            'episode' => $episode,
            'image' => $image,
        ]);
        $this->filesystem->dumpFile("{$destinationDir}/MODS.xml", $content);
    }

    private function generateEpisodeStructure(Episode $episode, string $destinationDir) : void {
        $content = $this->twig->render('export/format/mods/episode_structure.xml.twig', [
            'episode' => $episode,
        ]);
        $this->filesystem->dumpFile("{$destinationDir}/structure.xml", $content);
    }

    private function updateMessage(string $message) : void {
        $this->output->writeln($message);
        $this->export->setMessage($message);
        $this->em->persist($this->export);
        $this->em->flush();
    }

    private function updateProgress(int $step) : void {
        $this->export->setProgress((int) ($step * 100 / $this->totalSteps));
        $this->em->persist($this->export);
        $this->em->flush();
    }

    public function exportPodcast(OutputInterface $output, Podcast $podcast, Export $export) : void {
        $this->output = $output;
        $this->podcast = $podcast;
        $this->export = $export;

        $exportTmpRootDir = sys_get_temp_dir() . "/exports/{$this->export->getId()}";
        // remove folder if already exists
        if ($this->filesystem->exists($exportTmpRootDir)) {
            $this->filesystem->remove($exportTmpRootDir);
        }
        $this->filesystem->mkdir($exportTmpRootDir);

        $totalEpisodeCount = 0;
        $currentEpisode = 0;
        foreach ($this->podcast->getSeasons() as $season) {
            $totalEpisodeCount += count($season->getEpisodes());
        }
        $this->totalSteps = ($totalEpisodeCount * 2) + 10;
        $stepsCompletedCount = 0;

        $this->updateMessage('Starting Mods export.');
        foreach ($this->podcast->getSeasons() as $season) {
            $seasonDir = "{$exportTmpRootDir}/{$season->getSlug()}";
            $this->filesystem->mkdir($seasonDir);

            foreach ($season->getEpisodes() as $episode) {
                $currentEpisode++;
                $this->updateMessage("Generating metadata for {$episode->getSlug()} ({$currentEpisode}/{$totalEpisodeCount})");

                $episodeDir = "{$seasonDir}/{$episode->getSlug()}";
                $this->filesystem->mkdir($episodeDir);

                // /structure.xml /MODS.xml
                $this->generateEpisodeStructure($episode, $episodeDir);
                $this->generateEpisodeMod($episode, $episodeDir);

                // /audio/MODS.xml (prefer audio/x-wav) /audio/OBJ.<ext> /audio/PROXY_MP3.<ext> (if mp3 exists)
                $audioMp3 = $episode->getAudio('audio/mpeg');
                $audioWav = $episode->getAudio('audio/x-wav');
                $audio = $audioWav ?? $audioMp3;
                if ($audio?->getFile()) {
                    $episodeAudioDir = "{$episodeDir}/audio";
                    $this->filesystem->mkdir($episodeAudioDir);

                    $this->generateAudioMod($episode, $episodeAudioDir, $audio);
                    $this->filesystem->copy($audio->getFile()->getRealPath(), "{$episodeAudioDir}/OBJ.{$audio->getExtension()}");
                    if ($audioMp3 && $audioMp3 !== $audio && $audioMp3?->getFile()) {
                        $this->filesystem->copy($audioMp3->getFile()->getRealPath(), "{$episodeAudioDir}/PROXY_MP3.{$audioMp3->getExtension()}");
                    }
                }

                // /transcript/MODS.xml /transcript/OBJ.pdf /transcript/FULL_TEXT.txt (if episode transcript available)
                // /transcript_<n>/MODS.xml /transcript_<n>/OBJ.pdf for pdfs after the first
                if (count($episode->getPdfs()) > 0) {
                    $episodeTranscriptDir = "{$episodeDir}/transcript";
                    $this->filesystem->mkdir($episodeTranscriptDir);

                    if ($episode->getTranscript()) {
                        $text = Html2Text::convert($episode->getTranscript());
                        $this->filesystem->dumpFile("{$episodeTranscriptDir}/FULL_TEXT.txt", wordwrap($text));
                    }
                    foreach ($episode->getPdfs() as $index => $pdf) {
                        if ($pdf?->getFile()) {
                            if (0 === $index) {
                                $destinationDir = $episodeTranscriptDir;
                            } else {
                                $n = $index - 1;
                                $destinationDir = "{$episodeTranscriptDir}_{$n}";
                            }
                            $this->generateTranscriptMod($episode, $destinationDir, $pdf);
                            $this->filesystem->copy($pdf->getFile()->getRealPath(), "{$destinationDir}/OBJ.pdf");
                        }
                    }
                }

                // /TN.<ext> /img_<n>/MODS.xml /img_<n>/OBJ.<ext>
                $images = array_merge($episode->getImages(), $season->getImages(), $podcast->getImages());
                if (count($images) > 0) {
                    $tn = $images[0];
                    if ($tn?->getFile()) {
                        $this->filesystem->copy($tn->getFile()->getRealPath(), "{$episodeDir}/TN.{$tn->getExtension()}");
                    }

                    foreach ($images as $index => $image) {
                        if ($image?->getFile()) {
                            $episodeImageDir = "{$episodeDir}/img_{$index}";
                            $this->filesystem->mkdir($episodeImageDir);

                            $this->generateImageMod($episode, $episodeImageDir, $image);
                            $this->filesystem->copy($image->getFile()->getRealPath(), "{$episodeImageDir}/OBJ.{$image->getExtension()}");
                        }
                    }
                }

                $this->updateProgress(++$stepsCompletedCount);
            }
        }

        // zip step
        $this->updateMessage('Preparing files for compression.');
        $zipFilePath = $this->filesystem->tempnam(sys_get_temp_dir(), "podcast_export_{$this->podcast->getId()}", '.zip');
        $zip = new ZipArchive();
        $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $finder = new Finder();
        $finder->files()->in($exportTmpRootDir);
        $currentFile = 0;
        foreach ($finder as $index => $file) {
            $currentFile++;
            $zip->addFile($file->getRealpath(), $file->getRelativePathname());
            $zip->setCompressionName('bar.jpg', ZipArchive::CM_DEFLATE, 9);
        }
        $zip->registerProgressCallback(0.01, function ($r) use ($stepsCompletedCount, $totalEpisodeCount) : void {
            // we don't know how many files there are beforehand so we approximate the increase by
            // file completion fraction multiplied by the total episodes (why we do *2 episodes steps previously)
            $percent = (int) ($r * 100);
            $this->updateMessage("Compressing files ({$percent}%).");
            $tempCurrentStep = $stepsCompletedCount + ($r * $totalEpisodeCount);
            $this->updateProgress((int) $tempCurrentStep);
        });
        $zip->close();
        $this->updateProgress($stepsCompletedCount += $totalEpisodeCount);

        // move zip file to project folder and update export
        $this->updateMessage('Preparing zip for download.');
        $relativePath = "{$this->export->getId()}.zip";
        $appExportFilePath = $this->parameterBagInterface->get('export_root_dir') . "/{$relativePath}";
        $this->filesystem->rename($zipFilePath, $appExportFilePath, true);

        $this->export->setPath($relativePath);
        $this->em->persist($this->export);
        $this->em->flush();

        $this->updateProgress($stepsCompletedCount += 5);

        // cleanup step
        $this->updateMessage('Cleaning up temporary files.');
        $this->filesystem->remove($zipFilePath);
        $this->filesystem->remove($exportTmpRootDir);
        $this->updateProgress($stepsCompletedCount += 5);
    }
}
