<?php

namespace Imapi;

/**
 * Email address associated to a name.
 *
 * Example: "John Doe" <john@example.com>
 */
class EmailAddress
{
    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $name;

    public function __construct($email, $name = null)
    {
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNameOrEmail()
    {
        return $this->name ?: $this->email;
    }

    public function __toString()
    {
        if ($this->name == null) {
            return $this->email;
        }

        return sprintf('"%s" <%s>', $this->name, $this->email);
    }
}
