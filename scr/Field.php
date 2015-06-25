<?php
/**
 * Created by PhpStorm.
 * User: KK
 * Date: 3/31/2015
 * Time: 9:20 AM
 */
namespace App\Blocks;
class Field  extends Widget{
    protected $_name;
    protected $_type = 'text';
    protected $_attributes = ['class' => 'form-control input-sm'];
    protected $_disabled = false;
    protected $_readonly = false;
    protected $_required = false;
    protected $_checked = false;
    protected $_after;
    protected $_before;
    protected $_class;
    protected $_value;
    protected $_label;
    protected $_onClick;
    protected $_onChange;
    protected $_rows = 4;
    protected $_multiple = false;
    protected $_style;
    protected $_script;
    protected $_format = 'm/d/Y';
    protected $_key;
    protected $_confirm;
    protected $_pattern;
    protected $_comment;
    protected $_text;
    protected $_values;
    protected $_options;
    protected $_min;
    protected $_max;
    protected $_step;
    public function __construct($key, $data){
        $this->_key = $key;
        foreach($data as $property => $value){

            if($property == 'attributes'){
                if(!is_array($value))
                    $value = [$value];
                if(isset($value['class']))
                    $this->_attributes['class'] .= ' '.$value['class'];

                $this->_attributes = array_merge($value, $this->_attributes);
            }else{
                $property = '_'.$property;
                $this->$property = $value;
            }
        }
    }

    /*
     * Get field Id
     *
     * @return string
     */
    public function getId(){
        return str_replace(['[', ']'], '', str_replace('|', '-', $this->_key));
    }


    public function getValues(){
        if($this->getType() == 'buttons'){
            $buttons = [];
            foreach($this->_values as $buttonName => $buttonData){
                $buttons[$buttonName] = new Button($buttonName, $buttonData);
            }
            $this->_values = $buttons;
        }
        return parent::getValues();
    }

    /*
     * Get field attribute
     *
     * @return string
     */
    public function getAttributesHtml(){
        $attributes = $this->getAttributes();

        if($this->getClass())
            $attributes['class'] .= ' '.$this->getClass();
        if($this->getConfirm())
            $attributes['equalto'] = '#'.$this->getConfirm();
        if($this->getPattern())
            $attributes['pattern'] = $this->getPattern();
        if($this->getType() == 'textarea')
            $attributes['rows'] = $this->getRows();
        elseif($this->getType() == 'number'){
            $attributes['min'] = $this->getMin();
            $attributes['max'] = $this->getMax();
            $attributes['step'] = $this->getStep();
        }
        elseif($this->getType() == 'hidden')
            return ['id' => $this->getId()];

        if($this->isDisabled())
            $attributes[] = 'disabled';
        if($this->isReadonly())
            $attributes[] = 'readonly';
        if($this->isRequired())
            $attributes[] = 'required';
        if($this->isMultiple())
            $attributes[] = 'multiple';
        if($this->getStyle())
            $attributes['style'] = $this->getStyle();


        $attributes['id'] = $this->getId();

        if($this->getOnClick())
            $attributes['onClick'] = $this->getOnClick();
        if($this->getOnChange())
            $attributes['onChange'] = $this->getOnChange();

        if($this->getType() == 'multiselect')
            $attributes['multiple'] = true;
        return $attributes;
    }
}