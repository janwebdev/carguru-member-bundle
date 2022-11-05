<?php

namespace Carguru\MemberBundle\Controller;

use Carguru\MemberBundle\Form\MemberLoginForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/admin/login", name="carguru_member_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response|RedirectResponse
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED') || $this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('sonata_admin_dashboard');
        }

        // get the login error if there is one
        $errors = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = null;
        if (!empty($errors)) {
            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();
        }

        $form = $this->createForm(MemberLoginForm::class, [
            '_username' => $lastUsername
        ]);

        return $this->renderForm(
            '@CarguruMember/security/login.html.twig',
            ['errors' => $errors, 'form' => $form]
        );
    }

    /**
     * @Route("/admin/logout", name="carguru_member_logout")
     */
    public function logout(): void
    {
        throw new \LogicException("Don't forget to activate logout in security.yaml");
    }
}
