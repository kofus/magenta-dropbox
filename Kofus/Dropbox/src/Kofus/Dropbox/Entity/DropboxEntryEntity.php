<?php 
namespace Kofus\Dropbox\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kofus\Media\Entity\FileEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="kofus_dropbox_entries", uniqueConstraints={@ORM\UniqueConstraint(name="path", columns={"path"})})
 * @ORM\InheritanceType("SINGLE_TABLE")
 */

class DropboxEntryEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    protected $id;
    
    public function getId()
    {
        return $this->id;
    }
    
    
    /**
     * @ORM\Column()
     */
    protected $path;
    
    public function setPath($value)
    {
    	$this->path = $value; return $this;
    }
    
    public function getPath()
    {
    	return $this->path;
    }
    

    /**
     * @ORM\Column()
     */
    protected $revision;
    
    public function setRevision($value)
    {
    	$this->revision = $value; return $this;
    }
    
    public function getRevision()
    {
    	return $this->revision;
    }
    
    
    /**
     * @ORM\Column(type="json_array")
     */
    protected $mediaInfo = array();
    
    public function setMediaInfo(array $value)
    {
    	$this->mediaInfo = $value; return $this;
    }
    
    public function getMediaInfo()
    {
    	return $this->mediaInfo;
    }
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $timestampModified;
    
    public function setTimestampModified(\DateTime $datetime)
    {
    	$this->timestampModified = $datetime; return $this;
    }
    
    public function getTimestampModified()
    {
    	return $this->timestampModified;
    }
    
    /**
     * @ORM\ManyToOne(targetEntity="Kofus\Media\Entity\FileEntity")
     */
    protected $file;
    
    public function getFile()
    {
        return $this->file;
    }
    
    public function setFile(FileEntity $entity=null)
    {
        $this->file = $entity; return $this;
    }
    
    
    
  
    public function getNodeType()
    {
    	return 'DBE';
    }
    
    
    
}