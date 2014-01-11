<?php

namespace Service\Queue;

use Zmqueue\Request;

class Email extends Base
{
    const MAIL_TEMPLATE = 'template';
    const MAIL_TITLE = 'title';
    const MAIL_VARIABLES = 'variables';
    const MAIL_RECIPIENT = 'recipient';

    /**
     * Queue an email for html file
     * @param string $recipient
     * @param string $title
     * @param string $templateName
     * @param array $variables
     * @return mixed|null|string
     */
    public function queueMail($recipient, $title, $templateName, array $variables)
    {
        $jsonData[self::MAIL_RECIPIENT] = $recipient;
        $jsonData[self::MAIL_TITLE] = $title;
        $jsonData[self::MAIL_TEMPLATE] = $templateName;
        $jsonData[self::MAIL_VARIABLES] = $variables;

        $request = new Request('Email', $jsonData);
        $result = $this->queue->run($request);

        return $result;
    }

    /**
     * Get email content for html file
     * @param $templateName
     * @param array $variables
     * @return mixed|null|string
     */
    public static function getMailContent($templateName, array $variables) {
        $templateName = strtolower($templateName);

        $file = __DIR__.'/../../../resources/email/' . $templateName . '.html';
        if (!file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);

        foreach ($variables as $key => $value) {
            if (stripos($key, 'HTML_INCLUDE') === false) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            $content = str_replace($key, $value, $content);
        }

        return $content;
    }

} 