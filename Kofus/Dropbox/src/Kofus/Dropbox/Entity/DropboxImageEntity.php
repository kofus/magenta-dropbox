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
     * @ORM\Column()
     */
    protected $dropboxRevision;
    
    public function setDropboxRevision($value)
    {
    	$this->dropboxRevision = $value; return $this;
    }
    
    public function getDropboxRevision()
    {
    	return $this->dropboxRevision;
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
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $dropboxTimestampModified;
    
    public function setDropboxTimestampModified(\DateTime $datetime)
    {
    	$this->dropboxTimestampModified = $datetime; return $this;
    }
    
    public function getDropboxTimestampModified()
    {
    	return $this->dropboxTimestampModified;
    }
    
  
    public function getNodeType()
    {
    	return 'DROPIMG';
    }
    
    
    
}