<?php

class Controller
{
    protected function view($view, $data = [])
    {
        $viewPath = "../app/views/$view.php";
        
        if (!file_exists($viewPath)) {
            die("Error: View '$view' not found!");
        }

        extract($data);
        require_once $viewPath;
    }
}
