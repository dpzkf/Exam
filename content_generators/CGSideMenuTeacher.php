<?php

class CGSideMenuTeacher extends ContentGenerator {
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

            $raw_tasks = $library->GetTasks();
            for($i = 0; $i < count($raw_tasks); $i++) {
                // foreach ($library->GetTasks() as $task) {
                $task_id = $raw_tasks[$i]->GetID();
                $task_title = $raw_tasks[$i]->GetTitle();

                $tasks .= '<li><a href="#task_edit" data-task-id="'.$task_id.'">'.($i+1).'. '.$task_title.'</a></li>';
            }

            $tasks = '<ul class="library-items">
            <li><a href="#new_task" data-library-id="'.$id.'">
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
