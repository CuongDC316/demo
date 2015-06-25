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
use Lang;
use App\Helpers\Order as HelperOrder;

class BaseGrid extends Widget{
    protected $_gridId = 'baseGrid';
    protected $_massActions = array();
    protected $_useAjax = true;
    protected $_collection = null;
    protected $_gridButtons = array();
    protected $_columns = array();
    protected $_allIds = array();
    protected $_ajaxGridUrl = null;
    protected $_gridUrl = null;

    protected $_itemTotal = null;
    protected $_limit = 10;
    protected $_resource = null;
    protected $_params = [];
    protected $_collectionKey = null;
    protected $_defaultOrder = 'seq_no';
    protected $_defaultDir = 'desc';
    protected $_toExport = false;
    protected $_yesNoSelect = [];
    /**
     * Empty grid text
     *
     * @var sting|null
     */
    protected $_noRecordsText;

    /*
     * @param $girdId string
     * @param $resource string
     * @param $collectionKey string
     * @param $params array
     * @param ajaxUrl string
     *
     */
    public function __construct($gridId, $resource, $collectionKey, $params, $toExport=false){
        $this->setGridId($gridId);
        $this->setResource($resource);
        $this->setCollectionKey($collectionKey);
        $this->setToExport($toExport);
        $this->_addMassactions();
        $this->_addGridButtons();
        $this->_addColumns();
        $this->_addButtons();
        $this->setParams($params);
        $this->setCollection();
        $this->setNoRecordsText(Lang::get('general.no_records_found'));
        $this->setYesNoSelect(['' => Lang::get('general.any'), '1' => Lang::get('general.yes'), '0' => Lang::get('general.no')]);
    }

    /*
     * Set collection
     *
     * */
    public function setCollection(){
        $response = RestClient::call('GET', $this->getResource(), ['query'=> $this->getFilterParams()]);
        $data = json_decode($response->getBody()->getContents());
        $this->_allIds = $data->all_ids;
        $this->_itemTotal = $data->total;
        $this->_limit = $data->page_size;
        $collectionKey = $this->getCollectionKey();
        $this->_collection = $this->_toExport?$data->$collectionKey:
            new Paginator($data->$collectionKey, $this->getLimit(), 1);

    }

    /*
     * get column data to show in grid column
     *
     * */
    public function getColumnDataToShow($row, $columnName){
        $column = $this->_getColumn($columnName);
        if($column && $column->getRender()){
            $renderFunction = $column->getRender();
            return $this->$renderFunction($row, $column);
        }
        return $this->getColumnData($row, $columnName);
    }

    /*
     * get column data
     *
     * */
    public function getColumnData($row, $columnName){
        if (strpos($columnName,'|') === false)
            if(isset($row->$columnName))
                return $row->$columnName;
            else
                return null;
        else{
            $attributes = explode('|', $columnName);
            $tmp = $row;
            $i = 0;
            $count = count($attributes);
            foreach($attributes as $attribute){
                if(isset($tmp->$attribute)){
                    $tmp = $tmp->$attribute;
                    if($i < ($count-1) && !is_object($tmp))
                        break;
                }
                $i++;
            }
            $value = ($i < $count-1)?null:$tmp;
            return $value;
        }
    }

    /*
     * Get data to export
     *
     * @return array
     */

    public function getDataToExport(){
        $collection = $this->getCollection();
        $result = [];

        foreach($collection as $item){
            $data = $this->_exportItem($item);
            $result[] = $data;
        }

        if(!count($result)){ // no items
            foreach($this->_columns as $columnName => $column){
                if ($this->_isExportColumn($column))
                    $data[$column->getlabel()] = '';
            }
            $result[] = $data;
        }
        return $result;
    }

    /*
     * Get data to export
     *
     * @return array
     */

    public function getColumnFormatToExport(){
        $i = 65;
        foreach($this->_columns as $columnName => $column){
            $excelColumn = chr($i);
            if ($this->_isExportColumn($column)){
                if($column->getType() == 'number')
                    $data[$excelColumn] = '0';
                elseif($column->getType() == 'currency')
                    $data[$excelColumn] = '#,##0.00';
                elseif($column->getType() == 'date')
                    $data[$excelColumn] = 'dd/mm/yy';
                elseif($column->getType() == 'datetime')
                    $data[$excelColumn] = 'm/d/yy h:mm';
                elseif($column->getType() == 'time')
                    $data[$excelColumn] = 'h:mm:ss';
                else
                    $data[$excelColumn] = 'General';
                $i++;
            }
        }
        return $data;
    }

    /*
     * Get item data
     *
     * @param $item object
     * return array
     */
    protected function _exportItem($item){
        $data = [];
        foreach($this->_columns as $columnName => $column){

            if ($this->_isExportColumn($column)){
                $data[$column->getlabel()] = $this->_getColumnLabel($item, $column);

            }

        }
        return $data;
    }

    /*
     * Get column label to export
     *
     * @param $item object
     * @param $column App\Blocks\Column
     */
    protected function _getColumnLabel($item, $column){
        $value = $this->getColumnDataToShow($item, $column->getName());
        return ($column->getType() != 'select')?$value:(isset($column->getOptions()[$value])?$column->getOptions()[$value]:'');
    }

    /*
     * Check column is exported
     *
     * @return bool
     */
    protected function _isExportColumn($column){
        if (!in_array($column->getType(), ['action', 'checkbox']))
            return true;
        return false;
    }

    /*
     * Get last page
     *
     * */
    public function getLastPage(){
        if($this->getItemTotal() == 0)
            return 0;
        elseif($this->getItemTotal()%$this->getLimit() == 0)
            return $this->getItemTotal()/$this->getLimit();
        else
            return ceil($this->getItemTotal()/$this->getLimit());
    }

    /*
     * Get params, not get param from checkbox, radio column, add time to date[to]
     *
     * */
    public function getFilterParams(){
        $params = $this->_params;
        if(isset($params['filter'])){
            foreach($params['filter'] as $key => $value){
                if(isset($this->_columns[$key])){
                    if($this->_columns[$key]->getType() == 'checkbox' || $this->_columns[$key]->getType() == 'radio'){
                        unset($params['filter'][$key]);
                    }elseif($this->_columns[$key]->getType() == 'date' || $this->_columns[$key]->getType() == 'datetime'){
                        if(isset($value['to']))
                            $params['filter'][$key]['to'] = date('Y-m-d 23:59:59', strtotime($value['to']));
                    }
                }
            }
            unset($params['filter']['massaction']);
        }
        if($this->_toExport){
            $params['limit'] = 0;
            unset($params['page']);
            unset($params['offset']);
        }

        return $params;
    }

    /*
     * Set params
     *
     * */
    public function setParams($params){
        if(!isset($params['reset']) || $params['reset'] != 1)
            $params = array_replace($this->_getParamSessions(), $params?$params:[]);

        $checkboxColumnName = $this->_getCheckboxColumnName();

        if($checkboxColumnName){
            if(!isset($params['filter'][$checkboxColumnName])){
                unset($params['ids']);
                unset($params['nids']);
            } elseif($params['filter'][$checkboxColumnName] == 1){
                $params['ids'] = isset($params['selected_ids'])?$params['selected_ids']:implode(',', array_keys($this->getSelectedIds()));
                unset($params['nids']);
            } elseif($params['filter'][$checkboxColumnName] == 0){
                $params['nids'] = isset($params['selected_ids'])?$params['selected_ids']:implode(',', array_keys($this->getSelectedIds()));
                unset($params['ids']);
            }
            unset($params['selected_ids']);
        }
        if(!isset($params['order']))
            $params['order'] = $this->_defaultOrder;
        if(!isset($params['dir']))
            $params['dir'] = $this->_defaultDir;

        unset($params['reset']);
        $this->_params = $params;
        $this->_setParamSessions($this->_params);
        return $this;
    }


    /*
   * Add column
   *
   * */
    protected function _addColumn($name, $data){
        $this->_columns[$name] = new Column($name, $data);
        return $this;
    }

    /*
     * Get column
     *
     * @return App\Block\Column
     */
    protected function _getColumn($name){
        return isset($this->_columns[$name])?$this->_columns[$name]:null;
    }

    /*
   * Remove column
   *
   * */
    protected function _removeColumn($name){
        unset($this->_columns[$name]);
        return $this;
    }

    /*
  * Add buttons
  *
  * */
    protected function _addGridButtons(){
        $this->_addGridButton('reset', [
            'label' => Lang::get('general.reset'),
            'type' => 'refresh'
        ])
        ->_addGridButton('search', [
            'label' => Lang::get('general.search'),
            'type' => 'search'
        ]);
    }
    /*
   * Add grid button
   *
   * */
    protected function _addGridButton($name, $data){
        $this->_gridButtons[$name] = $data;
        return $this;
    }

    /*
   * Remove grid button
   *
   * */
    protected function _removeGridButton($name){
        unset($this->_gridButtons[$name]);
        return $this;
    }


    /*
    * set grid title
    *
    * */
    protected function _setGridTitle($title){
        return $this->_gridTitle = $title;
    }
    /*
    * Add massaction
    *
    * */
    protected function _addMassaction($name, $data){
        $this->_massActions[$name] = $data;
        return $this;
    }

    /*
    * Remove massaction
    *
    * */
    protected function _removeMassaction($name){
        unset($this->_massActions[$name]);
        return $this;
    }

    /*
    * Encode grid ID
    *
    * @return string
    * */
    protected function _getEncodedGridId(){
        return base64_encode($this->getGridId());
    }

    /*
   * Get sessions
    *
    * @array params
   * */
    protected function _getParamSessions(){
        $sessions = Session::all();

        $params = array();

        $encodedGridId = $this->_getEncodedGridId();
        foreach($sessions as $key => $value){
            if(strpos($key, $encodedGridId) !== false){

                $paramKey = substr($key, strlen($encodedGridId)+1);
                $params[$paramKey] = $value;
            }
        }
        return $params;
    }



    /*
    * Set sessions from input
     *
     * @array params
    * */
    protected function _setParamSessions($params){
        $sessions = Session::all();
        $encodedGridId = $this->_getEncodedGridId();

        //remove session
        foreach($sessions as $key => $value){
            if(strpos($key, $encodedGridId) !== false){
                Session::forget($key);
            }
        }
        //add new session
        foreach($params as $key => $value){
            Session::put($encodedGridId.'_'.$key, $value);
        }
    }

    /*
     * Generate grid html
     *
     * @return html
     */
    public function html(){
        return view('tabs.grid', array('grid' => $this));
    }

    /*
     * Get selected ids
     *
     * @return array
     */
    public function getSelectedIds(){
        return [];
    }

    /*
     * Convert array selected ids to params tring
     *
     * @return string
     */
    public function getSelectedIdsString(){
        $str = '';
        foreach($this->getSelectedIds() as $id => $values){
            $temp = '';
            foreach($values as $name => $value){
                $temp .= $name.'='.$value.'&';
            }
            $temp = trim($temp, '&');
            $str .= $id.'='.rawurlencode(base64_encode($temp)).'&'; //rawurlencode convert = -> %3D
        }
        return trim($str, '&');
    }

    /*
     * Get checkbox column
     *
     * @return string
     */
    protected function _getCheckboxColumnName(){
        foreach($this->_columns as $column){
            if($column->getType() == 'checkbox'){
                return $column->getName();
            }
        }
        return 'massaction';
    }

    /*
     * Get row url
     *
     * @param array $row
     * @return string
     */
    public function getRowUrl($row){
        return null;
    }

    public function showCurrencyFormat($row, $column){
        $row = json_decode(json_encode($row), true);
        if(isset($row['currency_template'])&&isset($row[$column->getName()]))
            return HelperOrder::Format($row['currency_template'],$row[$column->getName()]);
         
    }

}