<?php

namespace App;

class Parser
{
    private $mail;

    public function __construct(string $mail)
    {
        $dom = new \DomDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $mail);
        $this->mail = new \DOMXpath($dom);
    }

    public function getCheckInDate(): ?string
    {
        $elements = $this->mail->query('//table/tr/td[.="Check-in:"]/following-sibling::td/b');
        return $elements->length ? $elements[0]->nodeValue : null;
    }

    public function getCheckOutDate(): ?string
    {
        $elements = $this->mail->query('//table/tr/td[.="Check-out:"]/following-sibling::td/b');
        return $elements->length ? $elements[0]->nodeValue : null;
    }

    public function getGuestName(): ?string
    {
        $elements = $this->mail->query('//table/tr/td[.="Booked for:"]/following-sibling::td/b');
        return $elements->length ? $elements[0]->nodeValue : null;
    }

    public function getPhone(): ?string
    {
        $elements = $this->mail->query('//table/tr/td[.="Phone:"]/following-sibling::td/b');
        return $elements->length ? $elements[0]->nodeValue : null;
    }

    public function getEmail(): ?string
    {
        $elements = $this->mail->query('//table/tr/td[.="E-mail:"]/following-sibling::td/b');
        return $elements->length ? $elements[0]->nodeValue : null;
    }

    public function getReason(): ?string
    {
        $elements = $this->mail->query('//table/tr/td[.="Reason:"]/following-sibling::td');
        return $elements->length ? $elements[0]->nodeValue : null;
    }
}
