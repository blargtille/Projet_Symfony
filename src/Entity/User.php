<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Email]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Assert\Regex(pattern: '/^(?:\+?\d{1,3}[- ]?)?\d{7,11}$/',
        message: 'Le format de du téléphone n\'est pas valide')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[Assert\Regex(pattern: '/^.+\.(jpg|jpeg|png|gif|bmp)$/',
        message: 'La photo n\'est pas une image')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column]
    private ?bool $administrateur = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $site = null;

    #[ORM\ManyToMany(targetEntity: Sortie::class, mappedBy: 'participant')]
    private Collection $sortiesParticipation;

    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Sortie::class, orphanRemoval: true)]
    private Collection $sortiesOrganisation;

    public function __construct()
    {
        $this->sortiesParticipation = new ArrayCollection();
        $this->sortiesOrganisation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesParticipation(): Collection
    {
        return $this->sortiesParticipation;
    }

    public function addSortiesParticipation(Sortie $sortiesParticipation): self
    {
        if (!$this->sortiesParticipation->contains($sortiesParticipation)) {
            $this->sortiesParticipation->add($sortiesParticipation);
            $sortiesParticipation->addParticipant($this);
        }

        return $this;
    }

    public function removeSortiesParticipation(Sortie $sortiesParticipation): self
    {
        if ($this->sortiesParticipation->removeElement($sortiesParticipation)) {
            $sortiesParticipation->removeParticipant($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesOrganisation(): Collection
    {
        return $this->sortiesOrganisation;
    }

    public function addSortiesOrganisation(Sortie $sortiesOrganisation): self
    {
        if (!$this->sortiesOrganisation->contains($sortiesOrganisation)) {
            $this->sortiesOrganisation->add($sortiesOrganisation);
            $sortiesOrganisation->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortiesOrganisation(Sortie $sortiesOrganisation): self
    {
        if ($this->sortiesOrganisation->removeElement($sortiesOrganisation)) {
            // set the owning side to null (unless already changed)
            if ($sortiesOrganisation->getOrganisateur() === $this) {
                $sortiesOrganisation->setOrganisateur(null);
            }
        }

        return $this;
    }
}
