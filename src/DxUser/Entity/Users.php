<?php

namespace DxUser\Entity;

use ZfcUser\Entity\User as ZfcUserEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 */
class Users extends ZfcUserEntity
{

	/**
	 * @ORM\Column(name="role", length=255)
	 */
	private $role;

	/**
	 * @ORM\Column(name="is_enabled" ,type="integer")
	 */
	private $isEnabled;
	
	/**
	 * @ORM\Column(name="is_verified" ,type="integer")
	 */
	private $isVerified;
	
	/**
	 * @ORM\Column(name="is_deleted" ,type="integer")
	 */
	private $isDeleted;

	/**
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="created", type="datetime", nullable=true)
	 */
	private $created;

	/**
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(name="updated", type="datetime", nullable=true)
	 */
	private $updated;
	
	/**
     * @ORM\OneToOne(targetEntity="DxUser\Entity\UserProfile")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
	 */
	private $profile;

	public function __construct()
	{
		$this->isEnabled = TRUE;
		$this->isVerified = TRUE;
		$this->isDeleted = FALSE;
		$this->role = 'user';
	}
	
	/**
	 * Verify User email address
	 * @return \DxUser\Entity\User 
	 */
	public function verifyEmailAddress()
	{
		$this->isVerified = TRUE;
		return $this;
	}
	
	/**
	 * UN - Verify User email address
	 * @return \DxUser\Entity\User 
	 */
	public function unVerifyEmailAddress()
	{
		$this->isVerified = FALSE;
		return $this;
	}
	
	/**
	 * Check if user email address is already verified
	 * @return boolean
	 */
	public function isEmailVerified()
	{
		return $this->isVerified;
	}
	
	/**
	 * Return the User Profile
	 * @return type
	 */
	public function getProfile()
	{
		return $this->profile;
	}
	
	/**
	 * Set the PRofile Object
	 * @param object $profile \DxUser\Entity\UserProfile
	 * @return \DxUser\Entity\Users
	 */
	public function setProfile($profile)
	{
		$this->profile = $profile;
		return $this;
	}
	

	/**
	 * Set values by using an array derived from a form
	 * @param array $arr
	 * @return \DxUser\Entity\UserProfile
	 */
	public function setDataArray(array $arr)
	{
		foreach ($arr as $k => $v)
		{
			if (is_array($v))
			{
				$this->setDataArray($v);
			}
			else
			{
				if (property_exists($this, $k))
				{
					$this->$k = $v;
				}
			}
		}
		return $this;
	}
}
