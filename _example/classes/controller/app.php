<?php

class App_Controller extends Controller
{
    public $layout;
    public $content;
    
    public function invoke_action($action, $params)
    {
        try {
            parent::invoke_action($action, $params);
        } catch (ExpectedException $e) {
            throw new ProgrammerException('An ExpectedException failed to be'
                . ' caught in application code', $e);
        } catch (UserException $e) {
            $this->error($e->getMessage());
            return;
        }
    }
    
    public function before()
    {
        parent::before();
        
        $this->layout = new View('/layouts/default.php');
        $this->layout->title = Roy::config('app.longname');
        $this->layout->stylesheets = array();
        $this->layout->scripts = array();
        
        // Use /[controller]/[action] as the default content view
        $route = $this->request->route();
        $default_view = Path::concat('/', $route->controller(),
            $route->action());
        
        $this->content = new View($default_view);
        $this->content->set_layout($this->layout);
    }
    
    public function after()
    {
        parent::after();
    }
}
