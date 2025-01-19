<?php
namespace Enetpartner\Vslinkcomposer\Service\System;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CronTabService
{
    public function getCronTab($directory_system)
    {
        $process = new Process(['crontab', '-l']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Séparer la sortie en lignes
        $lines = explode("\n", $process->getOutput());

        // On filtre les lignes vides et on les structure
        $cronJobs = [];
        foreach ($lines as $line) {
            // Ignorer les lignes vides ou les commentaires
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            // Découper la ligne en colonnes pour les champs (minute, heure, jour, etc.)
            $fields = preg_split('/\s+/', $line);
            if (isset($fields[5]) && strpos($fields[5], $directory_system) === 0) {
                $cronJobs[] = $fields;
            }
        }

        return $cronJobs;
    }
}