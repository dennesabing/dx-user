<?php

namespace DxUser\Entity;

use ZfcUser\Entity\User as ZfcUserEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(name="user")
 * @ORM\HasLifecycleCallbacks
 */
class User extends ZfcUserEntity
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
	 * @ORM\Column(name="created", type="datetime")
	 */
	private $created;

	/**
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(name="updated", type="datetime")
	 */
	private $updated;

	public function __construct()
	{
		$this->isEnabled = TRUE;
		$this->isVerified = TRUE;
		$this->isDeleted = FALSE;
		$this->role = 'user';
	}
}
