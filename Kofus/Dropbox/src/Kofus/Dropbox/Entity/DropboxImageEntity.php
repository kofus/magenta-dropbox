<?php 
namespace Kofus\Dropbox\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kofus\Media\Entity\ImageEntity;

/**
 * @ORM\Entity
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="dropboxFileId", columns={"dropboxFileId"})})
 */

class DropboxImageEntity extends ImageEntity
{
    /**
     * @ORM\Column()
     */
    protected $dropboxEntryId;
    
    public function setDropboxEntryId($value)
    {
        $this->dropboxEntryId = $value; return $this;
    }
    
    public function getDropboxEntryId()
    {
        return $this->dropboxEntryId;
    }
    
    
    
    /**
     * @ORM\Column(type="json_array")
     */
    protected $dropboxMediaInfo = array();
    
    public function setDropboxMediaInfo(array $value)
    {
    	$this->dropboxMediaInfo = $value; return $this;
    }
    
    public function getDropboxMediaInfo()
    {
    	return $this->dropboxMediaInfo;
    }
    
  
    public function getNodeType()
    {
    	return 'DROPIMG';
    }
    
    
    
}