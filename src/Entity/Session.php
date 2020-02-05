<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="tbl_session")
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
     * @ORM\Column(type="date", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Training", inversedBy="sessions")
     * @ORM\JoinColumn(nullable=true)
     */
    private $training;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="sessions")
     * @ORM\JoinColumn(nullable=true)
     */
    private $location;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Instructor", inversedBy="sessions")
     * @ORM\JoinColumn(nullable=true)
     */
    private $instructors;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Trainee", inversedBy="sessions")
     */
    private $trainees;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Upload", inversedBy="sessions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $upload;

    /**
     * @ORM\Column(type="time")
     */
    private $start_time_am;

    /**
     * @ORM\Column(type="time")
     */
    private $end_time_am;

    /**
     * @ORM\Column(type="time")
     */
    private $start_time_pm;

    /**
     * @ORM\Column(type="time")
     */
    private $end_time_pm;


    public function __construct()
    {
        $this->instructors = new ArrayCollection();
        $this->trainees = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getTraining()->getTitle();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getTraining(): ?Training
    {
        return $this->training;
    }

    public function setTraining(?Training $training): self
    {
        $this->training = $training;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection|Instructor[]
     */
    public function getInstructors(): Collection
    {
        return $this->instructors;
    }

    public function addInstructor(Instructor $instructor): self
    {
        if (!$this->instructors->contains($instructor)) {
            $this->instructors[] = $instructor;
        }

        return $this;
    }

    public function removeInstructor(Instructor $instructor): self
    {
        if ($this->instructors->contains($instructor)) {
            $this->instructors->removeElement($instructor);
        }

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
        }

        return $this;
    }

    public function removeTrainee(Trainee $trainee): self
    {
        if ($this->trainees->contains($trainee)) {
            $this->trainees->removeElement($trainee);
        }

        return $this;
    }

    public function getUpload(): ?Upload
    {
        return $this->upload;
    }

    public function setUpload(?Upload $upload): self
    {
        $this->upload = $upload;

        return $this;
    }

    public function getStartTimeAm(): ?\DateTimeInterface
    {
        return $this->start_time_am;
    }

    public function setStartTimeAm(\DateTimeInterface $start_time_am): self
    {
        $this->start_time_am = $start_time_am;

        return $this;
    }

    public function getEndTimeAm(): ?\DateTimeInterface
    {
        return $this->end_time_am;
    }

    public function setEndTimeAm(\DateTimeInterface $end_time_am): self
    {
        $this->end_time_am = $end_time_am;

        return $this;
    }

    public function getStartTimePm(): ?\DateTimeInterface
    {
        return $this->start_time_pm;
    }

    public function setStartTimePm(\DateTimeInterface $start_time_pm): self
    {
        $this->start_time_pm = $start_time_pm;

        return $this;
    }

    public function getEndTimePm(): ?\DateTimeInterface
    {
        return $this->end_time_pm;
    }

    public function setEndTimePm(\DateTimeInterface $end_time_pm): self
    {
        $this->end_time_pm = $end_time_pm;

        return $this;
    }
}
