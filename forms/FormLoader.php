<?php

class FormLoader {
    static public function LoadForm(string $name) : ?string {
        $path = __DIR__.'/'.$name.'.html';
        if(file_exists($path)) return file_get_contents($path);

        return null;
    }

    static public function LoadJS(string $name) : ?string {
        $path = __DIR__.'/js/'.$name.'.js';
        if(file_exists($path)) return file_get_contents($path);

        return null;
    }
}
