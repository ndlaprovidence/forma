<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
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
    private $document_name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Trainee", mappedBy="sessionTrainee")
     */
    private $trainees;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Session", inversedBy="sessionTrainees")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sessions;

    public function __construct()
    {
        $this->trainees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocumentName(): ?string
    {
        return $this->document_name;
    }

    public function setDocumentName(string $document_name): self
    {
        $this->document_name = $document_name;

        return $this;
    }

    /**
     * @return Collection|Trainee[]
     */
    public function getTrainees(): Collection
    {
        return $this->trainees;
    }

    public function addTrainee(Trainee $trainee): self
    {
        if (!$this->trainees->contains($trainee)) {
            $this->trainees[] = $trainee;
            $trainee->setSessionTrainee($this);
        }

        return $this;
    }

    public function removeTrainee(Trainee $trainee): self
    {
        if ($this->trainees->contains($trainee)) {
            $this->trainees->removeElement($trainee);
            // set the owning side to null (unless already changed)
            if ($trainee->getSessionTrainee() === $this) {
                $trainee->setSessionTrainee(null);
            }
        }

        return $this;
    }

    public function getSessions(): ?Session
    {
        return $this->sessions;
    }

    public function setSessions(?Session $sessions): self
    {
        $this->sessions = $sessions;

        return $this;
    }
}
