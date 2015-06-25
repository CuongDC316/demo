<?php
/**
 * Created by PhpStorm.
 * User: KK
 * Date: 3/20/2015
 * Time: 10:44 AM
 */

namespace App\Blocks;

use Session;
use App\Client\RestClient;
use Auth;
use Illuminate\Pagination\Paginator;
use Config;
use File;
use App\Blocks\Button;
use App\Helpers\Common;
use URL;

class Widget {
    protected $_title = null;
    protected $_buttons = [];
    protected $_js = [];



    /*
     * Set js to widget
     *
     * @param string $path
     * @param array $params
     *
     * @return this
     */

    public function setJs($path, $params){
        $this->_js = ['path' => $path, 'params' => $params];
        return $this;
    }

    /*
     * Get js
     * @return string
     *
     */
    public function getJsPath(){
        return URL::to('js/'.$this->_js['path']);
    }

    public function getJsParams(){
        return $this->_js['params'];
    }


    /*
   * Add button
   *
   * */
    protected function _addButton($name, $data){
        $this->_buttons[$name] = new Button($name, $data);
        return $this;
    }

    /*
   * Remove button
   *
   * */
    protected function _removeButton($name){
        unset($this->_buttons[$name]);
        return $this;
    }

    /*
  * Remove button
  *
  * */
    protected function _updateButton($name, $data){
        $button = $this->_buttons[$name];
        foreach($data as $property => $value){
            $button->set($property, $value);
        }
        $this->_buttons[$name] = $button;
        return $this;
    }

    /*
     * Get button list
     *
     * @return array
     */
    public function getButtons(){
        uasort($this->_buttons, function($a, $b) {
            return strnatcmp($a->getSort(), $b->getSort());
        });
        return $this->_buttons;
    }


    public function getButtonIconClass($name){
        $icons = ['delete' => 'times',
            'add'       => 'plus',
            'back'      => 'step-backward',
            'reset'     => 'refresh',
            'duplicate' => 'copy',
            'confirm'   => 'check',
        ];
        if(isset($icons[$name]))
            return 'fa-'.$icons[$name];
        else
            return 'fa-'.$name;
    }

    public function __call($name, $arguments){
        if($name == 'set'){
            $attribute = '_'.$arguments[0];
            $this->$attribute = $arguments[1];
        }elseif($name == 'get'){
            $attribute = '_'.$arguments[0];
            return $this->$attribute;
        }elseif(strpos($name, 'set') !== false){
            $attribute = '_'.lcfirst(substr($name, 3));
            $this->$attribute = $arguments[0];
            return $this;
        }elseif(strpos($name, 'get') !== false){
            $attribute = '_'.lcfirst(substr($name, 3));
            return $this->$attribute;
        }elseif(strpos($name, 'is') !== false){
            $attribute = '_'.lcfirst(substr($name, 2));
            return $this->$attribute;
        }
        return;
    }


    /*
    * Get country list include states
    *
    * @return array [code => [stateCode => stateName, ....]]
    */
    public function getCountryStates(){
        return Common::getCountryStates();
    }

    /*
    * Get country list
    *
    * @return array [code => name, code => name ...]
    */
    protected function _getCountryList(){
        return Common::getCountryList();
    }

}