<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;

/**
* @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
*
* @Hateoas\Relation(
*      "self",
*      href = @Hateoas\Route(
*          "api_client_get",
*          parameters = { "id" = "expr(object.getId())" }
*      )
* )
*/
class Client
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $api;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getApi(): ?User
    {
        return $this->api;
    }

    public function setApi(?User $api): self
    {
        $this->api = $api;

        return $this;
    }

   
}
