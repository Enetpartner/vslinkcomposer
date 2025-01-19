<?php
namespace Enetpartner\Vslinkcomposer\Service\System;

class SystemInfoService {
    
    private string $updateLogFile = '/var/lib/apt/periodic/upgrade-stamp';

    /**
     * Récupère la date de la dernière mise à jour apt.
     *
     * @return string|null Retourne la date formatée ou null si le fichier n'existe pas.
     */
    public function getLastUpdateDate(): ?string
    {
        $osFamily = PHP_OS_FAMILY;

        if ($osFamily == "Linux")
        {
            if (file_exists($this->updateLogFile)) {
                $lastUpdate = filemtime($this->updateLogFile);
                return date('Y-m-d H:i:s', $lastUpdate);
            }
        }

        return 'Non disponible';
    }

     /**
     * Récupère la version de l'OS (Debian).
     */
    public function getOsVersion(): string
    {
       // return shell_exec('cat /etc/debian_version') ?: 'Version inconnue';
       
        $osFamily = PHP_OS_FAMILY;
        
        switch ($osFamily) {
            case 'Linux':
                $version = shell_exec('cat /etc/debian_version') ?: 'Version inconnue';
                return 'Debian '.$version;
            case 'Darwin': // macOS
                $version = shell_exec('sw_vers -productVersion') ?: 'Version inconnue';
                return 'macOS '.$version;
            case 'Windows':
                $version = shell_exec('ver') ?: 'Version inconnue';
                return 'Windows '.$version;
            default:
                return 'Environnement non supporté';
        }

    }

    public function getMemoryUsage(): array
    {
        $osFamily = PHP_OS_FAMILY;
        
        switch ($osFamily) {
            case 'Linux':
                return $this->getLinuxMemoryUsage();
            case 'Darwin':
                return $this->getMacMemoryUsage();
            case 'Windows':
                return ['total' => 'Non supporté', 'free' => 'Non supporté', 'used' => 'Non supporté'];
            default:
                return ['total' => 'Inconnu', 'free' => 'Inconnu', 'used' => 'Inconnu'];
        }
    }

    public function getDiskUsage(): array
    {
        $osFamily = PHP_OS_FAMILY;
        
        switch ($osFamily) {
            case 'Linux':
                return $this->getLinuxDiskUsage();
            case 'Darwin':
                return $this->getMacDiskUsage();
            case 'Windows':
                return ['total' => 'Non supporté', 'free' => 'Non supporté', 'used' => 'Non supporté'];
            default:
                return ['total' => 'Inconnu', 'free' => 'Inconnu', 'used' => 'Inconnu'];
        }
    }


    /**
     * Récupère l'espace disque disponible et total.
     */
    public function getLinuxDiskUsage(): array
    {
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');
        $diskUsed = $diskTotal - $diskFree;
        return [
            'total' => $this->formatBytes($diskTotal),
            'free' => $this->formatBytes($diskFree),
            'used' => $this->formatBytes($diskUsed),
        ];
    }

    public function getMacDiskUsage(): array
    {
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');

        return [
            'total' => $this->formatBytes($diskTotal),
            'free' => $this->formatBytes($diskFree),
            'used' => $this->formatBytes($diskTotal - $diskFree),
        ];
    }

    /**
     * Récupère l'utilisation de la mémoire.
     */
    public function getLinuxMemoryUsage(): array
    {
        $memoryInfo = file_get_contents('/proc/meminfo');
        if ($memoryInfo === false) {
            return ['total' => 'Inconnu', 'free' => 'Inconnu'];
        }

        $data = [];
        foreach (explode("\n", $memoryInfo) as $line) {
            if (preg_match('/^MemTotal:\s+(\d+)\s+kB$/', $line, $matches)) {
                $total = $matches[1] * 1024;
                $data['total'] = $this->formatBytes($matches[1] * 1024);
            }
            if (preg_match('/^MemFree:\s+(\d+)\s+kB$/', $line, $matches)) {
                $free = $matches[1] * 1024;
                $data['free'] = $this->formatBytes($matches[1] * 1024);
            }
        }
        $data_used = $total - $free;
        $data['used'] = $this->formatBytes($data_used);

        return $data;
    }

     /**
     * Récupère l'utilisation de la mémoire.
     */
    public function getMacMemoryUsage(): array
    {
        $memoryInfo = shell_exec('vm_stat');
        if ($memoryInfo === false) {
            return ['total' => 'Inconnu', 'free' => 'Inconnu', 'used' => 'Inconnu'];
        }

        preg_match('/Pages free:\s+(\d+)\./', $memoryInfo, $matchesFree);
        preg_match('/Pages active:\s+(\d+)\./', $memoryInfo, $matchesActive);
        preg_match('/Pages inactive:\s+(\d+)\./', $memoryInfo, $matchesInactive);
        preg_match('/Pages speculative:\s+(\d+)\./', $memoryInfo, $matchesSpeculative);
        preg_match('/Pages wired down:\s+(\d+)\./', $memoryInfo, $matchesWired);
        preg_match('/Pages occupied by compressor:\s+(\d+)\./', $memoryInfo, $matchesCompressed);

        $pageSize = 4096; // Taille de page en octets sur macOS
        $totalPages = shell_exec("sysctl -n hw.memsize") / $pageSize;
        $freePages = ($matchesFree[1] ?? 0) + ($matchesSpeculative[1] ?? 0);
        $usedPages = $totalPages - $freePages;

        return [
            'total' => $this->formatBytes($totalPages * $pageSize),
            'free' => $this->formatBytes($freePages * $pageSize),
            'used' => $this->formatBytes($usedPages * $pageSize),
        ];
    }

    /**
     * Formate les octets en une unité lisible.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        $units = ['KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));

        return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i - 1];
    }
}