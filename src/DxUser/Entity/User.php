<?php

namespace DxUser\Entity;

use DxUser\Entity\Users;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="DxUser\Entity\Repository\User")
 * @ORM\HasLifecycleCallbacks
 */
class User extends Users
{
}
