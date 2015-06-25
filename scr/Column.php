<?php
/**
 * Created by PhpStorm.
 * User: KK
 * Date: 3/31/2015
 * Time: 9:20 AM
 */
namespace App\Blocks;
use App\Helpers\Currency;
class Column  extends Widget{
    protected $_name;
    protected $_type = 'text';
    protected $_label;
    protected $_align;
    protected $_values;
    protected $_width;
    protected $_currency = '$';
    protected $_sort = true;
    protected $_filter = true;
    protected $_options;
    protected $_links;
    protected $_format = 'd m Y, h:i';
    protected $_editable = false;
    protected $_readonly = false;
    protected $_required = false;
    protected $_style;
    protected $_currencyPosition = 'left';
    protected $_numberOfDecimals = 2;
    protected $_decimalSeparator = '.';
    protected $_thousandSeparator = ',';
    protected $_min;
    protected $_max;
    protected $_render;

    public function __construct($name, $data){
        $this->_name = $name;

        $this->_currency = Currency::getSymbol();
        $this->_currencyPosition = Currency::getPosition();
        $this->_numberOfDecimals = Currency::getNumberOfDecimals();
        $this->_decimalSeparator = Currency::getDecimalSeparator();
        $this->_thousandSeparator = Currency::getThousandSeparator();


        foreach($data as $key => $value){
            $key = '_'.$key;
            $this->$key = $value;
        }
    }

    public function formatCurrency($amount) {
        switch ($this->getCurrencyPosition()) {
            case 'left':
                return $this->getCurrency() . number_format($amount, $this->getNumberOfDecimals(), $this->getDecimalSeparator(), $this->getThousandSeparator());
                break;
            case 'right':
                return number_format($amount, $this->getNumberOfDecimals(), $this->getDecimalSeparator(), $this->getThousandSeparator()) . $this->getCurrency();
                break;
            case 'leftspace':
                return $this->getCurrency() . ' ' . number_format($amount, $this->getNumberOfDecimals(), $this->getDecimalSeparator(), $this->getThousandSeparator());
                break;
            case 'rightspace':
                return number_format($amount, $this->getNumberOfDecimals(), $this->getDecimalSeparator(), $this->getThousandSeparator() . ' ' . $this->getCurrency());
                break;
            default:
                return $this->getCurrency() . number_format($amount, $this->getNumberOfDecimals(), $this->getDecimalSeparator(), $this->getThousandSeparator());
                break;
        }
    }

}