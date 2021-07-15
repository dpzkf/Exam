<?php

class CGProgressList extends ContentGenerator {
    private array $userProgress;

    public function __construct(array $userProgress) {
        $this->userProgress = $userProgress;
    }

    public function GenerateHTML() : string {
        if(empty($this->userProgress)) return "No tracked progress";

        $ul = file_get_contents($this->GetViewPath());

        $rows = '';
        foreach ($this->userProgress as $progress) {
            $item = file_get_contents($this->GetViewFolder().$this->GetViewName().'.item.html');

            $item = preg_replace("/(%LIBRARY_NAME%)/", $progress->GetLibrary()->GetTitle(), $item);
            $item = preg_replace("/(%COMPLETED_TASKS%)/", $progress->GetCompletedTasks(), $item);
            $item = preg_replace("/(%TASKS_COUNT%)/", count($progress->GetLibrary()->GetTasks()), $item);

            $rows .= $item;
        }

        $ul = preg_replace("/(%ITEMS%)/", $rows, $ul);

        return $ul;
    }
}
