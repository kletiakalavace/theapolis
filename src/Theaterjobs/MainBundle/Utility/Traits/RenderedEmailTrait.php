<?php

namespace Theaterjobs\MainBundle\Utility\Traits;


trait RenderedEmailTrait
{
    /**
     * Send email to user from rendered email
     *
     * @param string $renderedTemplate
     * @param array|string $fromEmail
     * @param array|string $toEmail
     * @param array|string $contentType
     */
    public function sendRenderedEmailMessage($renderedTemplate, $fromEmail, $toEmail, $contentType = 'text/html')
    {
        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = array_shift($renderedLines);
        $body = implode("\n", $renderedLines);
        $this->sendEmailMessage($subject, $body, $fromEmail, $toEmail, $contentType);
    }
}