<?php
/**
 * Created by PhpStorm.
 * User: KK
 * Date: 3/27/2015
 * Time: 10:30 AM
 */

namespace App\Blocks;

class BaseForm  extends Widget{
    protected $_id;
    protected $_fields = [];
    protected $_data;

    public function __construct($data){
        $this->_data = $data;
        $this->_addFields();
    }

    /*
     * Add a field to form
     */
    protected function _addField($name, $data){
        $field = new Field($name, $data);
        $this->_fields[] = $field;
        return $this;
    }


    /*
     * Generate html
     * @return string
     */
    public function html(){
        return view('tabs.form', array('form' => $this));
    }

    public function getValue($field){
        $data = $this->getData();
        if(!$data)
            return null;

        $value = $data;

        if($field->getName())
            $arr = explode('|', $field->getName());
        else
            $arr = explode('|', $field->getKey());
        foreach($arr as $att){
            if(isset($value[$att]))
                $value = $value[$att];
            else
                return null;
        }

        if($field->getType() == 'date'){
            $value = date('Y-m-d', strtotime($value));
        }
        return $value;
    }
}