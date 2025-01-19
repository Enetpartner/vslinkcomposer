<?php
/* 
 * (c) Luc Petitprez <luc@vscloud.fr>
 * Date: 2022-11-10 15:23:07 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Enetpartner\Vslinkcomposer\Entity;

use Doctrine\ORM\Mapping as ORM;

use Enetpartner\Vslinkcomposer\Repository\VersionRepository;
use Enetpartner\Vslinkcomposer\Entity\BaseAuditableEntity;

#[ORM\Table(name: 'vslink_version')]
#[ORM\Entity(repositoryClass: VersionRepository::class)]
class Version  extends BaseAuditableEntity
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(name: 'version', type: 'string', length: 255)]
    private $version;

 
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function getRelease(): ?string
    {
        $date = $this->getCreatedAt()->format('YmdHi');
        $release_number = (string) $this->getId();
        $release = $this->getVersion().'.'.$date.'.'.$release_number;
        return $release;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

  
}