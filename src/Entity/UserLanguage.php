<?php

namespace App\Entity;

use App\Entity\Traits\Blameable;
use App\Entity\Traits\Timestampable;
use App\Repository\UserLanguageRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(indexes={@ORM\Index(name="user_idx", columns={"user_id"})})
 * @ORM\Entity(repositoryClass=UserLanguageRepository::class)
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 * @UniqueEntity(fields={"userId"}, message="It looks this user has already an language preferences")
 * @Gedmo\Loggable(logEntryClass="LoggableEntry")
 */
class UserLanguage
{
    // Traits
    use Timestampable;
    use Blameable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5)
     * @Gedmo\Versioned
     * @Assert\NotBlank()
     */
    private $language;

    /**
     * @ORM\Column(type="integer")
     * @Gedmo\Versioned
     * @Assert\NotBlank()
     */
    private $userId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getShortLanguage(): ?string
    {
        if (null !== $this->language) {
            return strtolower(substr($this->language, 0, 2));
        }
        return null;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public static function languageChoices()
    {
        return [
            'user.language.form.language.choice.empty' => null,
            'user.language.form.language.choice.fr' => 'fr-fr',
            'user.language.form.language.choice.en' => 'en-us',
            'user.language.form.language.choice.nl' => 'nl-nl',
        ];
    }
}
