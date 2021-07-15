<?php

use \Components\Progress\Progress;
use \Components\Tests\Tests;

class CGSideMenuProgress extends ContentGenerator {
    private Progress $progress;
    private Tests $tests;
    private User $user;

    public function __construct(Progress $progress, Tests $tests, User $user) {
        $this->progress = $progress;
        $this->tests = $tests;
        $this->user = $user;
    }

    public function GenerateHTML(): string {
        $result = '';

        $libraries = $this->tests->GetLibraries();
        foreach ($libraries as $library) {
            $id = $library->GetID();
            $title = $library->GetTitle();

            $additional_class = $this->progress->GetForUserByLibrary($this->user, $library) ? "active" : "";

            if(!empty($additional_class)) {
                $additional_class = " class=\"$additional_class\"";
            }

            $result .= '<li'.$additional_class.'>
                <a href="#library_select" data-library-id="'.$id.'">
                    <img src="/images/mdi_archive.svg" alt="Associative icon" class="va-middle"><span class="va-middle">'.$title.'</span>
                </a>
        </li>';
        }

        return $result;
    }
}
