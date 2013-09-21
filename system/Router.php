<?php

class Router {
    
    private $uri;
  
    private $controller;
  
    private $action;
  
    private $id;
  
    public function __construct($uri) {
        $this->uri = $uri;
    }

    public function map() {

        if (empty($this->uri[0])) {
            $this->controller = 'home';
            $this->action = 'index';
            $this->id = null;
        } else {
            $this->controller = $this->uri[0];
            if (empty($this->uri[1])) {
                $this->action = 'index';
            } else {
                $this->action = $this->uri[1];
                if (empty($this->uri[2])) {
                    $this->id = null;
                } else {
                    $this->id = $this->uri[2];
                }
            }
        }
    }

    public function getController() {
        return $this->controller;
    }

    public function getAction() {
        return $this->action;
    }

    public function getId() {
        return $this->id;
    }
}

/* End of file Router.php */