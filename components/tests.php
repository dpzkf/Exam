<?php

namespace Components\Tests;

class Tests {
    private \mysqli $mysqli;

    public function __construct(\mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function CreateLibrary(\Library $library) {
        $library_name = $this->mysqli->real_escape_string($library->GetTitle());

        $this->mysqli->query("INSERT INTO `libraries` (title) VALUES ('$library_name');");

        return $this->mysqli->errno == 0;
    }

    public function GetLibraries() : array {
        $libraries = [];

        $query = $this->mysqli->query("SELECT * FROM `libraries`;");
        while($row = $query->fetch_assoc()) {
            $libraries[] = new \Library(
                (int)$row['id'],
                $row['title'],
                $this->GetTasksByLibraryID((int)$row['id'])
            );
        }

        return $libraries;
    }


    public function GetLibraryByID(int $id) : ?\Library {
        $query = $this->mysqli->query("SELECT * FROM `libraries` WHERE `id`='$id';");

        while($row = $query->fetch_assoc()) {
            return new \Library((int) $row['id'], $row['title'], $this->GetTasksByLibraryID((int)$row['id']));
        }

        return null;
    }

    public function CreateTask(\Task $task) {
        $library_id = $task->GetLibraryID();
        $title = $this->mysqli->real_escape_string($task->GetTitle());
        $question = $this->mysqli->real_escape_string($task->GetQuestion());
        $expected_output = $this->mysqli->real_escape_string($task->GetExpectedOutput());
        $time_limit = $task->GetTimeLimit();

        $this->mysqli->query("INSERT INTO `tasks` (id_library, title, question, expected_output, time_limit) VALUES (
                                                                                       '$library_id',
                                                                                       '$title',
                                                                                       '$question',
                                                                                       '$expected_output',
                                                                                       '$time_limit'
                                                                                       );");

        error_log($this->mysqli->error);

        return $this->mysqli->errno == 0;
    }

    public function GetTaskByID(int $id) : ?\Task {
        $query = $this->mysqli->query("SELECT * FROM `tasks` WHERE `id`='$id';");

        while($row = $query->fetch_assoc()) {
            return new \Task(
                (int)$row['id'],
                (int)$row['id_library'],
                $row['title'],
                $row['question'],
                $row['expected_output'],
                $row['time_limit'],
            );
        }

        return null;
    }

    public function GetTasksByLibraryID(int $id) : array {
        $tasks = [];

        $query = $this->mysqli->query("SELECT * FROM `tasks` WHERE `id_library`='$id';");
        while($row = $query->fetch_assoc()) {
            $tasks[] = new \Task(
                (int)$row['id'],
                (int)$row['id_library'],
                $row['title'],
                $row['question'],
                $row['expected_output'],
                $row['time_limit'],
            );
        }

        return $tasks;
    }

    public function UpdateLibrary(\Library $library, bool $with_tasks = false) {
        if($with_tasks)
            throw new \Exception("Unsupported operation");

        $id = $library->GetID();
        $title = $this->mysqli->real_escape_string($library->GetTitle());

        $this->mysqli->query("UPDATE `libraries` SET `title`='$title' WHERE `id`='$id';");
        // return $this->mysqli->affected_rows != 0;
        return $this->mysqli->errno == 0;
    }

    public function DeleteLibrary(\Library $library, bool $with_tasks = false) {
        $id = $library->GetID();

        if($with_tasks) {
            $this->mysqli->query("DELETE FROM `tasks` WHERE `id_library`='$id';");
            if($this->mysqli->errno != 0) return false;
        }

        $this->mysqli->query("DELETE FROM `libraries` WHERE `id`='$id';");
        return $this->mysqli->errno == 0;
    }

    public function UpdateTask(\Task $task) {
        $id = $task->GetID();
        $title = $this->mysqli->real_escape_string($task->GetTitle());
        $question = $this->mysqli->real_escape_string($task->GetQuestion());
        $expected_output = $this->mysqli->real_escape_string($task->GetExpectedOutput());

        $this->mysqli->query(
            "UPDATE `tasks` SET 
                       `title`='$title',
                       `question`='$question',
                       `expected_output`='$expected_output'
                    WHERE `id`='$id';"
        );

        error_log("question = $question");

        return $this->mysqli->errno == 0;
    }

    public function DeleteTask(\Task $task) {
        $id = $task->GetID();

        $this->mysqli->query("DELETE FROM `tasks` WHERE `id`='$id';");
        return $this->mysqli->errno == 0;
    }
}
