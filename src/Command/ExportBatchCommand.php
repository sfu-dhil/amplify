<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Command;

use App\Entity\Episode;
use App\Repository\SeasonRepository;
use DOMDocument;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\Pdf;
use Soundasleep\Html2Text;
use Soundasleep\Html2TextException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ExportBatchCommand extends Command {
    private ?SeasonRepository $repository = null;

    private ?Environment $twig = null;

    protected static $defaultName = 'app:export:batch';

    protected static string $defaultDescription = 'Export a season of a podcast for an islandora batch import.';

    protected function configure() : void {
        $this->setDescription(self::$defaultDescription);
        $this->addArgument('seasonId', InputArgument::REQUIRED, 'Season database ID');
        $this->addArgument('directory', InputArgument::REQUIRED, 'Directory to export to');
    }

    /**
     * @param Audio|Episode|Image|Pdf $object
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function generateMods(string $type, $object, string $destination, Episode $episode) : void {
        $mods = $this->twig->render("export/{$type}.xml.twig", [
            'object' => $object,
            'episode' => $episode,
        ]);
        file_put_contents($destination, $mods);
    }

    /**
     * @throws RuntimeError
     * @throws LoaderError
     * @throws SyntaxError
     * @throws Html2TextException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $season = $this->repository->find($input->getArgument('seasonId'));
        if ( ! $season) {
            $output->writeln('Cannot find season ' . $input->getArgument('seasonId'));

            return 1;
        }

        $dir = $input->getArgument('directory');
        $fs = new Filesystem();

        if ($fs->exists($dir)) {
            $output->writeln("Warning: export directory {$dir} already exists.");
            $fs->remove($dir);
        }
        $fs->mkdir($dir, 0755);

        foreach ($season->getEpisodes() as $episode) {
            $slug = $episode->getSlug();
            $path = "{$dir}/{$slug}";

            $fs->mkdir($path, 0755);
            $this->generateMods('parent', $episode, "{$path}/MODS.xml", $episode);

            $obj = $episode->getAudio('audio/x-wav');
            if ( ! $obj) {
                $obj = $episode->getAudio('audio/mpeg');
            }
            $mp3 = $episode->getAudio('audio/mpeg');

            $fs->mkdir("{$path}/audio", 0755);
            $this->generateMods('audio', $obj, "{$path}/audio/MODS.xml", $episode);

            $fs->copy($obj->getFile()->getRealPath(), "{$path}/audio/OBJ." . $obj->getExtension());
            if ($mp3 && $mp3 !== $obj) {
                $fs->copy($mp3->getFile()->getRealPath(), "{$path}/audio/PROXY_MP3." . $obj->getExtension());
            }

            if (count($episode->getPdfs()) > 0) {
                $pdf = $episode->getPdfs()[0];
                $fs->mkdir("{$path}/transcript", 0755);
                $this->generateMods('transcript', $pdf, "{$path}/transcript/MODS.xml", $episode);
                $fs->copy($pdf->getFile()->getRealPath(), "{$path}/transcript/OBJ.pdf");
                if ($episode->getTranscript()) {
                    $text = Html2Text::convert($episode->getTranscript());
                    $fs->dumpFile("{$path}/transcript/FULL_TEXT.txt", wordwrap($text));
                }
                foreach (array_slice($episode->getPdfs(), 1) as $n => $extra) {
                    $fs->mkdir("{$path}/transcript_{$n}", 0755);
                    $this->generateMods('transcript', $extra, "{$path}/transcript_{$n}/MODS.xml", $episode);
                    $fs->copy($pdf->getFile()->getRealPath(), "{$path}/transcript_{$n}/OBJ.pdf");
                }
            }

            $images = array_merge($episode->getImages(), $episode->getSeason()->getImages(), $episode->getPodcast()->getImages());
            if (count($images)) {
                $tn = $images[0];
                $fs->copy($tn->getFile()->getRealPath(), "{$path}/TN." . $tn->getfile()->getExtension());

                foreach ($images as $n => $image) {
                    $subdir = "{$path}/img_{$n}";
                    $fs->mkdir($subdir, 0755);
                    $this->generateMods('image', $image, "{$subdir}/MODS.xml", $episode);
                    $fs->copy($image->getFile()->getRealPath(), "{$subdir}/OBJ." . $image->getExtension());
                }
            }

            $this->generateStructure($episode, "{$path}/structure.xml");
        }

        return 0;
    }

    public function generateStructure(Episode $episode, string $destination) : void {
        $slug = $episode->getSlug();

        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= "<islandora_compound_object title='{$slug}'>";
        $xml .= "<child content='{$slug}/audio'/>";

        $images = array_merge($episode->getImages(), $episode->getSeason()->getImages(), $episode->getPodcast()->getImages());
        foreach ($images as $n => $image) {
            $xml .= "<child content='{$slug}/img_{$n}'/>";
        }

        foreach($episode->getPdfs() as $n => $pdf) {
            $dir = "transcript" . ($n > 0 ? "_{$n}" : "");
            $xml .= "<child content='{$slug}/{$dir}'/>";
        }
        $xml .= '</islandora_compound_object>';

        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($xml);
        $doc->save($destination);
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
}
