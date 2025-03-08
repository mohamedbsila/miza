<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        // Get the URL from the session
        $targetPath = $request->getSession()->get('_security.main.target_path');
        
        if ($targetPath) {
            // Remove the target path from session
            $request->getSession()->remove('_security.main.target_path');
            return new RedirectResponse($targetPath);
        }

        // If no target path, redirect to homepage
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }
} 