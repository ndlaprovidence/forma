<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="tbl_sessionTrainee")
 * @ORM\Entity(repositoryClass="App\Repository\SessionTraineeRepository")
 */
class SessionTrainee
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
    private $convocation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trainee")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trainee;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Session")
     * @ORM\JoinColumn(nullable=false)
     */
    private $session;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConvocation(): ?string
    {
        return $this->convocation;
    }

    public function setConvocation(string $convocation): self
    {
        $this->convocation = $convocation;

        return $this;
    }

    public function getTrainee(): ?Trainee
    {
        return $this->trainee;
    }

    public function setTrainee(?Trainee $trainee): self
    {
        $this->trainee = $trainee;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;

        return $this;
    }
}
