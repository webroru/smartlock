<?php

namespace App;

class Parser
{
    private const MONTHS_RU = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
    private const MONTHS_EN = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    private $mail;

    public function __construct(string $mail)
    {
        $dom = new \DomDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $mail);
        $this->mail = new \DOMXpath($dom);
    }

    public function getCheckInDate(): ?string
    {
        $elements = $this->mail->query('//table/tbody/tr/td[normalize-space() = "Даты проживания"]/following-sibling::td');
        if (!$elements->length) {
            return null;
        }

        $value = trim($elements[0]->nodeValue);
        $dateStr = explode(' — ', $value)[0];
        return $this->translateDate($dateStr);
    }

    public function getCheckOutDate(): ?string
    {
        $elements = $this->mail->query('//table/tbody/tr/td[normalize-space() = "Даты проживания"]/following-sibling::td');
        if (!$elements->length) {
            return null;
        }

        $value = trim($elements[0]->nodeValue);
        $dateStr = explode(' — ', $value)[1];
        return $this->translateDate($dateStr);
    }

    public function getGuestName(): ?string
    {
        $elements = $this->mail->query('//table/tbody/tr/td[.="Заказчик"]/following-sibling::td');
        return $elements->length ? $elements[0]->nodeValue : null;
    }

    public function getPhone(): ?string
    {
        $elements = $this->mail->query('//table/tbody/tr/td[.="Телефон"]/following-sibling::td');
        return $elements->length ? $elements[0]->nodeValue : null;
    }

    public function getEmail(): ?string
    {
        $elements = $this->mail->query('//table/tbody/tr/td[.="Эл. почта"]/following-sibling::td');
        return $elements->length ? $elements[0]->nodeValue : null;
    }

    public function isChanged(): bool
    {
        return false;
    }

    private function translateDate(string $date): string
    {
        return str_replace(self::MONTHS_RU, self::MONTHS_EN, mb_strtolower($date, 'UTF-8'));
    }

    public function getOrderId(): ?string
    {
        $elements = $this->mail->query('//table/tbody/tr/td/div/b[2]');
        return $elements->length ? $elements[0]->nodeValue : null;
    }
}
