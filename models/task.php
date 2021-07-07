<?php

class Task implements JsonSerializable {
    private int $id;
    private int $id_library;
    private string $title;
    private string $question;
    private string $expected_output;
    private int $time_limit;

    public function __construct(int $id, int $id_library, string $title, string $question, string $expected_output, int $time_limit)
    {
        $this->id = $id;
        $this->id_library = $id_library;
        $this->title = $title;
        $this->question = $question;
        $this->expected_output = $expected_output;
        $this->time_limit = $time_limit;
    }

    public function GetID() {
        return $this->id;
    }
    public function GetLibraryID() {
        return $this->id_library;
    }
    public function GetTitle() {
        return $this->title;
    }
    public function GetQuestion() {
        return $this->question;
    }
    public function GetExpectedOutput() {
        return $this->expected_output;
    }
    public function GetTimeLimit() {
        return $this->time_limit;
    }

    public function SetID(int $id) {
        $this->id = $id;
    }
    public function SetLibraryID(int $id_library) {
        $this->id_library = $id_library;
    }
    public function SetTitle(string $title) {
        $this->title = $title;
    }
    public function SetQuestion(string $question) {
        $this->question = $question;
    }
    public function SetExpectedOutput(string $expected_output) {
        $this->expected_output = $expected_output;
    }
    public function SetTimeLimit(int $time_limit) {
        $this->time_limit = $time_limit;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }
}
