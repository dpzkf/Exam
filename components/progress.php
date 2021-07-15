<?php

namespace Components\Progress;

use mysqli;
use User;
use Library;
use Components\Tests\Tests;

class Progress {
    private mysqli $mysqli;
    private Tests $tests;

    public function __construct(mysqli $mysqli, Tests $tests) {
        $this->mysqli = $mysqli;
        $this->tests = $tests;
    }

    public function GetForUserByLibrary(User $user, Library $library) : ?\Progress {
        $query = $this->mysqli->query(
            "SELECT * FROM `progress` WHERE `id_user`='".$user->GetID()."' AND `id_library`='".$library->GetID()."';"
        );

        while($row = $query->fetch_assoc()) {
            return new \Progress((int) $row['id'], $user, $library, (int) $row['completed_tasks']);
        }

        return null;
    }

    public function GetForUser(User $user) : array {
        $arr = [];
        $query = $this->mysqli->query("SELECT * FROM `progress` WHERE `id_user`='".$user->GetID()."';");

        while($row = $query->fetch_assoc()) {
            $library = $this->tests->GetLibraryByID((int) $row['id_library']);

            $arr[] = new \Progress((int) $row['id'], $user, $library, (int) $row['completed_tasks']);
        }

        return $arr;
    }

    public function ActivateLibraryForUser(User $user, Library $library) {
        if($this->GetForUserByLibrary($user, $library)) return true;

        $this->mysqli->query("INSERT INTO `progress` (id_user, id_library, completed_tasks) VALUES (
                               '".(int)$user->GetID()."',
                               '".$library->GetID()."',
                               '0'
                               );");

        return $this->mysqli->errno == 0;
    }
}
