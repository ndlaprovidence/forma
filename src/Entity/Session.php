<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionRepository")
 */
class Session
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start_date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $end_date;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SessionTrainer", mappedBy="sessions", orphanRemoval=true)
     */
    private $sessionTrainers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SessionTrainee", mappedBy="sessions", orphanRemoval=true)
     */
    private $sessionTrainees;

    public function __construct()
    {
        $this->sessionTrainers = new ArrayCollection();
        $this->sessionTrainees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Collection|SessionTrainer[]
     */
    public function getSessionTrainers(): Collection
    {
        return $this->sessionTrainers;
    }

    public function addSessionTrainer(SessionTrainer $sessionTrainer): self
    {
        if (!$this->sessionTrainers->contains($sessionTrainer)) {
            $this->sessionTrainers[] = $sessionTrainer;
            $sessionTrainer->setSessions($this);
        }

        return $this;
    }

    public function removeSessionTrainer(SessionTrainer $sessionTrainer): self
    {
        if ($this->sessionTrainers->contains($sessionTrainer)) {
            $this->sessionTrainers->removeElement($sessionTrainer);
            // set the owning side to null (unless already changed)
            if ($sessionTrainer->getSessions() === $this) {
                $sessionTrainer->setSessions(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SessionTrainee[]
     */
    public function getSessionTrainees(): Collection
    {
        return $this->sessionTrainees;
    }

    public function addSessionTrainee(SessionTrainee $sessionTrainee): self
    {
        if (!$this->sessionTrainees->contains($sessionTrainee)) {
            $this->sessionTrainees[] = $sessionTrainee;
            $sessionTrainee->setSessions($this);
        }

        return $this;
    }

    public function removeSessionTrainee(SessionTrainee $sessionTrainee): self
    {
        if ($this->sessionTrainees->contains($sessionTrainee)) {
            $this->sessionTrainees->removeElement($sessionTrainee);
            // set the owning side to null (unless already changed)
            if ($sessionTrainee->getSessions() === $this) {
                $sessionTrainee->setSessions(null);
            }
        }

        return $this;
    }
}
