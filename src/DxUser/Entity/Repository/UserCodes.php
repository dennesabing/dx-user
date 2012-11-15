<?php

namespace DxUser\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class UserCodes extends EntityRepository
{
	/**
	 * Find a row by a user, typeof and code
	 * @param object|integer $user $the User Object
	 * @param string $typeOf The Type of row
	 * @param string $code The Code
	 * @return object
	 */
	public function findUserCode($user, $typeOf, $code)
	{
		return $this->findOneBy(array('user_id' => $user->getId(), 'typeof' => $typeOf));
	}
}
