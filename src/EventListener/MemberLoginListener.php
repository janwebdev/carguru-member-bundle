<?php

namespace App\EventListener;

use Carguru\MemberBundle\Model\MemberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class MemberLoginListener
{
	private EntityManagerInterface $em;
	private RequestStack $request;

	public function __construct(RequestStack $request, EntityManagerInterface $em)
	{
		$this->em = $em;
		$this->request = $request;
	}

	/**
	 * @param InteractiveLoginEvent $event
	 */
	public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
	{
		/** @var MemberInterface $user */
		$user = $event->getAuthenticationToken()->getUser();

		if($user instanceof MemberInterface) {

			$user->setLastLoginAt(new \DateTimeImmutable());
			$user->setLastLoginIpAddress($this->request->getCurrentRequest()->getClientIp());
			$this->em->flush();
		}
	}
}