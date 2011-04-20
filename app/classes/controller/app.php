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
    }
    
    public function after()
    {
        parent::after();
    }
}
