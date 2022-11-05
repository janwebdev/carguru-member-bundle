<?php

namespace Carguru\MemberBundle\Doctrine;

use Carguru\MemberBundle\Model\MemberInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class MemberPasswordSubscriber implements EventSubscriber
{
	private UserPasswordHasherInterface $passwordEncoder;

	public function __construct(UserPasswordHasherInterface $passwordEncoder)
	{
		$this->passwordEncoder = $passwordEncoder;
	}

	public function prePersist(LifecycleEventArgs $args): void
	{
		$object = $args->getObject();
		if(!$object instanceof MemberInterface) {
			return;
		}

		$this->encodePassword($object);
	}

	public function preUpdate(LifecycleEventArgs $args): void
	{
		$object = $args->getObject();
		if(!$object instanceof MemberInterface) {
			return;
		}

		if($object->getPlainPassword()) {

		    $this->encodePassword($object);

		    // necessary to force the update to see the change
		    $em = $args->getObjectManager();
		    $meta = $em->getClassMetadata(get_class($object));
		    $em->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $object);
        }
	}

	private function encodePassword(PasswordAuthenticatedUserInterface $object): void
	{
		if($object->getPlainPassword()) {
			$encoded = $this->passwordEncoder->hashPassword($object, $object->getPlainPassword());
			$object->setPassword($encoded);
		}
	}

	public function getSubscribedEvents(): array
	{
		return ['prePersist', 'preUpdate'];
	}
}