<?php

namespace App\Entity;

use App\Repository\PhotoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PhotoRepository::class)
 */
class Photo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $namePhoto;

    /**
     * @ORM\ManyToOne(targetEntity=Tricks::class, inversedBy="photos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tricks;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNamePhoto(): ?string
    {
        return $this->namePhoto;
    }

    public function setNamePhoto(string $namePhoto): self
    {
        $this->namePhoto = $namePhoto;

        return $this;
    }

    public function getTricks(): ?Tricks
    {
        return $this->tricks;
    }

    public function setTricks(?Tricks $tricks): self
    {
        $this->tricks = $tricks;

        return $this;
    }
}
