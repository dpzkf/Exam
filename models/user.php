<?php

class User {
    private int $id;
    private string $email;
    private string $password;
    private bool $is_teacher;

    public function __construct(int $id, string $email, string $password, bool $is_teacher)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->is_teacher = $is_teacher;
    }

    public function GetID() {
        return $this->id;
    }
    public function GetEmail() {
        return $this->email;
    }
    public function GetPassword() {
        return $this->password;
    }
    public function IsTeacher() {
        return $this->is_teacher;
    }

    public function SetID(int $id) {
        $this->id = id;
    }
    public function SetEmail(string $email) {
        $this->email = email;
    }
    public function SetPassword(string $email) {
        $this->password = password;
    }
    public function SetIsTeacher(bool $is_teacher) {
        $this->is_teacher = $is_teacher;
    }
}
