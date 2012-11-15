<?php

namespace DxUser\Entity;

use ScnSocialAuth\Entity\UserProviderInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user_provider")
 */
class UserProvider implements UserProviderInterface
{
	
	/** 
	 * @ORM\Id 
	 * @ORM\Column(type="integer",name="user_id") 
	 * @ORM\ManyToOne(targetEntity="DxUser\Entity\User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
	 */
	private $userId;

	/** 
	 * @ORM\Id 
	 * @ORM\Column(type="integer",name="provider_id") 
	 */
	private $providerId;

	/** 
	 * @ORM\Column(type="string") 
	 */
	private $provider;

	/**
	 * @return the $userId
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * @param  integer	$userId
	 * @return UserProvider
	 */
	public function setUserId($userId)
	{
		$this->userId = $userId;

		return $this;
	}

	/**
	 * @return the $providerId
	 */
	public function getProviderId()
	{
		return $this->providerId;
	}

	/**
	 * @param  integer $providerId
	 * @return UserProvider
	 */
	public function setProviderId($providerId)
	{
		$this->providerId = $providerId;

		return $this;
	}

	/**
	 * @return the $provider
	 */
	public function getProvider()
	{
		return $this->provider;
	}

	/**
	 * @param  string $provider
	 * @return UserProvider
	 */
	public function setProvider($provider)
	{
		$this->provider = $provider;

		return $this;
	}

}
