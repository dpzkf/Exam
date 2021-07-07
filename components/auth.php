<?php

namespace Components\Auth;

class Auth {
    private \mysqli $mysqli;
    private string $password_salt;
    private string $sec_auth_salt;

    public function __construct(\mysqli $mysqli, string $password_salt, string $sec_auth_salt)
    {
        $this->mysqli = $mysqli;
        $this->password_salt = $password_salt;
        $this->sec_auth_salt = $sec_auth_salt;
    }

    public function HashPassword(string $password) {
        return hash('sha1', $this->password_salt.$password);
    }

    public function SearchUser(string $email) : ?\User {
        $email = $this->mysqli->real_escape_string($email);

        $query = $this->mysqli->query("SELECT * FROM `users` WHERE `email`='$email';");

        while($row = $query->fetch_assoc()) {
            return new \User((int) $row['id'], $row['email'], $row['password'], (bool) $row['is_teacher']);
        }

        return null;
    }

    public function SearchUserByID(int $id) : ?\User {
        $query = $this->mysqli->query("SELECT * FROM `users` WHERE `id`='$id';");

        while($row = $query->fetch_assoc()) {
            return new \User((int) $row['id'], $row['email'], $row['password'], (bool) $row['is_teacher']);
        }

        return null;
    }

    public function Register(string $email, string $password, int $is_teacher) {
        $email = $this->mysqli->real_escape_string($email);
        $password = $this->HashPassword($password);

        if($user = $this->SearchUser($email))
            return false;

        $this->mysqli->query("INSERT INTO `users` (`email`, `password`, `is_teacher`) VALUES ('$email', '$password', '$is_teacher');");

        if($this->mysqli->affected_rows != 0) {
            $user = new \User($this->mysqli->insert_id, $email, $password, $is_teacher);

            $secauth = new SecAuth($this->mysqli);
            return $secauth->CreateNewKey($user, $this->sec_auth_salt);
        }

        return false;
    }

    public function Login(string $email, string $password) {
        $password = $this->HashPassword($password);

        if($user = $this->SearchUser($email)) {
            if($user->GetPassword() == $password) {
                $secauth = new SecAuth($this->mysqli);
                return $secauth->CreateNewKey($user, $this->sec_auth_salt);
            }
        }

        return false;
    }

    public function SetTeacherStatus(string $email, bool $is_teacher) {
        $email = $this->mysqli->real_escape_string($email);
        $this->mysqli->query("UPDATE `users` SET `is_teacher`= '$is_teacher' WHERE `email`='$email';");

        return $this->mysqli->errno == 0;
    }
}
