<?php
namespace Enetpartner\Vslinkcomposer\Controller\System;

use Enetpartner\Vslinkcomposer\Entity\Version;
use Enetpartner\Vslinkcomposer\Service\AppService;
use Enetpartner\Vslinkcomposer\Service\System\CronTabService;
use Enetpartner\Vslinkcomposer\Service\System\Fail2banService;
use Enetpartner\Vslinkcomposer\Service\System\DirectoryService;
use Enetpartner\Vslinkcomposer\Service\System\SystemInfoService;
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

    #[Route('vslink/admin/backup/list', name: "vslink_admin_backup_list")]
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
         
        return $this->render('@VslinkcomposerBundle/Vslink/System/backup_list.html.twig', ['files' => $files_backup, 'controller_name' => 'vslink_admin_backup_list']);  
    }

    #[Route('vslink/admin/log/list', name: "vslink_admin_log_list")]
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
         
        return $this->render('@MonPackage/Vslink/System/log_list.html.twig', ['files' => $files_log, 'controller_name' => 'vslink_admin_log_list']);  
    }

    #[Route('vslink/admin/crontab/list', name: "vslink_admin_crontab_list")]
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

        return $this->render('Vslink/System/crontab_list.html.twig', [
            'cronJobs' => $cronJobs,
            'controller_name' => 'vslink_admin_crontab_list'
        ]);  
    }

    #[Route('vslink/admin/system/info', name: "vslink_admin_system_info")]
    public function systemInfo(SystemInfoService $systemInfoService, Fail2banService $fail2banService)
    {
        if (!$this->security->isGranted('ROLE_ADMINISTRATEUR')) {
            throw $this->createAccessDeniedException('Accès limité.');
        }
        return $this->render('Vslink/System/system_info.html.twig', [
            'os_version' => $systemInfoService->getOsVersion(),
            'disk_usage' => $systemInfoService->getDiskUsage(),
            'memory_usage' => $systemInfoService->getMemoryUsage(),
            'last_update' => $systemInfoService->getLastUpdateDate(),
            'fail2ban' => $fail2banService->getFail2banActive(),
            'controller_name' => 'vslink_admin_system_info'
        ]);
    }

    #[Route('vslink/admin/version/list', name: "vslink_admin_version_list")]
    public function versionList()
    {
        if (!$this->security->isGranted('ROLE_ADMINISTRATEUR')) {
            throw $this->createAccessDeniedException('Accès limité.');
        }
        $versions = $this->em->getRepository(Version::class)->findAllBy();
        return $this->render('Vslink/System/version.html.twig', [ 'versions' => $versions, 'controller_name' => 'vslink_admin_version_list']);
    }


}