<?php

namespace LivrariaBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * Usuarios 
 *
 * @ORM\Entity
 * @ORM\Table(name="usuarios")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */protected $id;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
     protected $nome;

    /**
     * Set nome
     *
     * @param string $nome
     *
     * @return User
     */
    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * Get nome
     *
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }
}
