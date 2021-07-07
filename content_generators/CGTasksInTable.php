<?php

class CGTasksInTable extends ContentGenerator {
    private array $tasks;

    public function __construct(array $tasks) {
        $this->tasks = $tasks;
    }

    public function GenerateHTML() : string {
        if(empty($this->tasks)) return "";

        $table = file_get_contents($this->GetViewPath());

        $rows = '';
        foreach ($this->tasks as $task) {
            $item = file_get_contents($this->GetViewFolder().$this->GetViewName().'.item.html');

            $item = preg_replace("/(%TASK_ID%)/", $task->GetID(), $item);
            $item = preg_replace("/(%TASK_TITLE%)/", $task->GetTitle(), $item);

            $rows .= $item;
        }

        $table = preg_replace("/(%TASK_ROWS%)/", $rows, $table);

        return $table;
    }
}
