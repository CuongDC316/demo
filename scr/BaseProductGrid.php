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

class BaseProductGrid extends BaseGrid{
    /*
     * Render product name column with variant
     *
     * @return string html
     */
    public function showNameIncludeVariant($row, $column){
        $row = json_decode(json_encode($row), true);

        if(isset($row['name'])){
            $html = '<span>'.$row['name'].'</span><br /><span class="variants">';

            $variantAttributes = isset($row['attributes'])?$row['attributes']:[];
            unset($row['attributes']);

            $isEditable = $column->isEditable();
            $isReadonly = $column->isReadonly();
            $variants = '';
            $inputs = '';
            foreach($row as $property => $value){
                if(in_array($property, $variantAttributes)){
                    $variants .= $value . '/';
                    if($isEditable){
                        $type = $isReadonly?'hidden':'text';
                        $inputs .= '<input type="'.$type.'" name="'.$property.'" value="'.$value.'" />';
                    }
                }

            }
            return $html.trim($variants, '/').'</span>'.$inputs;
        }
    }

    /*
     * Get column label to export
     *
     * @param $item object
     * @param $column App\Blocks\Column
     */
    protected function _getColumnLabel($item, $column){
        $value = $this->getColumnDataToShow($item, $column->getName());
        $label = ($column->getType() == 'select' && isset($column->getOptions()[$value]))?$column->getOptions()[$value]:$value;
        $label = str_ireplace('<br />', "\n", $label);
        return strip_tags($label);
    }

    /*
     * Get status
     *
     * @return int
     */
    protected function getStatus(){
        $ajaxUrl = $this->getAjaxGridUrl();
        $temp = explode('/', $ajaxUrl);
        return end($temp);
    }
}