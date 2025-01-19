<?php
namespace Enetpartner\Vslinkcomposer\Service\System;
use Symfony\Component\Finder\Finder;

class DirectoryService {
    
    public function listDirectoryContents(string $directory): array
    {
        $fileList = [];
        $finder = new Finder();
        if (is_dir($directory)) {
            $finder->files()->in($directory);
            foreach ($finder as $file) {
                $fileList[] = [
                    'name' => $file->getRelativePathname(), // Nom du fichier avec chemin relatif
                    'size' => $file->getSize(), // Taille du fichier en octets
                    'modified_at' => $file->getMTime(), // Timestamp de la dernière modification
                    'formatted_date' => $file->getMTime() ? date('Y-m-d H:i:s', $file->getMTime()) : '',
                ];
            }

            usort($fileList, function ($a, $b) {
                return $b['modified_at'] <=> $a['modified_at'];
            });
        }
        return $fileList;
    }
}