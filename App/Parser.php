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
        $elements = $this->mail->query('//table/tbody/tr/td/b[.="Check-in:"]/../following-sibling::td');
        return $elements->length ? $elements[0]->nodeValue : null;
    }

    public function getCheckOutDate(): ?string
    {
        $elements = $this->mail->query('//table/tbody/tr/td/b[.="Check-out:"]/../following-sibling::td');
        return $elements->length ? $elements[0]->nodeValue : null;
    }

    public function getGuestName(): ?string
    {
        $elements = $this->mail->query('//table/tbody/tr/td/b[.="Booked for:"]/../following-sibling::td');
        return $elements->length ? $elements[0]->nodeValue : null;
    }

    public function getPhone(): ?string
    {
        $elements = $this->mail->query('//table/tbody/tr/td/b[.="Phone:"]/../following-sibling::td');
        return $elements->length ? $elements[0]->nodeValue : null;
    }

    public function getEmail(): ?string
    {
        $elements = $this->mail->query('//table/tbody/tr/td/b[.="E-mail:"]/../following-sibling::td');
        return $elements->length ? $elements[0]->nodeValue : null;
    }

    public function isChanged(): bool
    {
        $elements = $this->mail->query('//table/tbody/tr/td/div/b[contains(text(), "Booking changed")]');
        return (bool) $elements->length;
    }
}
