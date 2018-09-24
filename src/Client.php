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
     */
    public static function connect(
        string $host,
        string $username,
        string $password,
        string $port = '143',
        string $secure = 'tls'
    ) : self
    {
        $hordeClient = new Horde_Imap_Client_Socket([
            'username' => $username,
            'password' => $password,
            'hostspec' => $host,
            'port' => $port,
            'secure' => $secure,
        ]);

        return new static($hordeClient);
    }

    /**
     * Returns the list of folders in the account.
     *
     * @return string[]
     */
    public function getFolders() : array
    {
        return array_keys($this->hordeClient->listMailboxes('*'));
    }
    
    /**
     * Finds the email Ids matching the query. If $query is null, then it will fetch the email Ids in the inbox.
     *
     * This method is obviously more efficient than getEmails() if you want to synchronize local mails.
     *
     * @return string[]
     */
    public function getEmailIds(Query $query = null) : array
    {
        $hordeQuery = new Horde_Imap_Client_Search_Query();

        $query = $query ?: new Query;

        if ($query->getYoungerThan() !== null) {
            $hordeQuery->intervalSearch(
                $query->getYoungerThan(),
                Horde_Imap_Client_Search_Query::INTERVAL_YOUNGER
            );
        }

        $this->setFlags($hordeQuery, $query);
        return $this->search($query->getFolder(), $hordeQuery);
    }

    /**
     * Finds the emails matching the query. If $query is null, then it will fetch the emails in the inbox.
     *
     * @return Email[]
     */
    public function getEmails(Query $query = null) : array
    {
        $ids = $this->getEmailIds($query);
        return $this->fetchEmails($query->getFolder(), $ids);
    }

    /**
     * @return Email|null Returns null if the email was not found.
     */
    public function getEmailFromId(string $id, string $folder = 'INBOX')
    {
        $emails = $this->fetchEmails($folder, [$id]);
        return (count($emails) > 0) ? $emails[0] : null;
    }

    /**
     * @param string[] $ids
     * @return Email[]
     */
    public function getEmailsFromId(array $ids, string $folder = 'INBOX') : array
    {
        return $this->fetchEmails($folder, $ids);
    }

    /**
     * Move emails from one folder to another.
     *
     * @param int[] $ids
     */
    public function moveEmails(array $ids, string $from, string $to)
    {
        $this->hordeClient->copy((string) $from, (string) $to, [
            'ids' => new Horde_Imap_Client_Ids($ids),
            'move' => true,
        ]);
    }

    /**
     * Delete emails by moving them to the trash folder.
     *
     * @param int[] $ids
     * @param string $trashFolder Trash folder. There is no standard default, it can be 'Deleted Messages', 'Trash'…
     * @param string $fromFolder Folder from which the email Ids come from.
     */
    public function deleteEmails(array $ids, $trashFolder, $fromFolder = 'INBOX')
    {
        $this->moveEmails($ids, $fromFolder, $trashFolder);
    }

    /**
     * @return int[]
     */
    private function search(string $folder, Horde_Imap_Client_Search_Query $query) : array
    {
        $results = $this->hordeClient->search($folder, $query);
        /** @var Horde_Imap_Client_Ids $ob */
        $ob = $results['match'];
        return $ob->ids;
    }

    private function fetchEmails(string $folder, array $ids) : array
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

    public function getHordeClient() : Horde_Imap_Client_Socket
    {
        return $this->hordeClient;
    }
    
    private function setFlags(Horde_Imap_Client_Search_Query $hordeQuery, Query $query){
        if(count($query->getFlags()) > 0){
            foreach ($query->getFlags() as $key => $value){
                $hordeQuery->flag($key, $value);
            }
        }
    }
}
