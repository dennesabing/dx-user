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
		$arr = array();
		if($user)
		{
			$arr['userId'] = $user->getId();
		}
		if($typeOf)
		{
			$arr['typeOf'] = $typeOf;
		}
		if($code)
		{
			$arr['code'] = $code;
		}
		return $this->findOneBy($arr);
	}
	
	
	public function removeCode($user, $typeOf, $code = FALSE)
	{
		$meta = $this->getClassMetadata();
		$qb = $this->_em->createQueryBuilder($meta->name);
		$qb->delete($meta->name, 'c');
		$qb->where("c.userId = " . $user->getId());
		$qb->andWhere("c.typeOf = '" . $typeOf . "'");
		if($code)
		{
			$qb->andWhere("c.code = '" . $code . "'");
		}
//		dump($qb->getQuery()->getSql());die;
		return $qb->getQuery()->getScalarResult();
	}

}
