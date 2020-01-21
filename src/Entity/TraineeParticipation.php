<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TraineeParticipationRepository")
 */
class TraineeParticipation
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Trainee", inversedBy="traineeParticipation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trainee;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Session", mappedBy="traineeParticipation")
     */
    private $session;

    public function __construct()
    {
        $this->session = new ArrayCollection();
    }

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

    /**
     * @return Collection|Session[]
     */
    public function getSession(): Collection
    {
        return $this->session;
    }

    public function addSession(Session $session): self
    {
        if (!$this->session->contains($session)) {
            $this->session[] = $session;
            $session->setTraineeParticipation($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->session->contains($session)) {
            $this->session->removeElement($session);
            // set the owning side to null (unless already changed)
            if ($session->getTraineeParticipation() === $this) {
                $session->setTraineeParticipation(null);
            }
        }

        return $this;
    }
}
