<?php

class SecAuthKey {
    private int $id;
    private int $id_user;
    private string $key;
    private string $ip;
    private int $date;

    public function __construct(int $id, int $id_user, string $key, string $ip, int $date) {
        $this->id = $id;
        $this->id_user = $id_user;
        $this->key = $key;
        $this->ip = $ip;
        $this->date = $date;
    }

    public function GetID() {
        return $this->id;
    }
    public function GetUserID() {
        return $this->id_user;
    }
    public function GetKey() {
        return $this->key;
    }
    public function GetIP() {
        return $this->ip;
    }
    public function GetDate() {
        return $this->date;
    }

    public function SetID(int $id) {
        $this->id = $id;
    }
    public function SetUserID(int $user_id) {
        $this->id_user = $user_id;
    }
    public function SetKey(string $key) {
        $this->key = $key;
    }
    public function SetIP(string $ip) {
        $this->ip = $ip;
    }
    public function SetDate(int $date) {
        $this->date = $date;
    }
}
