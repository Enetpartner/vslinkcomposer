<?php
/* 
 * (c) Luc Petitprez <luc@vscloud.fr>
 * Date: 2022-11-12 19:04:13 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Enetpartner\Vslinkcomposer\Entity;

use Doctrine\DBAL\Types\Types;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Gedmo\Mapping\Annotation as Gedmo;

use Doctrine\ORM\Mapping as Orm;

#[ORM\MappedSuperclass()]
#[ORM\HasLifecycleCallbacks()]
abstract class BaseAuditableEntity
{

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Gedmo\Blameable(on: 'create')]
    private $createdBy;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Gedmo\Blameable(on: 'update')]
    private $updatedBy;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'deleted_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $deletedBy;

    #[ORM\Column(name: 'created_at',type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'create')]
    private $createdAt;

    #[ORM\Column(name: 'updated_at',type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    private $updatedAt;

    #[ORM\Column(name: 'deleted_at',type: 'datetime', nullable: true)]
    private $deletedAt;

    #[ORM\Column(name: 'deleted', type : 'boolean',  nullable: false, options: [ 'default' => 0])]
    private $deleted;

    #[ORM\Column(name: 'archived', type : 'boolean',  nullable: false, options: [ 'default' => 0])]
    private $archived;
    
    /**
     * Utiliser comme detecteur de modification dans les listes
     * @var type boolean
     */
    private $update = false;

    public function __construct()
    {
        $this->deleted = 0;
    }
            
    public function getUpdate() {
        return $this->update;
    }
    
    public function setUpdate($boolean) {
        return $this->update = $boolean;
    }
    
    // Setters and getters here

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return BaseAuditableEntity
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return BaseAuditableEntity
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedBy
     *
     * @param App\Entity\User $updatedBy
     * @return BaseAuditableEntity
     */
    public function setUpdatedBy($updatedBy = null) :void
    {
        $this->updatedBy = $updatedBy;
    
        //return $this;
    }

    /**
     * Get updatedBy
     *
     * @return App\Entity\User 
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set deletedBy
     */
    public function setDeletedBy($deletedBy = null) :void
    {
        $this->deletedBy = $deletedBy;
    
        //return $this;
    }

    /**
     * Get deletedBy
     *
     * @return App\Entity\User 
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
    * @ORM\PrePersist
    */
    #[ORM\PrePersist]
    public function setCreatedValue()
    {
    $this->createdAt = new \DateTime();
    $this->updatedAt = new \DateTime();
    $this->deleted = 0;
    $this->archived = 0;
    }
    
    /**
    * @ORM\PreUpdate 
    */
    #[ORM\PreUpdate]
    public function setUpdatedValue()
    {
    $this->updatedAt = new \DateTime();
    }
   
    /**
     * Set createdBy
     *
     * @param App\Entity\User $createdBy
     * @return BaseAuditableEntity
     */
    public function setCreatedBy($createdBy = null) :void
    {
        $this->createdBy = $createdBy;

        //return $this;
    }

    /**
     * Get createdBy
     *
     * @return App\Entity\User 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return BaseAuditableEntity
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        if($deleted == 0) {
            $this->deletedAt = null;
        } else {  $this->deletedAt = $this->currentDateTime(); }

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean 
     */
    public function getDeleted():?bool
    {
        return $this->deleted;
    }

    /**
     * Is deleted
     *
     * @return boolean 
     */
    public function IsDeleted() :?bool
    {
        return $this->deleted;
    }

    /**
     * Delete
     *
     * @return boolean 
     */
    public function Delete() :void
    {
        $this->deleted = 1;
        $this->deletedAt = $this->currentDateTime();
        
    }

    /**
     * Restore
     */
    public function Restore() :void
    {
        $this->deletedAt = null;
        $this->deleted = 0;
    }

    public function willBeDeleted(?\DateTimeInterface $deletedAt = null): bool
    {
        $this->deletedAt = $deletedAt;
        return true;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    private function currentDateTime(): DateTimeInterface
    {
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));
        if ($dateTime === false) { return null;  }
        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));
        return $dateTime;
    }

    public function getArchived(): ?bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): self
    {
        $this->archived = $archived;

        return $this;
    }

    public function isArchived(): ?bool
    {
        return $this->archived;
    }
}
