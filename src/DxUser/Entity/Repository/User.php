<?php

namespace DxUser\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class User extends EntityRepository
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
	protected $cachePrefix = 'DxUserEntity';

	/**
	 * Find a user by username
	 * @param string $username
	 * @return \DxUser\Entity\User
	 */
	public function findByUsername($username)
	{
		$qb = $this->createQueryBuilder('u');
		$query = $qb->where($qb->expr()->eq('u.username', "'" . $username . "'"))
				->getQuery()
				->useResultCache($this->cacheEnabled, $this->cacheLifetime, $this->cacheName(__FUNCTION__ . $username));
		return $query->getSingleResult();
	}

	/**
	 * Find a user by Email address
	 * @param type $email
	 * @return \DxUser\Entity\User
	 */
	public function findByEmail($email)
	{
		$qb = $this->createQueryBuilder('u');
		$query = $qb->where($qb->expr()->eq('u.email', "'" . $email . "'"))
				->getQuery()
				->useResultCache($this->cacheEnabled, $this->cacheLifetime, $this->cacheName(__FUNCTION__ . $email));
		return $query->getSingleResult();
	}

	/**
	 * Find a user by user Id
	 * @param type $id
	 * @return \DxUser\Entity\User
	 */
	public function findById($id)
	{
		$qb = $this->createQueryBuilder('u');
		$query = $qb->where($qb->expr()->eq('u.id', $id))
				->getQuery()
				->useResultCache($this->cacheEnabled, $this->cacheLifetime, $this->cacheName(__FUNCTION__ . $id));
		return $query->getSingleResult();
	}

	/**
	 * Insert new User
	 * @param type $entity
	 * @param type $tableName
	 * @param \DxUser\Entity\Repository\HydratorInterface $hydrator
	 * @return \DxUser\Entity\User
	 */
	public function insert($entity)
	{
		return $this->persist($entity);
	}

	/**
	 * Update a user
	 * @param type $entity
	 * @param type $where
	 * @param type $tableName
	 * @param \DxUser\Entity\Repository\HydratorInterface $hydrator
	 * @return \DxUser\Entity\User
	 */
	public function update($entity)
	{
		return $this->persist($entity);
	}

	/**
	 * Execute sql command
	 * @param type $entity
	 * @return \DxUser\Entity\User
	 */
	public function persist($entity)
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
				$cacheDriver->delete($this->cachePrefix . 'findByUsername' . $entity->getUsername());
				$cacheDriver->delete($this->cachePrefix . 'findByEmail' . $entity->getEmail());
				$cacheDriver->delete($this->cachePrefix . 'findById' . $entity->getId());
			}
		}
	}
}
