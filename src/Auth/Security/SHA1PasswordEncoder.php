<?php

namespace Auth\Security;

use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class SHA1PasswordEncoder implements PasswordHasherInterface
{
    /**
     * Hashes a plain password.
     *
     * @throws InvalidPasswordException When the plain password is invalid, e.g. excessively long
     */
    public function hash(string $plainPassword): string
    {
        return "{SHA}" . base64_encode(pack("H*", sha1($plainPassword)));
    }

    /**
     * Verifies a plain password against a hash.
     */
    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return $hashedPassword === $this->hash($plainPassword);
    }

    /**
     * Checks if a password hash would benefit from rehashing.
     */
    public function needsRehash(string $hashedPassword): bool
    {
        // TODO: Implement needsRehash() method.
    }
}
