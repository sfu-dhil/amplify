<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Command;

use App\Entity\Episode;
use App\Repository\SeasonRepository;
use DOMDocument;
use Nines\MediaBundle\Service\AudioManager;
use Soundasleep\Html2Text;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class ExportBatchCommand extends Command {
    private SeasonRepository $repository;

    private Environment $twig;

    private AudioManager $audioManager;

    protected static $defaultName = 'app:export:batch';

    protected static $defaultDescription = 'Export a season of a podcast for an islandora batch import.';

    protected function configure() : void {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('seasonId', InputArgument::REQUIRED, 'Season database ID')
            ->addArgument('directory', InputArgument::REQUIRED, 'Directory to export to')
        ;
    }

    protected function generateMods(Episode $episode) {
        $mods = $this->twig->render('episode/mods.xml.twig', ['episode' => $episode]);
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($mods);

        return $doc;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $io = new SymfonyStyle($input, $output);
        $season = $this->repository->find($input->getArgument('seasonId'));
        if ( ! $season) {
            $io->writeln('Cannot find season ' . $input->getArgument('seasonId'));

            return 1;
        }
        $dir = $input->getArgument('directory');
        $fs = new Filesystem();

        if ($fs->exists($dir)) {
            $output->writeln("Warning: export directory {$dir} already exists.");
            $fs->remove($dir);
        }
        $fs->mkdir($dir, 0777);

        foreach ($season->getEpisodes() as $episode) {
            $slug = $episode->getSlug();
            $path = "{$dir}/{$slug}";
            $fs->mkdir($path, 0777);
            $mods = $this->generateMods($episode);
            $mods->save("{$path}/MODS.xml");

            if ($episode->getTranscript()) {
                $text = Html2Text::convert($episode->getTranscript());
                $fs->dumpFile("{$path}/FULL_TEXT.txt", wordwrap($text));
            }

            $obj = $episode->getAudio('audio/x-wav');
            if ( ! $obj) {
                $obj = $episode->getAudio('audio/mpeg');
            }
            $mp3 = $episode->getAudio('audio/mpeg');

            $fs->copy($obj->getFile(), "{$path}/OBJ." . $obj->getExtension());
            if ($mp3 && $mp3 !== $obj) {
                $fs->copy($mp3->getFile(), "{$path}/PROXY_MP3." . $obj->getExtension());
            }
            $images = array_merge($episode->getImages(), $episode->getSeason()->getImages(), $episode->getPodcast()->getImages());
            if (count($images)) {
                $thumb = array_shift($images);
                $fs->copy($thumb->getImageFile()->getRealPath(), "{$path}/TN.{$thumb->getExtension()}");
                foreach ($images as $n => $image) {
                    $fs->copy($image->getImageFile()->getRealPath(), "{$path}/IMG_" . ($n + 1) . '.' . $image->getExtension());
                }
            }
        }

        return 0;
    }

    /**
     * @required
     */
    public function setSeasonRepository(SeasonRepository $repository) : void {
        $this->repository = $repository;
    }

    /**
     * @required
     */
    public function setTwig(Environment $twig) : void {
        $this->twig = $twig;
    }

    /**
     * @required
     */
    public function setAudioManager(AudioManager $audioManager) : void {
        $this->audioManager = $audioManager;
    }
}
