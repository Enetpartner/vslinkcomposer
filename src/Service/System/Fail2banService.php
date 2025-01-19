<?php

namespace Enetpartner\Vslinkcomposer\Service\System;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Fail2banService
{
    /**
     * Vérifie si Fail2ban est actif
     *
     * @return string
     */
    public function getFail2banActive(): string
    {
        $osFamily = PHP_OS_FAMILY;

        if ($osFamily == "Linux")
        {
            $process = new Process(['sudo','fail2ban-client', 'ping']);
            $process->run();

            if (!$process->isSuccessful()) {
                return 'Non actif';
            }

            $output = trim($process->getOutput());
            if($output === 'Server replied: pong')
            {
                return 'Actif';
            }
            return 'Pas de réponse';
        }

        return 'Indisponible';
    }
}