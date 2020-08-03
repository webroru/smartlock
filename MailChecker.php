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
            \addLog('Cannot connect to Gmail: ' . imap_last_error());
            exit;
        }
    }

    public function getMail(): array
    {
        $sinceToday = ' SINCE ' . (new \DateTime())->format('d-M-Y');
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
                \addLog("Error: can't detect encoding part for $uid mail");
                continue;
            }
            $part = $structure->parts[1];
            $message = imap_fetchbody($this->inbox, $uid, 2, FT_PEEK);

            if ($part->encoding === 3) {
                $result[$uid] = imap_base64($message);
            } elseif ($part->encoding === 1) {
                $result[$uid] = imap_8bit($message);
            } else {
                $result[$uid] = imap_qprint($message);
            }
        }
        return $result;
    }

    public function setSeen(int $uid): void
    {
        imap_setflag_full($this->inbox, $uid, "\\Seen");
    }
}
