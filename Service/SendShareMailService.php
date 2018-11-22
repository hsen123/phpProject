<?php

namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SendShareMailService
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var Swift_Mailer
     */
    private $mailer;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(TranslatorInterface $translator, Swift_Mailer $mailer, EntityManagerInterface $manager, RouterInterface $router)
    {
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->manager = $manager;
        $this->router = $router;
    }

    public function sendResultSnapshotShareMail($shareLink, $emails)
    {
        $message = new \Swift_Message(
            $this->translator->trans('share.email.snapshot.subject',
                ['%entity%' => $this->translator->trans('share.result')]),
            $this->translator->trans('share.email.snapshot.body',
                [
                    '%entity%' => $this->translator->trans('share.result'),
                    '%shareLink%' => $shareLink,
                ]
            ),
            'text/html'
        );

        $message->setFrom([getenv('MAILER_DEFAULT_FROM_ADDRESS') => getenv('MAILER_DEFAULT_FROM_SENDER')]);

        foreach ($emails as $email) {
            $message->setTo($email);
            $this->mailer->send($message);
        }
    }

    public function sendAnalysisSnapshotShareMail($shareLink, $emails)
    {
        $message = new \Swift_Message(
            $this->translator->trans('share.email.snapshot.subject',
                ['%entity%' => $this->translator->trans('share.analysis')]),
            $this->translator->trans('share.email.snapshot.body',
                [
                    '%entity%' => $this->translator->trans('share.analysis'),
                    '%shareLink%' => $shareLink,
                ]
            ),
            'text/html'
        );

        $message->setFrom([getenv('MAILER_DEFAULT_FROM_ADDRESS') => getenv('MAILER_DEFAULT_FROM_SENDER')]);

        foreach ($emails as $email) {
            $message->setTo($email);
            $this->mailer->send($message);
        }
    }

    public function sendResultDynamicShareMail($shareLink, $emails)
    {
        $message = new \Swift_Message(
            $this->translator->trans('share.email.dynamicShare.subject',
                ['%entity%' => $this->translator->trans('share.result')]),
            $this->translator->trans('share.email.dynamicShare.body',
                [
                    '%entity%' => $this->translator->trans('share.result'),
                    '%shareLink%' => $shareLink,
                ]
            ),
            'text/html'
        );

        $message->setFrom([getenv('MAILER_DEFAULT_FROM_ADDRESS') => getenv('MAILER_DEFAULT_FROM_SENDER')]);

        foreach ($emails as $email) {
            $message->setTo($email);
            $this->mailer->send($message);
        }
    }

    public function sendAnalysisDynamicShareMail($shareLink, $emails)
    {
        $message = new \Swift_Message(
            $this->translator->trans('share.email.dynamicShare.subject',
                ['%entity%' => $this->translator->trans('share.analysis')]),
            $this->translator->trans('share.email.dynamicShare.body',
                [
                    '%entity%' => $this->translator->trans('share.analysis'),
                    '%shareLink%' => $shareLink,
                ]
            ),
            'text/html'
        );

        $message->setFrom([getenv('MAILER_DEFAULT_FROM_ADDRESS') => getenv('MAILER_DEFAULT_FROM_SENDER')]);

        foreach ($emails as $email) {
            $message->setTo($email);
            $this->mailer->send($message);
        }
    }
}
