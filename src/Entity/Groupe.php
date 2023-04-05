<?php

namespace App\Entity;

use App\Repository\GroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupeRepository::class)]
class Groupe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'groupes')]
    private Collection $Utilisateurs;

    public function __construct()
    {
        $this->Utilisateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUtilisateurs(): Collection
    {
        return $this->Utilisateurs;
    }

    public function addUtilisateur(User $utilisateur): self
    {
        if (!$this->Utilisateurs->contains($utilisateur)) {
            $this->Utilisateurs->add($utilisateur);
        }

        return $this;
    }

    public function removeUtilisateur(User $utilisateur): self
    {
        $this->Utilisateurs->removeElement($utilisateur);

        return $this;
    }
}
