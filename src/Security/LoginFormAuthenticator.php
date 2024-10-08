<?php

namespace App\Security;

use App\Entity\Profesional;
use App\Repository\ProfesionalRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class LoginFormAuthenticator extends AbstractAuthenticator
{
    private ProfesionalRepository $profesionalRepository;
    private RouterInterface $router;

    public function __construct(ProfesionalRepository $profesionalRepository, RouterInterface $router)
    {
        $this->profesionalRepository = $profesionalRepository;
        $this->router = $router;
    }

    /**
     * Una vez que activemos esta nueva clase en el sistema de seguridad, al principio de cada petición, Symfony llamará a este método supports() y básicamente preguntará
     * ¿Ves información de autenticación en esta petición que entiendas?
     */
    public function supports(Request $request): ?bool
    {
        // return $request->headers->has('X-AUTH-TOKEN');
        return ($request->getPathInfo() === '/login' && $request->isMethod('POST'));
    }

    public function authenticate(Request $request): Passport
    {
        $nif = $request->request->get('_username');
        $password = $request->request->get('_password');

        return new Passport(
            new UserBadge($nif, function ($userIdentifier) {
                // Opcionalmente, pase una devolución de llamada para cargar el profesional manualmente
                $profesional = $this->profesionalRepository->findOneBy(['nif' => $userIdentifier]);
                if (!$profesional) {
                    throw new UserNotFoundException();
                }
                return $profesional;
            }),
            new CustomCredentials(function ($credentials, Profesional $profesional) {
                return $credentials === 'Aa_123456';
            }, $password)
        );

        // $apiToken = $request->headers->get('X-AUTH-TOKEN');
        // if (null === $apiToken) {
        // The token header was empty, authentication fails with HTTP Status
        // Code 401 "Unauthorized"
        // throw new CustomUserMessageAuthenticationException('No API token provided');
        // }

        // implement your own logic to get the user identifier from `$apiToken`
        // e.g. by looking up a user in the database using its API key
        // $userIdentifier = /** ... */;

        // return new SelfValidatingPassport(new UserBadge($userIdentifier));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse(
            $this->router->generate('app_homepage')
        );
        // on success, let the request continue
        // return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];
        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse(
            $this->router->generate('app_login')
        );

        // return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    // public function start(Request $request, AuthenticationException $authException = null): Response
    // {
    //     /*
    //      * If you would like this class to control what happens when an anonymous user accesses a
    //      * protected page (e.g. redirect to /login), uncomment this method and make this class
    //      * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
    //      *
    //      * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
    //      */
    // }
}
