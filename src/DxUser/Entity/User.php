<?php

namespace DxUser\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="DxUser\Entity\Repository\User")
 * @ORM\HasLifecycleCallbacks
 */
class User
{

	/**
	 * @ORM\Column(type="integer", name="user_id")
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(length=255)
	 */
	protected $username;

	/**
	 * @ORM\Column(length=255)
	 */
	protected $email;

	/**
	 * @ORM\Column(length=255, name="display_name")
	 */
	protected $displayName;

	/**
	 * @ORM\Column(length=255)
	 */
	protected $password;
	
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
	 * @ORM\Column(type="datetime")
	 */
	private $created;

	/**
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(type="datetime")
	 */
	private $updated;

	/**
	 * @ORM\OneToMany(targetEntity="DxBuySell\Entity\Item", mappedBy="user", fetch="EXTRA_LAZY")
	 */
	private $buySellItems;

	public function __construct()
	{
		$this->isEnabled = TRUE;
		$this->isVerified = TRUE;
		$this->isDeleted = FALSE;
	}
	
	/**
	 * Get id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set id.
	 *
	 * @param int $id
	 * @return UserInterface
	 */
	public function setId($id)
	{
		$this->id = (int) $id;
		return $this;
	}

	/**
	 * Get username.
	 *
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Set username.
	 *
	 * @param string $username
	 * @return UserInterface
	 */
	public function setUsername($username)
	{
		$this->username = $username;
		return $this;
	}

	/**
	 * Get email.
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Set email.
	 *
	 * @param string $email
	 * @return UserInterface
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * Get displayName.
	 *
	 * @return string
	 */
	public function getDisplayName()
	{
		return $this->displayName;
	}

	/**
	 * Set displayName.
	 *
	 * @param string $displayName
	 * @return UserInterface
	 */
	public function setDisplayName($displayName)
	{
		$this->displayName = $displayName;
		return $this;
	}

	/**
	 * Get password.
	 *
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Set password.
	 *
	 * @param string $password
	 * @return UserInterface
	 */
	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}

}
