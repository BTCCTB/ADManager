<?php

namespace App\Entity;

use Adldap\Models\User as AdUser;
use App\Entity\Traits\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 *
 * @UniqueEntity(fields={"email"}, message="It looks this user has already an account")
 *
 * @Gedmo\Loggable(logEntryClass="LoggableEntry")
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class User implements EntityInterface, UserInterface, EquatableInterface
{
    // Traits
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    private $password;

    /**
     * @Assert\NotBlank(groups={"Create"})
     *
     * @var string
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @Gedmo\Versioned()
     *
     * @var string[]
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", unique=true)
     *
     * @Gedmo\Versioned()
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     *
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(type="string")
     *
     * @Gedmo\Versioned()
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $accountName;

    /**
     * @ORM\Column(type="string")
     *
     * @Gedmo\Versioned()
     *
     * @var string
     */
    private $firstname;

    /**
     * @ORM\Column(type="string")
     *
     * @Gedmo\Versioned()
     *
     * @var string
     */
    private $lastname;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Gedmo\Versioned()
     *
     * @Assert\NotBlank()
     *
     * @var bool
     */
    private $isActive = true;

    /**
     * @ORM\Column(type="date", nullable=true)
     *
     * @var \DateTime
     */
    private $expiredAt = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MessageLog", mappedBy="sender")
     */
    private $messageLogs;

    public function __construct()
    {
        $this->messageLogs = new ArrayCollection();
    }

    /**
     * Returns the roles granted to the user.
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return string[] The user roles
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }

        return $roles;
    }

    /**
     * @param array $roles
     *
     * @return self
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return self
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        $this->plainPassword = '';
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     *
     * @return self
     */
    public function setPlainPassword(string $plainPassword): User
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountName(): string
    {
        return $this->accountName;
    }

    /**
     * @param string $accountName
     *
     * @return self
     */
    public function setAccountName(string $accountName): User
    {
        $this->accountName = $accountName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     *
     * @return self
     */
    public function setFirstname(string $firstname): User
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     *
     * @return self
     */
    public function setLastname(string $lastname): User
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return self
     */
    public function setActive(bool $isActive): User
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiredAt(): \DateTime
    {
        return $this->expiredAt;
    }

    /**
     * @param \DateTime $expiredAt
     *
     * @return self
     */
    public function setExpiredAt(\DateTime $expiredAt): User
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return array(
            'email' => $this->getEmail(),
        );
    }

    public function createFromAD(AdUser $adUser)
    {
        $this->setEmail($adUser->getUserPrincipalName())
            ->setFirstname($adUser->getFirstName())
            ->setLastname($adUser->getLastName())
            ->setAccountName($adUser->getAccountName())
            ->setRoles(['ROLE_USER'])
            ->setActive($adUser->isActive())
            ->setCreatedAt(new \DateTime())
        ;
    }

    public function getIdentity()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    /**
     * The equality comparison should neither be done by referential equality
     * nor by comparing identities (i.e. getId() === getId()).
     *
     * However, you do not need to compare every attribute, but only those that
     * are relevant for assessing whether re-authentication is required.
     *
     * Also implementation should consider that $user instance may implement
     * the extended user interface `AdvancedUserInterface`.
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if ($user instanceof self) {
            $isEqual = (count($this->getRoles()) === count($user->getRoles()));

            if ($isEqual) {
                foreach ($this->getRoles() as $role) {
                    $isEqual = $isEqual && \in_array($role, $user->getRoles(), true);
                }
            }

            return $isEqual;
        }

        return false;
    }

    public function __toString()
    {
        return $this->getIdentity();
    }

    /**
     * @return Collection|MessageLog[]
     */
    public function getMessageLogs(): Collection
    {
        return $this->messageLogs;
    }

    public function addMessageLog(MessageLog $messageLog): self
    {
        if (!$this->messageLogs->contains($messageLog)) {
            $this->messageLogs[] = $messageLog;
            $messageLog->setSender($this);
        }

        return $this;
    }

    public function removeMessageLog(MessageLog $messageLog): self
    {
        if ($this->messageLogs->contains($messageLog)) {
            $this->messageLogs->removeElement($messageLog);
            // set the owning side to null (unless already changed)
            if ($messageLog->getSender() === $this) {
                $messageLog->setSender(null);
            }
        }

        return $this;
    }
}
