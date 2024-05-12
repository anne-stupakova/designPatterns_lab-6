<?php
namespace Memento;

interface PasswordStrategy {
    public function hashPassword($password);
    public function verifyPassword($password, $hashedPassword);
}

class HashedPasswordStrategy implements PasswordStrategy {
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }
}

class ReversiblePasswordStrategy implements PasswordStrategy {
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }
}

class PasswordManager {
    private $strategy;

    public function setStrategy(PasswordStrategy $strategy) {
        $this->strategy = $strategy;
    }

    public function hashPassword($password) {
        return $this->strategy->hashPassword($password);
    }

    public function verifyPassword($password, $hashedPassword) {
        return $this->strategy->verifyPassword($password, $hashedPassword);
    }
}
