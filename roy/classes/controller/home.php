<?php

class Home_Controller extends Controller
{
    public function action_index()
    {
        $content = new View('/roy/home.php');
        echo $content->render();
    }
}
