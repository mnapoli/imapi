# Imapi

Imapi is a high level IMAP API for PHP.

It aims to be different from other implementations:

- **be very high level**: you don't have to know how IMAP works (because IMAP is very ugly)
- take care of related problems like **parse MIME email content** or **sanitize HTML in emails**
- based on Horde's IMAP library rather than on PHP's IMAP extension (explained below)
- be full featured, yet leave the door open for low-level calls to Horde's library for uncovered features
- be maintained (unfortunately IMAP is not a very active topic and many good projects are unfinished or dead)

It is not based on PHP's IMAP extension, but rather on the amazing Horde library. The reason is well explained
on [Horde's library page](http://dev.horde.org/imap_client/):

> Horde/Imap_Client is significantly faster, more feature-rich, and extensible when compared to PHP's imap (c-client) extension.

> Don't be confused: almost every so-called "PHP IMAP Library" out there is nothing more than a thin-wrapper around the imap extension, so NONE of these libraries can fix the basic limitations of that extension.

## Getting started

First, install the project with Composer: `mnapoli/imapi`.

The easy way:

```php
$client = Imapi\Client::connect('imap.host.com', 'user', 'password');
```

If you want full control on the connection, you can use Horde's constructor:

```php
$hordeClient = new Horde_Imap_Client_Socket([
    'username' => $username,
    'password' => $password,
    'hostspec' => $host,
    'port'     => '143',
    'secure'   => 'tls',
]);

$client = new Imapi\Client($hordeClient);
```


## Reading

### Reading the inbox

Fetching all the messages from the inbox:

```php
$emails = $client->getEmails();

foreach ($emails as $email) {
    echo $email->getSubject();
}
```

Yes it's that easy. Emails are objects (`Imapi\Email`) that expose all the information of the email.

If you need to synchronize emails stored locally with the IMAP server, you will probably not want to fetch the emails,
i.e. their content. You can fetch only their ID, which is much faster:

```php
$ids = $client->getEmailIds();

foreach ($ids as $id) {
    if (/* this email needs to be synced */) {
        $email = $client->getEmailFromId($id);
        // ...
    }
}
```

### Advanced queries

Both `getEmails()` and `getEmailIds()` can take an optional `Query` object.

```php
// Read from the `INBOX.Sent` folder
$query = QueryBuilder::create('INBOX.Sent')
    ->youngerThan(3600) // 1 hour
    ->getQuery();

$emails = $client->getEmails($query);
```

### Reading folders

```php
$folders = $client->getFolders();
```


## Operations

### Moving emails

```php
$emailIds = ['123', '456'];

// Moving from the INBOX to the Archive folder
$client->moveEmails($emailIds, 'INBOX', 'Archive');
```

### Deleting emails

"Deleting" means simply moving to the trash folder. Unfortunately, the trash folder is custom to each provider,
so you need to explicitly provide it:

```php
$emailIds = ['123', '456'];

$client->deleteEmails($emailIds, 'Deleted Messages');
```
