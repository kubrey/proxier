<?php

namespace proxier;


class BaseSiteCom {

    protected $errors = array();
    protected $parsedProxies = array();
    protected $config = array();

    public function parse(){

    }

    /**
     * @param $error
     * @return $this
     */
    public function setError($error){
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getLastError(){
        if($this->errors){
            return end($this->errors);
        }
        return null;
    }

    /**
     * @return array
     */
    public function getErrors(){
        return $this->errors;
    }

}