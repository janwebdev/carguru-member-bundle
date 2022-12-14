<?php

namespace Carguru\MemberBundle;

use Carguru\MemberBundle\DependencyInjection\MemberExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CarguruMemberBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new MemberExtension();
    }
}
