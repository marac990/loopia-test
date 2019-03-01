<?php

/*
 * © Loopia. All rights reserved.
 */

namespace Loopia\App\Api;

class Credentials implements CredentialsInterface {

	private $username;
	private $password;

	public function __construct(
	    $username,
        $password
    )
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function getUsername( )
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getToken()
    {
        if (false !== $hash = password_hash($this->password, PASSWORD_DEFAULT)) {
            return $this->username . ':' . base64_encode($hash);
        }

        throw new \Exception('Failed creating authentication hash');
    }
}
