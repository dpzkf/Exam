<?php

class Progress {
    private int $id;
    private User $user;
    private Library $library;
    private int $completed_tasks;

    public function __construct(int $id, User $user, Library $library, int $completed_tasks) {
        $this->id = $id;
        $this->user = $user;
        $this->library = $library;
        $this->completed_tasks = $completed_tasks;
    }

    public function GetID() {
        return $this->id;
    }

    public function GetUser() {
        return $this->user;
    }

    public function GetLibrary() {
        return $this->library;
    }

    public function GetCompletedTasks() {
        return $this->completed_tasks;
    }
}
