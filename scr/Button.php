<?php
/**
 * Created by PhpStorm.
 * User: KK
 * Date: 4/15/2015
 * Time: 10:22 AM
 */

namespace App\Blocks;


class Button extends Widget{
    protected $_sort = 0;
    protected $_name;
    protected $_url;
    protected $_onclick;
    protected $_label;
    protected $_type;
    public function __construct($name, $data){
        $this->_name = $name;
        foreach($data as $property => $value){
            $property = '_'.$property;
            $this->$property = $value;
        }
    }

    /*
     * Get onclick
     *
     * @return string
     */
    public function getOnclick(){
        if($this->_onclick)
            return $this->_onclick;
        elseif($this->_url)
            return 'window.location=\''.$this->_url.'\'';
        else
            return null;
    }

    /*
     * Get icon by type
     *
     *
     */

    public function getIcon(){
        $icons = [
            'delete' => 'times',
            'add'       => 'plus',
            'back'      => 'step-backward',
            'reset'     => 'refresh',
            'duplicate' => 'copy',
            'confirm'   => 'check',
            'save'      => 'save',
            'deliver'   => 'truck',
        ];

        if(isset($icons[$this->getType()]))
            return 'fa-'.$icons[$this->getType()];
        elseif(isset($icons[$this->getName()]))
            return 'fa-'.$icons[$this->getName()];
        else
            return 'fa-'.$this->getName();
    }
}