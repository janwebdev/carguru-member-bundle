<?php

namespace Carguru\MemberBundle\Security;

use Carguru\MemberBundle\Form\MemberLoginForm;
use Carguru\MemberBundle\Service\MemberManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class MemberFormAuthenticator extends AbstractLoginFormAuthenticator
{
	use TargetPathTrait;

	private FormFactoryInterface $formFactory;
	private MemberManager $memberManager;
	private RouterInterface $router;

	public const LOGIN_ROUTE            = 'carguru_member_login';
	public const SUCCESS_REDIRECT_ROUTE = 'sonata_admin_dashboard';

	public function __construct(
        FormFactoryInterface $formFactory,
        MemberManager $memberManager,
        RouterInterface $router
    )
	{
		$this->formFactory = $formFactory;
		$this->memberManager = $memberManager;
		$this->router = $router;
	}

	protected function getLoginUrl(Request $request): string
	{
		return $this->router->generate(self::LOGIN_ROUTE);
	}

	public function supports(Request $request): bool
	{
		$routeName = $request->attributes->get('_route');
		return $routeName === self::LOGIN_ROUTE && $request->isMethod(Request::METHOD_POST);
	}

	public function authenticate(Request $request): Passport
	{
		$form = $this->formFactory->create(MemberLoginForm::class);
		$form->handleRequest($request);

		$data = $form->getData();
		$request->getSession()->set(
			Security::LAST_USERNAME,
			$data['_username'] ?? ''
		);

		if (!$form->isValid()) {
			$errors = [];
			foreach($form->all() as $el) {
				foreach($el->getErrors() as $error) {
					$errors[$el->getName()][] = $error->getMessage();
				}
			}
			throw new CustomUserMessageAuthenticationException(json_encode($errors));
		}


		$username = $data['_username'];
		$password = $data['_password'];
		$csrfToken = $request->request->get('_csrf_token');

		return new Passport(
			new UserBadge($username, function ($userIdentifier) {
                $member = $this->memberManager->findByUsername($userIdentifier);

				if (!$member) {
					// fail authentication with a custom error
					throw new CustomUserMessageAuthenticationException('Account with such credentials cannot be found');
				}

				return $member;
			}),
			new PasswordCredentials($password),
			[
				new CsrfTokenBadge('authenticate', $csrfToken),
				new RememberMeBadge()
			]
		);
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
	{
		$targetPath = null;

		// if the user hit a secure page and start() was called, this was
		// the URL they were on, and probably where you want to redirect to
		if ($request->getSession() instanceof SessionInterface) {
			$targetPath = $this->getTargetPath($request->getSession(), $firewallName);
		}

		if (!$targetPath) {
			$targetPath = $this->getDefaultSuccessRedirectUrl();
		}

		if ($request->isXmlHttpRequest()) {

			return new JsonResponse(['status' => true, 'redirect' => $targetPath]);

		}

		return new RedirectResponse($targetPath);
	}

	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
	{
		if ($request->isXmlHttpRequest()) {

			return new JsonResponse(['status' => false, 'error' => 'Wrong credentials', 'exception' => $exception->getMessage()]);

		}

		if ($request->getSession() instanceof SessionInterface) {
			$request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
		}

		$url = $this->getLoginUrl($request);

		return new RedirectResponse($url);
	}

	protected function getDefaultSuccessRedirectUrl()
	{
		return $this->router->generate(self::SUCCESS_REDIRECT_ROUTE);
	}
}