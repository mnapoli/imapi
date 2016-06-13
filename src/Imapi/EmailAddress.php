<?php

namespace Imapi;

/**
 * Email address associated to a name.
 *
 * Example: "John Doe" <john@example.com>
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class EmailAddress
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string|null
     */
    private $name;

    public function __construct(string $email, string $name = null)
    {
        $this->email = $email;
        $this->name = $name;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    public function getNameOrEmail() : string
    {
        return $this->name ?: $this->email;
    }

    public function __toString() : string
    {
        if ($this->name == null) {
            return $this->email;
        }

        return sprintf('"%s" <%s>', $this->name, $this->email);
    }
}
