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
     * @ORM\ManyToOne(targetEntity="App\Entity\Session", inversedBy="traineeParticipation")
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
