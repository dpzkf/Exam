<?php

class CGSideMenu extends ContentGenerator {
    private array $libraries;

    public function __construct(array $libraries) {
        $this->libraries = $libraries;
    }

    public function GenerateHTML(): string {
        $result = '';

        foreach ($this->libraries as $library) {
            $id = $library->GetID();
            $title = $library->GetTitle();

            $tasks = '';
            foreach ($library->GetTasks() as $task) {
                $task_id = $task->GetID();
                $task_title = $task->GetTitle();

                $tasks .= '<li><a href="#task_edit" data-task-id="'.$task_id.'">'.$task_id.'. '.$task_title.'</a></li>';
            }

            if(strlen($tasks) > 0)
                $tasks = '<ul class="library-items">
            <li><a href="#new_task">
                <img src="/images/mdi_plus.svg" alt="Associative icon" class="va-middle"><span class="va-middle">New task</span>
            </a></li>'.$tasks.'
            </ul>';

            $result .= '<li>
                <a href="#library_select" data-library-id="'.$id.'">
                    <img src="/images/mdi_archive.svg" alt="Associative icon" class="va-middle"><span class="va-middle">'.$title.'</span>
                </a>
                '.$tasks.'
        </li>';
        }

        return $result;
    }
}
