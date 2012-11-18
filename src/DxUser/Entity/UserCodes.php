<?php

namespace DxUser\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DxUser\Entity\UserCodesIterface;

/**
 * @ORM\Table(name="user_codes")
 * @ORM\Entity(repositoryClass="DxUser\Entity\Repository\UserCodes")
 * @ORM\HasLifecycleCallbacks
 */
class UserCodes implements UserCodesInterface
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="DxUser\Entity\User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", onDelete="CASCADE")
	 */
	private $user;

	/**
	 * @ORM\Column(name="user_id", type="integer")
	 */
	private $userId;

	/**
	 * @ORM\Column(name="typeof", type="string", length=255)
	 */
	private $typeOf;

	/**
	 * @ORM\Column(name="code", type="string", length=255)
	 */
	private $code;

	/**
	 * @ORM\Column(name="serialized_data", type="text", nullable=false)
	 */
	private $extraInfo;

	/**
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="created", type="datetime")
	 */
	private $created;

	/**
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(name="updated", type="datetime")
	 */
	private $updated;

	/**
	 * Extra data to be serialized
	 * @var array
	 */
	private $extraInfos = array();

	public function __construct()
	{
		$this->extraInfos = array();
	}

	/**
	 * Set the User Object
	 * @param object $user
	 * @return \DxUser\Entity\UserCodes 
	 */
	public function setUser($user)
	{
		$this->user = $user;
		$this->setUserId($user->getId());
		return $this;
	}

	/**
	 * Get the User Object
	 * @return object
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Set the User Id
	 * @param integer $userId
	 * @return \DxUser\Entity\UserCodes 
	 */
	public function setUserId($userId)
	{
		$this->userId = $userId;
		return $this;
	}

	/**
	 * Get User Id
	 * @return integer 
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * Set the Type Of code
	 * @param string $typeOf
	 * @return \DxUser\Entity\UserCodes 
	 */
	public function setTypeOf($typeOf)
	{
		$this->typeOf = $typeOf;
		return $this;
	}

	/**
	 * Get the Type of code
	 * @return string 
	 */
	public function getTypeOf()
	{
		return $this->typeOf;
	}

	/**
	 * Set the Code
	 * @param string $code
	 * @return \DxUser\Entity\UserCodes 
	 */
	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}

	/**
	 * Get the Code
	 * @return string 
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Add an extra info
	 * @param string $key
	 * @param mixed $val
	 * @return \DxUser\Entity\UserCodes 
	 */
	public function addExtra($key, $val)
	{
		$this->extraInfos[$key] = $val;
		return $this;
	}

	/**
	 * Remove an extra info
	 * @param string $key
	 * @return \DxUser\Entity\UserCodes 
	 */
	public function removeExtra($key)
	{
		if (isset($this->extraInfos[$key]))
		{
			unset($this->extraInfos);
		}
		return $this;
	}

	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 */
	public function doPrePersist()
	{
		$this->extraInfo = serialize($this->extraInfos);
	}

	/**
	 * @ORM\PostUpdate
	 * @ORM\PostPersist
	 */
	public function doPostPersist()
	{
		$this->extraInfos = unserialize($this->extraInfo);
	}

}
