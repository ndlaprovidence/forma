<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="tbl_company")
 * @ORM\Entity(repositoryClass="App\Repository\CompanyRepository")
 */
class Company
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
    private $corporate_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reference_number;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone_number;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Trainee", mappedBy="company", orphanRemoval=true)
     */
    private $trainees;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $postal_code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    public function __construct()
    {
        $this->trainees = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->corporate_name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCorporateName(): ?string
    {
        return $this->corporate_name;
    }

    public function setCorporateName(string $corporate_name): self
    {
        $this->corporate_name = $corporate_name;

        return $this;
    }

    public function getReferenceNumber(): ?string
    {
        return $this->reference_number;
    }

    public function setReferenceNumber(?string $reference_number): self
    {
        $this->reference_number = $reference_number;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(?string $phone_number): self
    {
        $this->phone_number = $phone_number;

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
            $trainee->setCompany($this);
        }

        return $this;
    }

    public function removeTrainee(Trainee $trainee): self
    {
        if ($this->trainees->contains($trainee)) {
            $this->trainees->removeElement($trainee);
            // set the owning side to null (unless already changed)
            if ($trainee->getCompany() === $this) {
                $trainee->setCompany(null);
            }
        }

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postal_code;
    }

    public function setPostalCode(?string $postal_code): self
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }
}
