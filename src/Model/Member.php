<?php

namespace Carguru\MemberBundle\Model;

use Doctrine\ORM\Mapping\Annotation as ORM;

class Member implements MemberInterface
{
    private int $id;

    private string $username;

    private string $password;

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
}
