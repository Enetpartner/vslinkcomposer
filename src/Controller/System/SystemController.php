<?php
namespace App\Controller\System;

use App\Entity\Version;
use App\Service\AppService;
use App\Service\System\CronTabService;
use App\Service\System\Fail2banService;
use App\Service\System\DirectoryService;
use App\Service\System\SystemInfoService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;

class SystemController extends AbstractController
{
    private $em;

    public function __construct( private AppService $appService, ManagerRegistry $entityManager, private Security $security)
    {
        $this->em = $entityManager->getManager();
    }    

    #[Route('admin/backup/list', name: "admin_backup_list")]
    public function backuplist(DirectoryService $directoryService)
    {
        if (!$this->security->isGranted('ROLE_ADMINISTRATEUR')) {
            throw $this->createAccessDeniedException('Accès limité.');
        }
        
        $files_backup = null;
        $folder_system = $this->appService->getParameter('repertoire_projet');
        
        if($folder_system)
        {
            $files_backup = $directoryService->listDirectoryContents($folder_system.'/backup');
        }
         
        return $this->render('System/backup_list.html.twig', ['files' => $files_backup, 'controller_name' => 'admin_backup_list']);  
    }

    #[Route('admin/log/list', name: "admin_log_list")]
    public function loglist(DirectoryService $directoryService)
    {
        if (!$this->security->isGranted('ROLE_ADMINISTRATEUR')) {
            throw $this->createAccessDeniedException('Accès limité.');
        }
        $files_log = null;
        $folder_system = $this->appService->getParameter('repertoire_projet');
        
        if($folder_system)
        {
            $files_log = $directoryService->listDirectoryContents($folder_system.'/var/log');
        }
         
        return $this->render('System/log_list.html.twig', ['files' => $files_log, 'controller_name' => 'admin_log_list']);  
    }

    #[Route('admin/crontab/list', name: "admin_crontab_list")]
    public function crontabList(CronTabService $cronTabService)
    {
        if (!$this->security->isGranted('ROLE_ADMINISTRATEUR')) {
            throw $this->createAccessDeniedException('Accès limité.');
        }

        $folder_system = $this->appService->getParameter('repertoire_projet');
        try {
            $cronJobs = $cronTabService->getCronTab($folder_system);
        } catch (\Exception $e) {
            return new Response('Erreur: ' . $e->getMessage());
        }

        return $this->render('System/crontab_list.html.twig', [
            'cronJobs' => $cronJobs,
            'controller_name' => 'admin_crontab_list'
        ]);  
    }

    #[Route('admin/system/info', name: "admin_system_info")]
    public function systemInfo(SystemInfoService $systemInfoService, Fail2banService $fail2banService)
    {
        if (!$this->security->isGranted('ROLE_ADMINISTRATEUR')) {
            throw $this->createAccessDeniedException('Accès limité.');
        }
        return $this->render('System/system_info.html.twig', [
            'os_version' => $systemInfoService->getOsVersion(),
            'disk_usage' => $systemInfoService->getDiskUsage(),
            'memory_usage' => $systemInfoService->getMemoryUsage(),
            'last_update' => $systemInfoService->getLastUpdateDate(),
            'fail2ban' => $fail2banService->getFail2banActive(),
            'controller_name' => 'admin_system_info'
        ]);
    }

    #[Route('admin/version/list', name: "admin_version_list")]
    public function versionList()
    {
        if (!$this->security->isGranted('ROLE_ADMINISTRATEUR')) {
            throw $this->createAccessDeniedException('Accès limité.');
        }
        $versions = $this->em->getRepository(Version::class)->findAllBy();
        return $this->render('System/version.html.twig', [ 'versions' => $versions, 'controller_name' => 'admin_version_list']);
    }


}