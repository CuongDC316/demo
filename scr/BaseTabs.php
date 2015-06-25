<?php
/**
 * Created by PhpStorm.
 * User: KK
 * Date: 3/20/2015
 * Time: 10:44 AM
 */

namespace App\Blocks;

use Session;
use Auth;
use Illuminate\Pagination\Paginator;
use Config;
use File;
use URL;
use Route;
use App;
use Lang;

class BaseTabs extends Widget{
    protected $_id;
    protected $_tabs = [];
    protected $_method = 'POST';
    protected $_action;
    protected $_prefixRoute;
    protected $_dataId;
    protected $_data;
    /* Instance new object
     *
     * @param string $id
     * @param string $prefixRoute
     * @param string $title
     */
    public function __construct($tabId, $prefixRoute){
        $this->_id = $tabId;
        $this->_prefixRoute = $prefixRoute;
        //$params = Route::current()->parameters();
        $this->_data = App::bound($tabId)?App::make($tabId):null;
        $this->_dataId = isset($this->_data['_id'])?$this->_data['_id']:0;
        $this->_addButtons();
        $this->_addTabs();
    }

    /**
     * Add a tab
     * @param string $key
     * @param array $data
     * @return $this
     */
    protected function _addTab($key, $tabData){
        $this->_tabs[$key] = $tabData;
        return $this;
    }

    /*
    * Add buttons
    *
    * */
    protected function _addButtons(){
        $this->_addButton('back', [
                'label'     => Lang::get('general.back'),
                'url'       => $this->getUrl('list'),
                'sort'      => -1
            ]
        );

        $this->_addButton('save', [
                'label'     => Lang::get('general.save'),
                'onclick'   => $this->getId().'.submit()',
                'sort'      =>  10
            ]
        );

        $this->_addButton('saveAndContinue', [
                'label'     => Lang::get('general.save_and_edit'),
                'type'      => 'save',
                'onclick'   => $this->getId().'.saveAndContinueEdit()',
                'sort'      =>  20
            ]
        );

        if($this->getDataId()){
            $this->_addButton('duplicate', [
                    'label'     => Lang::get('general.duplicate'),
                    'url'       => $this->getUrl('duplicate', ['id' => $this->getDataId()]),
                    'sort'      => 90,
                ]
            );

            $this->_addButton('delete', [
                    'label'     => Lang::get('general.delete'),
                    'onclick'   => "deleteItem('".$this->getUrl('delete', ['id' => $this->getDataId()])."')",
                    'sort'      => 100
                ]
            );
        }
    }

    /*
     * Get form action
     *
     * @return string
     */
    public function getAction(){
        return $this->getUrl('save', ['id' => $this->getDataId()]);
    }

    /*
     * Get url
     *
     * @param string $action
     * @param array $params
     * @return string
     */
    public function getUrl($action, $params=null){
        if(Route::has($this->getPrefixRoute().'.'.$action))
            if($params)
                return URL::route($this->getPrefixRoute().'.'.$action, $params);
            else
                return URL::route($this->getPrefixRoute().'.'.$action);
    }
}