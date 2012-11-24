<?php

namespace DxUser\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class UserProfile extends EntityRepository
{

	/**
	 * Enable/Disable caching
	 * @var boolean
	 */
	protected $cacheEnabled = TRUE;

	/**
	 * Cache lifetime in seconds
	 * @var integer
	 */
	protected $cacheLifetime = 100000;

	/**
	 * The cache name prefix
	 * @var string
	 */
	protected $cachePrefix = 'DxUserProfileEntity';

	/**
	 * Find a user by user id
	 * @param object $user \DxUser\Entity\User
	 * @return \DxUser\Entity\UserProfile
	 */
	public function findByUser($user)
	{
		$qb = $this->createQueryBuilder('p');
		$query = $qb->where($qb->expr()->eq('p.userId', $user->getId()))
				->getQuery()
				->useResultCache($this->cacheEnabled, $this->cacheLifetime, $this->cacheName(__FUNCTION__ . $user->getId()));
		return $query->getSingleResult();
	}

	/**
	 * Execute sql command
	 * @param type $entity
	 * @return \DxUser\Entity\User
	 */
	public function update($entity)
	{
		$this->_em->persist($entity);
		$this->_em->flush();
		$this->clearCache($entity);
		return $entity;
	}

	/**
	 * Return a cache name
	 * @param object|string $node The Node or the cacheName
	 * @param string $method Suffix to add or the class function or method name __FUNCTION__
	 * @return string
	 */
	protected function cacheName($name)
	{
		return $this->cachePrefix . $name;
	}

	/** 	
	 * Clear cache for this entity
	 * @param object $node 
	 */
	public function clearCache($entity)
	{
		$cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
		if ($entity !== NULL)
		{
			$meta = $this->getClassMetadata();
			if ($entity instanceof $meta->name)
			{
				$cacheId = $this->cachePrefix . 'findByUser' . $entity->getId();
				if($cacheDriver->contains($cacheId))
				{
					$cacheDriver->delete($cacheId);
				}
			}
		}
	}

}