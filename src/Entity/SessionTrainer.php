<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="tbl_sessionTrainer")
 * @ORM\Entity(repositoryClass="App\Repository\SessionTrainerRepository")
 */
class SessionTrainer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trainer")
     * @ORM\JoinColumn(nullable=true)
     */
    private $trainer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Session")
     * @ORM\JoinColumn(nullable=true)
     */
    private $session;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrainer(): ?Trainer
    {
        return $this->trainer;
    }

    public function setTrainer(?Trainer $trainer): self
    {
        $this->trainer = $trainer;

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
