<?php

namespace App\Entity;

use App\Repository\EtatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtatRepository::class)]
class Etat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'etatE', targetEntity: Sortie::class, orphanRemoval: true)]
    private Collection $Sorties;

    public function __construct()
    {
        $this->Sorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->Sorties;
    }

    public function addSorty(Sortie $sorty): self
    {
        if (!$this->Sorties->contains($sorty)) {
            $this->Sorties->add($sorty);
            $sorty->setEtatE($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): self
    {
        if ($this->Sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getEtatE() === $this) {
                $sorty->setEtatE(null);
            }
        }

        return $this;
    }
}
