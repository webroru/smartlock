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

    public function getCheckInDate()
    {
        $elements = $this->mail->query('//table/tr[6]/td[2]/b');

        return $elements ? $elements[0]->nodeValue : null;
    }

    public function getCheckOutDate()
    {
        $elements = $this->mail->query('.//table/tr[7]/td[2]/b');
        return $elements ? $elements[0]->nodeValue : null;
    }

    public function getBookingNumber()
    {
        $elements = $this->mail->query('//table//tr[5]/td[2]');
        if (!$elements) {
            return null;
        }
        preg_match('/new ([\d]+)/', $elements[0]->nodeValue, $matches);
        //return preg_replace('/[\D]+/', '', $elements[0]->nodeValue);
        return $matches[1];
    }

    public function getGuestName()
    {
        $elements = $this->mail->query('//table/tr[13]/td[2]/b');

        return $elements ? $elements[0]->nodeValue : null;
    }
}
