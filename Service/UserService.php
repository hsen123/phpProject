<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class UserService
{
    const NEWSLETTER_REGISTERED_FLASH = 'newsletter_registered_flash';

    private $translator;

    private $router;

    private $mailer;

    private $manager;

    public function __construct(TranslatorInterface $translator, RouterInterface $router, \Swift_Mailer $mailer, EntityManagerInterface $manager)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->manager = $manager;
    }

    public function getAllNullColumns(User $user)
    {
        $nullColumnCount = 0;

        if (null === $user->getFirstName() || '' === $user->getFirstName()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        if (null === $user->getLastName() || '' === $user->getLastName()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        if (null === $user->getDisplayName() || '' === $user->getDisplayName()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        if (null === $user->getEmail() || '' === $user->getEmail()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        if (null === $user->getCompany() || '' === $user->getCompany()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        if (null === $user->getCompanyAdress() || '' === $user->getCompanyAdress()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        if (null === $user->getCompanyCity() || '' === $user->getCompanyCity()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        if (null === $user->getCompanyPostalCode() || '' === $user->getCompanyPostalCode()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        if (null === $user->getCompanyCountry() || '' === $user->getCompanyCountry()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        if (null === $user->getCompanyPhone() || '' === $user->getCompanyPhone()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        if (null === $user->getSegment() || '' === $user->getSegment()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        if (null === $user->getSegmentDepartment() || '' === $user->getSegmentDepartment()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        if (null === $user->getSegmentWorkgroup() || '' === $user->getSegmentWorkgroup()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        if (null === $user->getSegmentPosition() || '' === $user->getSegmentPosition()) {
            $nullColumnCount = $nullColumnCount + 1;
        }

        return $nullColumnCount;
    }

    public function activateNewsletter(User $user, Request $request)
    {
        $token = md5(uniqid(rand(), true));

        $user->setNewsletterToken($token);
        $user->setNewsletterTokenTime(new \DateTime());
        if (null === $user->getUnsubscribeToken()) {
            $user->setUnsubscribeToken(md5(uniqid(rand(), true)));
        }
        $user->setNewsletterActive(false);

        $message = new \Swift_Message(
            $this->translator->trans('newsletter.email.subject'),
            $this->translator->trans('newsletter.email.body',
                [
                    '%confirmationLink%' => $this->router->generate('confirm-newsletter', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL),
                    '%privacyStatementNewsletter%' => $this->router->generate('infoPrivacyStatementNewsletter', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    '%privacyStatement%' => $this->router->generate('infoPrivacyStatement', [], UrlGeneratorInterface::ABSOLUTE_URL),
                ]
            ),
            'text/html'
        );

        $message->setTo($user->getEmail());
        $message->setFrom([getenv('MAILER_DEFAULT_FROM_ADDRESS') => getenv('MAILER_DEFAULT_FROM_SENDER')]);

        $this->mailer->send($message);
        $this->manager->flush();
        $session = $request->getSession();

        if (!$session instanceof Session) {
            return;
        }

        $session->getFlashBag()->add(self::NEWSLETTER_REGISTERED_FLASH, 'newsletter.registration');
    }
}
