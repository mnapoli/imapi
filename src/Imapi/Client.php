<?php

namespace Imapi;

use Horde_Imap_Client_Fetch_Query;
use Horde_Imap_Client_Ids;
use Horde_Imap_Client_Search_Query;
use Horde_Imap_Client_Socket;
use Imapi\Query\Query;

/**
 * Client for an IMAP connection.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Client
{
    /**
     * @var Horde_Imap_Client_Socket
     */
    private $hordeClient;

    /**
     * @var EmailFactory
     */
    private $emailFactory;

    public function __construct(Horde_Imap_Client_Socket $hordeClient, EmailFactory $emailFactory = null)
    {
        $this->hordeClient = $hordeClient;
        $this->emailFactory = $emailFactory ?: new EmailFactory();
    }

    /**
     * Connect to a remote IMAP server and return the client instance.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $port
     * @param string $secure
     *
     * @return Client
     */
    public static function connect($host, $username, $password, $port = '143', $secure = 'tls')
    {
        $hordeClient = new Horde_Imap_Client_Socket([
            'username' => $username,
            'password' => $password,
            'hostspec' => $host,
            'port'     => $port,
            'secure'   => $secure,
        ]);

        return new static($hordeClient);
    }

    /**
     * Returns the list of folders in the account.
     *
     * @return string[]
     */
    public function getFolders()
    {
        return array_keys($this->hordeClient->listMailboxes('*'));
    }

    /**
     * Finds the emails matching the query. If $query is null, then it will fetch the emails in the inbox.
     *
     * @param Query $query
     *
     * @return Email[]
     */
    public function getEmails(Query $query = null)
    {
        $hordeQuery = new Horde_Imap_Client_Search_Query();

        if ($query->getYoungerThan() !== null) {
            $hordeQuery->intervalSearch(
                $query->getYoungerThan(),
                Horde_Imap_Client_Search_Query::INTERVAL_YOUNGER
            );
        }

        return $this->searchAndFetch($query->getFolder(), $hordeQuery);
    }

    /**
     * Finds the email Ids matching the query. If $query is null, then it will fetch the email Ids in the inbox.
     *
     * This method is obviously more efficient than getEmails() if you want to synchronize local mails.
     *
     * @param Query $query
     *
     * @return string[]
     */
    public function getEmailIds(Query $query = null)
    {
        $hordeQuery = new Horde_Imap_Client_Search_Query();

        if ($query->getYoungerThan() !== null) {
            $hordeQuery->intervalSearch(
                $query->getYoungerThan(),
                Horde_Imap_Client_Search_Query::INTERVAL_YOUNGER
            );
        }

        return $this->search($query->getFolder(), $hordeQuery);
    }

    /**
     * @param string $id
     * @param string $folder
     * @return Email|null Returns null if the email was not found.
     */
    public function getEmailFromId($id, $folder = 'INBOX')
    {
        $emails = $this->fetchEmails($folder, [ $id ]);

        return (count($emails) > 0) ? $emails[0] : null;
    }

    /**
     * @param string[] $ids
     * @param string   $folder
     * @return Email[]
     */
    public function getEmailsFromId(array $ids, $folder = 'INBOX')
    {
        return $this->fetchEmails($folder, $ids);
    }

    /**
     * Move emails from one folder to another.
     *
     * @param int[]  $ids
     * @param string $from
     * @param string $to
     */
    public function moveEmails(array $ids, $from, $to)
    {
        $this->hordeClient->copy((string) $from, (string) $to, [
            'ids' => new Horde_Imap_Client_Ids($ids),
            'move' => true,
        ]);
    }

    /**
     * Delete emails by moving them to the trash folder.
     *
     * @param int[]  $ids
     * @param string $trashFolder Trash folder. There is no standard default, it can be 'Deleted Messages', 'Trash'…
     * @param string $fromFolder  Folder from which the email Ids come from.
     */
    public function deleteEmails(array $ids, $trashFolder, $fromFolder = 'INBOX')
    {
        $this->moveEmails($ids, $fromFolder, $trashFolder);
    }

    /**
     * @param string                         $folder
     * @param Horde_Imap_Client_Search_Query $query
     * @return Email[]
     */
    private function searchAndFetch($folder, Horde_Imap_Client_Search_Query $query)
    {
        return $this->fetchEmails($folder, $this->search($folder, $query));
    }

    /**
     * @param string                         $folder
     * @param Horde_Imap_Client_Search_Query $query
     * @return int[]
     */
    private function search($folder, Horde_Imap_Client_Search_Query $query)
    {
        $results = $this->hordeClient->search($folder, $query);
        /** @var Horde_Imap_Client_Ids $results */
        $results = $results['match'];

        return $results->ids;
    }

    private function fetchEmails($folder, array $ids)
    {
        $query = new Horde_Imap_Client_Fetch_Query();
        $query->envelope();
        $query->fullText([
            'peek' => true,
        ]);
        $query->flags();

        $hordeEmails = $this->hordeClient->fetch($folder, $query, [
            'ids' => new Horde_Imap_Client_Ids($ids)
        ]);

        return $this->emailFactory->createMany($folder, $hordeEmails);
    }

    /**
     * @return Horde_Imap_Client_Socket
     */
    public function getHordeClient()
    {
        return $this->hordeClient;
    }
}
