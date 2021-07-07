<?php

abstract class ContentGenerator {
    abstract public function GenerateHTML() : string;

    public function GetViewPath() {
        return $this->GetViewFolder().$this->GetViewName().'.html';
    }

    public function GetViewName() {
        return get_class($this);
    }

    public function GetViewFolder() {
        return __DIR__.'/views/';
    }
}
