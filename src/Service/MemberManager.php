<?php

namespace Carguru\MemberBundle\Service;

use Carguru\MemberBundle\Model\Member;
use Carguru\MemberBundle\Model\MemberInterface;
use Doctrine\ORM\EntityManagerInterface;

class MemberManager
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function save(MemberInterface $member)
    {
        $this->em->persist($member);
        $this->em->flush();
    }

    public function findByUsername(string $username): ?MemberInterface
    {
        return $this->em->getRepository(Member::class)->findOneBy(['username' => $username]);
    }
}