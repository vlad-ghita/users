<?php

declare(strict_types=1);

namespace Bolt\UsersExtension\Twig;

use Bolt\UsersExtension\Extension;
use Bolt\UsersExtension\Utils\ExtensionUtils;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LoginFormExtension extends AbstractExtension
{
    /**@var UrlGeneratorInterface */
    private $router;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    /** @var ExtensionUtils */
    private $utils;

    /** @var Extension */
    private $extension;

    public function __construct(UrlGeneratorInterface $router, CsrfTokenManagerInterface $csrfTokenManager, ExtensionUtils $utils, Extension $extension)
    {
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->utils = $utils;
        $this->extension = $extension;
    }

    /**
     * Register Twig functions.
     */
    public function getFunctions(): array
    {
        $safe = [
            'is_safe' => ['html'],
        ];

        return [
            new TwigFunction('login_form', [$this, 'getLoginForm'], $safe),
            new TwigFunction('login_form_username', [$this, 'getUsernameField'], $safe),
            new TwigFunction('login_form_password', [$this, 'getPasswordField'], $safe),
            new TwigFunction('registration_form_csrf', [$this, 'getCsrfField'], $safe),
            new TwigFunction('registration_form_submit', [$this, 'getSubmitButton'], $safe),
        ];
    }

    public function getLoginForm(bool $withLabels = true, array $labels = []): string
    {
        $username = $this->getUsernameField($withLabels, $labels);
        $password = $this->getPasswordField($withLabels, $labels);
        $csrf = $this->getCsrfField();
        $submit = $this->getSubmitButton($labels);
        $redirectField = $this->getRedirectField();
        $postUrl = $this->router->generate('bolt_login');

        return sprintf("<form method='post' action='%s'>%s %s %s %s %s</form>", $postUrl, $username, $password, $submit, $csrf, $redirectField);
    }

    public function getUsernameField(bool $withLabel, array $labels): string
    {
        $text = in_array('username', $labels) ? $labels['username'] : 'Username';
        $label = $withLabel ? sprintf('<label for="username">%s</label>', $text) : '';

        $input = '<input type="text" id="username" name="username">';
        return $label . $input;
    }

    public function getPasswordField(bool $withLabel, array $labels): string
    {
        $text = in_array('password', $labels) ? $labels['password'] : 'Password';
        $label = $withLabel ? sprintf('<label for="password">%s</label>', $text) : '';

        $input = '<input type="password" id="password" name="password">';
        return $label . $input;
    }

    public function getEmailField(bool $withLabel, array $labels): string
    {
        $text = in_array('email', $labels) ? $labels['email'] : 'Email';
        $label = $withLabel ? sprintf('<label for="email">%s</label>', $text) : '';

        $input = '<input type="email" id="email" name="email">';
        return $label . $input;
    }

    public function getSubmitButton(array $labels = []): string
    {
        $text = in_array('submit', $labels) ? $labels['submit']: 'Submit';

        return sprintf('<input type="submit" value="%s">', $text);
    }

    public function getCsrfField(): string
    {
        $token = $this->csrfTokenManager->getToken('authenticate');

        return sprintf('<input type="hidden" name="_csrf_token" value="%s">', $token);
    }

    public function getRedirectField(string $pathOrUrl = null): string
    {
        if ($pathOrUrl === null) {
            $pathOrUrl = '/'; // @todo: Get the path from the config
        }

        if ($this->utils->isRoute($pathOrUrl)) {
            $pathOrUrl = $this->utils->generateFromRoute($pathOrUrl);
        }

        return sprintf('<input type="hidden" name="_target_path" value="%s">', $pathOrUrl);
    }
}
