<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
* @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
* @ExclusionPolicy("all")
* @Hateoas\Relation(
*      "self",
*      href = @Hateoas\Route(
*          "api_client_get",
*          parameters = { "id" = "expr(object.getId())" }
*      )
* )
* @UniqueEntity(
*     fields={"api", "username"},
*     errorPath="username",
*     message="username is already taken"
* )
*/

class Client
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Expose
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Expose
     * @Assert\NotBlank
     * @Assert\Length(min=3)
     */
    private $username;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     *
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
