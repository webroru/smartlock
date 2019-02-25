<?php

namespace App;

class Parser
{
    private $mail;

    public function __construct(string $mail)
    {
        //$this->mail = new \SimpleXMLElement($mail);
        $dom = new \DomDocument();
        $dom->loadHTML($mail);
        $this->mail = new \DOMXpath($dom);
    }

    public function getCheckInDate()
    {
        $elements = $this->mail->query('//table/tbody/tr[6]/td[2]/b');

        return $elements ? $elements[0]->nodeValue : null;

    }

    public function getCheckOutDate()
    {
        $elements = $this->mail->query('//table/tbody/tr[7]/td[2]/b');

        return $elements ? $elements[0]->nodeValue : null;
    }

    public function getBookingNumber()
    {
        $elements = $this->mail->query('//table/tbody/tr[5]/td[2]');
        if (!$elements) {
            return null;
        }
        return preg_replace('/[\D]+/', '', $elements[0]->nodeValue);
    }

    public function getGuestName()
    {
        $elements = $this->mail->query('//table/tbody/tr[14]/td[2]/b');

        return $elements ? $elements[0]->nodeValue : null;
    }

    public function getRoomNumber()
    {
        return 1;
    }
}
