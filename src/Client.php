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

      /**
     * 
     * @var boolean
     */
    private $peek;
    
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
     * Finds the emails matching the query. If $query is null, then it will fetch the emails in the inbox.
     * @param boolean $peek sets the peek option, "false" fetched emails' state will be set to seen "true" no change of state default is true
     * @return Email[]
     */
    public function getEmails(Query $query = null, bool $peek = true): array 
    {
        $hordeQuery = new Horde_Imap_Client_Search_Query();

        $query = $query ?: new Query;

        if ($query->getYoungerThan() !== null) {
            $hordeQuery->intervalSearch(
                    $query->getYoungerThan(), 
                    Horde_Imap_Client_Search_Query::INTERVAL_YOUNGER
            );
        }
        $this->setPeek($peek);
        $this->setFlags($hordeQuery, $query);
        return $this->searchAndFetch($query->getFolder(), $hordeQuery);
    }

    /**
     * Finds the email Ids matching the query. If $query is null, then it will fetch the email Ids in the inbox.
     *
     * This method is obviously more efficient than getEmails() if you want to synchronize local mails.
     * 
     * @param boolean $peek sets the peek option, "false" fetched emails' state will be set to seen "true" no change of state default is true
     * @return string[]
     */
    public function getEmailIds(Query $query = null, bool $peek = true): array 
    {
        $hordeQuery = new Horde_Imap_Client_Search_Query();

        $query = $query ?: new Query;

        if ($query->getYoungerThan() !== null) {
            $hordeQuery->intervalSearch(
                    $query->getYoungerThan(), 
                    Horde_Imap_Client_Search_Query::INTERVAL_YOUNGER
            );
        }

        $this->setPeek($peek);
        $this->setFlags($hordeQuery, $query);
        return $this->search($query->getFolder(), $hordeQuery);
    }

    /**
     * @param String $folder the folder to get email from
     * @param boolean $peek sets the peek option, "false" fetched emails' state will be set to seen "true" no change of state default is true
     * @return Email|null Returns null if the email was not found.
     */
    public function getEmailFromId(string $id,  string $folder = 'INBOX', bool $peek = true) 
    {
        $this->setPeek($peek);
        $emails = $this->fetchEmails($folder, [$id]);

        return (count($emails) > 0) ? $emails[0] : null;
    }

    /**
     * @param string[] $ids
     * @param String $folder the folder to get emails from
     * @param boolean $peek sets the peek option, "false" fetched emails' state will be set to seen "true" no change of state default is true
     * @return Email[]
     */
    public function getEmailsFromId(array $ids, string $folder = 'INBOX', bool $peek = true) : array 
    {
        $this->setPeek($peek);
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
     * @return Email[]
     */
    private function searchAndFetch(string $folder, Horde_Imap_Client_Search_Query $query) : array
    {
        return $this->fetchEmails($folder, $this->search($folder, $query));
    }

    /**
     * @return int[]
     */
    private function search(string $folder, Horde_Imap_Client_Search_Query $query) : array
    {
        $results = $this->hordeClient->search($folder, $query);
        /** @var Horde_Imap_Client_Ids $results */
        $results = $results['match'];

        return $results->ids;
    }

    private function fetchEmails(string $folder, array $ids) : array
    {
        $query = new Horde_Imap_Client_Fetch_Query();
        $query->envelope();
        $query->fullText([
            'peek' => $this->getPeek(),
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
    
    private function setFlags(Horde_Imap_Client_Search_Query $hordeQuery,Query $query){
        if(count($query->getFlags()) > 0){
            foreach ($query->getFlags() as $key => $value){
                switch ($key){
                    case Query::FLAG_ANSWERED:
                        $hordeQuery->flag(\Horde_Imap_Client::FLAG_ANSWERED, $value);
                        break;
                    case Query::FLAG_DELETED:
                        $hordeQuery->flag(\Horde_Imap_Client::FLAG_DELETED, $value);
                        break;
                    case Query::FLAG_DRAFT:
                        $hordeQuery->flag(\Horde_Imap_Client::FLAG_DRAFT, $value);
                        break;
                    case Query::FLAG_FLAGED:
                        $hordeQuery->flag(\Horde_Imap_Client::FLAG_FLAGGED, $value);
                        break;
                    case Query::FLAG_RECENT:
                        $hordeQuery->flag(\Horde_Imap_Client::FLAG_RECENT, $value);
                        break;
                    case Query::FLAG_SEEN:
                        $hordeQuery->flag(\Horde_Imap_Client::FLAG_SEEN, $value);
                        break;
                }
            }
        }
    }
    /**
     * 
     * @param boolean $peek
     */
    public function setPeek($peek) 
    {
        $this->peek = $peek;
    }
    
    public function getPeek()
    {
        return $this->peek;
    }
}
