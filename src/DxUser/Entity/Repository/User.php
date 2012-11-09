<?php

namespace DxUser\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class User extends EntityRepository
{

	public function findByUsername($username)
	{
		return $this->findOneBy(array('username' => $username));
	}

	public function findByEmail($email)
	{
		return $this->findOneBy(array('email' => $email));
	}

	public function findById($id)
	{
		return $this->find($id);
	}

	public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
	{
		return $this->persist($entity);
	}

	public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
	{
		return $this->persist($entity);
	}

	protected function persist($entity)
	{
		$this->em->persist($entity);
		$this->em->flush();

		return $entity;
	}

}
