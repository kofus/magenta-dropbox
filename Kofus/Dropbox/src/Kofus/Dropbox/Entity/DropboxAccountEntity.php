<?php 
namespace Kofus\Dropbox\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kofus\System\Node;

/**
 * @ORM\Entity
 * @ORM\Table(name="kofus_dropbox_accounts")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="accessToken", columns={"accessToken"})})
 */

class DropboxAccountEntity implements Node\NodeInterface, Node\EnableableNodeInterface 
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
    protected $title;
    
    public function setTitle($value)
    {
        $this->title = $value; return $this;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * @ORM\Column()
     */
    protected $accessToken;
    
    public function setAccessToken($value)
    {
    	$this->accessToken = $value; return $this;
    }
    
    public function getAccessToken()
    {
    	return $this->accessToken;
    }
    
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $enabled = true;
    
    public function isEnabled($bool = null)
    {
    	if ($bool !== null) {
    		$this->enabled = (bool) $bool;
    		return $this;
    	}
    	return $this->enabled;
    }    
    
    public function getNodeType()
    {
    	return 'DROPACC';
    }
    
    public function __toString()
    {
    	return $this->getNodeId();
    }
    
    public function getNodeId()
    {
    	return $this->getNodeType() . $this->getId();
    }    
    
    
    
}