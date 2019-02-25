<?php

namespace App;

class MailChecker
{
    const HOST = '{imap.gmail.com:993/imap/ssl}INBOX';
    const USER = 'webroru@gmail.com';
    const PASSWORD = 'Jopajopa';
    const CRITERIA = 'UNSEEN FROM "webroru@gmail.com"';

    private $inbox;

    public function __construct()
    {
        $this->inbox = imap_open(self::HOST, self::USER, self::PASSWORD);
        if (!$this->inbox) {
            die('Cannot connect to Gmail: ' . imap_last_error());
        }
    }

    public function getMail(): array
    {
        $emails = imap_search($this->inbox, self::CRITERIA);
        $result = [];
        foreach ($emails as $uid) {
            $structure = imap_fetchstructure($this->inbox, $uid);
            if (!isset($structure->parts, $structure->parts[1])) {
                echo "Error: can't detect encoding part for $uid mail\n";
                continue;
            }
            $part = $structure->parts[1];
            $message = imap_fetchbody($this->inbox, $uid, 2);

            if ($part->encoding === 3) {
                $result[] = imap_base64($message);
            } elseif ($part->encoding === 1) {
                $result[] = imap_8bit($message);
            } else {
                $result[] = imap_qprint($message);
            }
        }
        return $result;
    }
}
