<?php

namespace DxUser\Entity;

interface UserCodesInterface
{
/**
	 * Set the User Object
	 * @param object $user
	 * @return \DxUser\Entity\UserCodes 
	 */
	public function setUser($user);

	/**
	 * Get the User Object
	 * @return object
	 */
	public function getUser();

	/**
	 * Set the User Id
	 * @param integer $userId
	 * @return \DxUser\Entity\UserCodes 
	 */
	public function setUserId($userId);

	/**
	 * Get User Id
	 * @return integer 
	 */
	public function getUserId();

	/**
	 * Set the Type Of code
	 * @param string $typeOf
	 * @return \DxUser\Entity\UserCodes 
	 */
	public function setTypeOf($typeOf);

	/**
	 * Get the Type of code
	 * @return string 
	 */
	public function getTypeOf();

	/**
	 * Set the Code
	 * @param string $code
	 * @return \DxUser\Entity\UserCodes 
	 */
	public function setCode($code);

	/**
	 * Get the Code
	 * @return string 
	 */
	public function getCode();
}
