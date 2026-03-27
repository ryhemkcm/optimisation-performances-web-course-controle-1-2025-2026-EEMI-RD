<?php

namespace App\Controller;

use App\Repository\DirectusFilesRepository;
use App\Repository\GalaxyRepository;
use App\Repository\ModelesFilesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CarouselController extends AbstractController
{
    #[Route('/carousel', name: 'app_carousel')]
    public function index(
        GalaxyRepository $galaxyRepository,
        ModelesFilesRepository $modelesFilesRepository,
        DirectusFilesRepository $directusFilesRepository
    ): Response
    {
        $galaxies = $galaxyRepository->findAll();
        $carousel = [];

        $modeleIds = [];
        foreach ($galaxies as $galaxy) {
            $modeleIds[] = $galaxy->getModele();
        }

        $modelesFiles = $modelesFilesRepository->findBy([
            'modeles_id' => $modeleIds
        ]);

        $filesByModele = [];
        $fileIds = [];

        foreach ($modelesFiles as $modelesFile) {
            $modeleId = $modelesFile->getModelesId();
            $fileId = $modelesFile->getDirectusFilesId();

            $filesByModele[$modeleId][] = $fileId;
            $fileIds[] = $fileId;
        }

        $files = $directusFilesRepository->findBy([
            'id' => $fileIds
        ]);

        $filesById = [];
        foreach ($files as $file) {
            $filesById[$file->getId()] = $file;
        }

        foreach ($galaxies as $galaxy) {
            $files = [];
            $modeleId = $galaxy->getModele();

            if (isset($filesByModele[$modeleId])) {
                foreach ($filesByModele[$modeleId] as $fileId) {
                    if (isset($filesById[$fileId])) {
                        $files[] = $filesById[$fileId];
                    }
                }
            }

            $carousel[] = [
                'title' => $galaxy->getTitle(),
                'description' => $galaxy->getDescription(),
                'files' => $files
            ];
        }

        return $this->render('carousel/index.html.twig', [
            'carousel' => $carousel
        ]);
    }
}