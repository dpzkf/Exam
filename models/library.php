<?php

class Library implements JsonSerializable {
    private int $id;
    private string $title;
    private array $tasks;

    public function __construct(int $id, string $title, array $tasks)
    {
        $this->id = $id;
        $this->title = $title;
        $this->tasks = $tasks;
    }

    public function GetID() {
        return $this->id;
    }
    public function GetTitle() {
        return $this->title;
    }
    public function GetTasks() {
        return $this->tasks;
    }

    public function SetID(int $id) {
        $this->id = $id;
    }
    public function SetTitle(string $title) {
        $this->title = $title;
    }
    public function SetTasks(array $tasks) {
        $this->tasks = $tasks;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }
}
