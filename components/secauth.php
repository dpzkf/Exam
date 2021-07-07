<?php

namespace Components\Auth;

class SecAuth {
    private \mysqli $mysqli;

    public function __construct(\mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function SearchKey(string $key) : ?\SecAuthKey {
        $key = $this->mysqli->real_escape_string($key);

        $query = $this->mysqli->query("SELECT * FROM `sec_auth` WHERE `security_key`='$key';");

        while($row = $query->fetch_assoc()) {
            return new \SecAuthKey((int) $row['id'], (int) $row['id_user'], $row['security_key'], $row['ip'], (int) $row['date']);
        }

        return null;
    }

    public function CreateNewKey(\User $user, string $salt) {
        $email = $this->mysqli->real_escape_string($user->GetEmail());
        $password = $this->mysqli->real_escape_string($user->GetPassword());

        $user_id = $user->GetID();
        $key = $this::GenerateKeyForData($salt, $email.$password);
        $ip = $_SERVER['REMOTE_ADDR'];
        $timestamp = time();

        $this->mysqli->query(
            "INSERT INTO `sec_auth` (`id_user`, `security_key`, `ip`, `date`) VALUES ('$user_id', '$key', '$ip', '$timestamp');"
        );

        if($this->mysqli->affected_rows != 0) {
            return new \SecAuthKey($this->mysqli->insert_id, $user_id, $key, $ip, $timestamp);
        }

        return false;
    }

    public function RemoveKey(\SecAuthKey $key) {
        $id = $key->GetID();
        $this->mysqli->query("DELETE FROM `sec_auth` WHERE `id`='$id';");

        return $this->mysqli->errno == 0;
    }

    public static function GenerateKeyForData(string $salt, $data) {
        $key = '';
        $hash = hash('sha256', $salt.$data);

        $key .= RandomSequence(5, $hash).'-';
        $key .= RandomSequence(8, $hash).'-';
        $key .= RandomSequence(2, $hash);

        $key .= ':'.rand(1000, 1000*strlen($key));

        return $key; // "xxxxx[5]-xxxxxxxx[8]-xx[2]:random_number[4]"
    }
}