<?php

namespace App;

class Parser
{
    private $mail;

    public function __construct(string $mail)
    {
        $dom = new \DomDocument();
        $dom->loadHTML($mail);
        $this->mail = new \DOMXpath($dom);
    }

    public function getCheckInDate(): ?string
    {
        $elements = $this->mail->query('//table/tr[6]/td[2]/b');

        return $elements ? $elements[0]->nodeValue : null;
    }

    public function getCheckOutDate(): ?string
    {
        $elements = $this->mail->query('.//table/tr[7]/td[2]/b');
        return $elements ? $elements[0]->nodeValue : null;
    }

    public function getGuestName(): ?string
    {
        $elements = $this->mail->query('//table/tr[13]/td[2]/b');

        return $elements ? $elements[0]->nodeValue : null;
    }

    public function getPhone(): ?string
    {
        $elements = $this->mail->query('//table/tr[14]/td[2]/b');

        return $elements ? $elements[0]->nodeValue : null;
    }
}
