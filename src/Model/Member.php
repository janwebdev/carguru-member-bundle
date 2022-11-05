<?php

namespace Carguru\MemberBundle\Model;

use Doctrine\ORM\Mapping\Annotation as ORM;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="members")
 */
class Member implements MemberInterface, UserInterface, \Serializable, EquatableInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", options={"unsigned":true})
     */
    private int $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private string $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $password;

    private ?string $plainPassword = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $lastLoginAt = null;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private ?string $lastLoginIpAddress = null;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->plainPassword = $password;
    }

    public function isEqualTo(UserInterface $userInterface): bool
    {
        return $this->getId() === $userInterface->getId();
    }

    public function serialize(): string
    {
        return serialize([$this->id, $this->username, $this->password]);
    }

    public function unserialize($serialized): void
    {
        [$this->id, $this->username, $this->password] = unserialize($serialized, ['allowed_classes' => false]);
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function getLastLoginIpAddress(): ?string
    {
        return $this->lastLoginIpAddress;
    }

    public function setLastLoginIpAddress(?string $lastLoginIpAddress): self
    {
        $this->lastLoginIpAddress = $lastLoginIpAddress;

        return $this;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        // See "Do you need to use a Salt?" at https://symfony.com/doc/current/cookbook/security/entity_provider.html
        // we're using bcrypt in security.yml to encode the password, so
        // the salt value is built-in and you don't have to generate one

        return null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantees that a user always has at least one role for security
        if (empty($roles)) {
            $roles[] = 'ROLE_ADMIN';
        }

        return array_unique($roles);
    }

    public function setRoles(?array $roles): self
    {
        if (!$roles) {
            $roles = ['ROLE_ADMIN'];
        }
        $this->roles = $roles;

        return $this;
    }

    /**
     * Removes sensitive data from the user.
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
