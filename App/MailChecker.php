<?php

namespace App;

class MailChecker
{
    private const HOST = '{imap.gmail.com:993/imap/ssl}INBOX';
    private const NEW_BOOKING = 'UNSEEN FROM "noreply@reservationsteps.ru" SUBJECT "Новое бронирование"';
    private const CHANGED_BOOKING = 'UNSEEN FROM "noreply@reservationsteps.ru" SUBJECT "Изменение бронирования"';

    private $inbox;

    public function __construct()
    {
        $this->inbox = imap_open(self::HOST, getenv('MAIL_USER'), getenv('MAIL_PASSWORD'));
        if (!$this->inbox) {
            throw new \Exception('Cannot connect to Gmail: ' . imap_last_error());
        }
    }

    public function getMail(): array
    {
        $sinceToday = ' SINCE ' . (new \DateTime('yesterday'))->format('d-M-Y');
        $emails = array_merge(
            imap_search($this->inbox, self::NEW_BOOKING . $sinceToday)  ?: [],
            imap_search($this->inbox, self::CHANGED_BOOKING . $sinceToday) ?: []
        );

        if (!$emails) {
            return [];
        }

        $result = [];
        foreach ($emails as $uid) {
            $structure = imap_fetchstructure($this->inbox, $uid);
            if (!isset($structure->parts, $structure->parts[1])) {
                Logger::error("Can't detect encoding part for $uid mail");
                continue;
            }
            $part = $structure->parts[1];
            $message = imap_fetchbody($this->inbox, $uid, 2, FT_PEEK);
            $result[$uid] = $this->decodeBody($message, $part->encoding);
        }
        return $result;
    }

    public function setSeen(int $uid): void
    {
        imap_setflag_full($this->inbox, $uid, "\\Seen");
    }

    private function decodeBody(string $body, int $encoding): string
    {
        switch ($encoding) {
            case ENC7BIT:
                return $body;
            case ENC8BIT:
                return quoted_printable_decode(imap_8bit($body));
            case ENCBINARY:
                return imap_binary($body);
            case ENCBASE64:
                return imap_base64($body);
            case ENCQUOTEDPRINTABLE:
                return quoted_printable_decode($body);
            case ENCOTHER:
            default:
                return $body;
        }
    }
}
