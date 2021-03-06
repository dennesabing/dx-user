<?php

namespace DxUser\Entity;

use DxUser\Entity\Users;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_profile")
 * @ORM\Entity(repositoryClass="DxUser\Entity\Repository\UserProfile")
 * @ORM\HasLifecycleCallbacks
 */
class UserProfile
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer",name="user_id") 
	 */
	private $userId;
	
	/**
     * @ORM\OneToOne(targetEntity="DxUser\Entity\User", inversedBy="profile")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", onDelete="CASCADE")
	 */
	private $user;
	
	/**
	 * The first name
	 * @ORM\Column(type="string",name="f_name", length=64, nullable=true) 
	 * @var string
	 */
	private $firstName = NULL;

	/**
	 * The last name
	 * @ORM\Column(type="string",name="l_name", length=64, nullable=true) 
	 * @var string
	 */
	private $lastName = NULL;

	/**
	 * The middle name
	 * @ORM\Column(type="string",name="m_name", length=64, nullable=true) 
	 * @var string
	 */
	private $middleName = NULL;

	/**
	 * Get the User full name
	 * @return string
	 */
	public function getName()
	{
		return $this->firstName . ' ' . $this->lastName;
	}

	/**
	 * SEt the user middle name
	 * @param string $middleName
	 * @return \DxUser\Entity\UserProfile
	 */
	public function setMiddleName($middleName)
	{
		$this->middleName = $middleName;
		return $this;
	}

	/**
	 * Return the User middle name
	 * @return string
	 */
	public function getMiddleName()
	{
		return $this->middleName;
	}

	/**
	 * Set the User Last Name
	 * @param string $lastName
	 * @return \DxUser\Entity\UserProfile
	 */
	public function setLastName($lastName)
	{
		$this->lastName = $lastName;
		return $this;
	}

	/**
	 * Retuen the User last name
	 * @return string
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * Set the First name
	 * @param string $firstName
	 * @return \DxUser\Entity\UserProfile
	 */
	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
		return $this;
	}

	/**
	 * REturn the User first name
	 * @return string
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * SEt the User
	 * @param type $user
	 * @return \DxUser\Entity\UserProfile
	 */
	public function setUser($user)
	{
		$this->user = $user;
		$this->userId = $user->getId();
		return $this;
	}
	
	/**
	 * REturn USer
	 * @return type
	 */
	public function getUser()
	{
		return $this->user;
	}
	
	/**
	 * Return the User Id
	 * @return integer
	 */
	public function getId()
	{
		return $this->userId;
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
