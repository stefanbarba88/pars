<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractLoginFormAuthenticator {
  use TargetPathTrait;

  public const LOGIN_ROUTE = 'app_login';

  private $urlGenerator;
  private $entityManager;
  private $csrfTokenManager;
  private $passwordEncoder;

  public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordHasherInterface $passwordEncoder) {
    $this->urlGenerator = $urlGenerator;
    $this->entityManager = $entityManager;
    $this->csrfTokenManager = $csrfTokenManager;
    $this->passwordEncoder = $passwordEncoder;
  }

  public function authenticate(Request $request): Passport {
    $email = $request->request->get('email', '');

    $request->getSession()->set(Security::LAST_USERNAME, $email);

    return new Passport(
      new UserBadge($email),
      new PasswordCredentials($request->request->get('password', '')),
      [
        new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
      ]
    );
  }

  public function getCredentials(Request $request) {
    $credentials = [
      'email' => $request->request->get('email'),
      'password' => $request->request->get('password'),
      '_csrf_token' => $request->request->get('_csrf_token'),
    ];
    $request->getSession()->set(Security::LAST_USERNAME, $credentials['email']);

    return $credentials;
  }
  public function getUser($credentials, UserProviderInterface $userProvider) {
    return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials[ 'email' ]]);
  }

  public function checkCredentials($credentials, UserInterface $user) {
    $token = new CsrfToken('authenticate' , $credentials[' csrf_token' ]);
    if(!$this->csrfTokenManager->isTokenValid($token)) {
      throw new InvalidCsrfTokenException();
    }
    return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
  }

  public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
//        // 1. Try to redirect the user to their original intended path
//        if($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
//            return new RedirectResponse($targetPath);
//        }
//        return new RedirectResponse($this->urlGenerator->generate('app_admin' ));
    $user = $token->getUser();

    if (in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
      return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    return new RedirectResponse($this->urlGenerator->generate('app_home'));
  }

  protected function getLoginUrl(Request $request): string {
    return $this->urlGenerator->generate(self::LOGIN_ROUTE);
  }
}
