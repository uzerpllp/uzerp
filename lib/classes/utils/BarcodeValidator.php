<?php
/**
 * This class is based on code by Ferry Bouwhuis. It has had minor modifications to change the usage/flow
 *
 * @link           http://www.phpclasses.org/package/8560-PHP-Detect-type-and-check-EAN-and-UPC-barcodes.html
 * @link           https://github.com/violuke/php-barcodes/blob/master/src/violuke/Barcodes/BarcodeValidator.php
 * @author         Ferry Bouwhuis
 * @version        1.0.1
 * @LastChange     2014-04-13
 */
class BarcodeValidator {
    private $barcode;
    private $type;
    private $gtin14;
    private $valid;
    const TYPE_GTIN = 'GTIN';
    const TYPE_EAN_8 = 'EAN-8';
    const TYPE_EAN = 'EAN';
    const TYPE_UPC = 'UPC';
    const TYPE_UPC_COUPON_CODE = 'UPC Coupon Code';
    const TYPE_EAN_RESTRICTED = 'EAN Restricted';
    public function __construct($barcode) {
        $this->barcode = $barcode;
        // Trims parsed string to remove unwanted whitespace or characters
        $this->barcode = trim($this->barcode);
        if (preg_match('/[^0-9]/', $this->barcode)) {
            $this->valid = false;
            return;
        }
        if (!is_string($this->barcode)) {
            $this->barcode = strval($this->barcode);
        }
        $this->gtin14 = $this->barcode;
        $length = strlen($this->gtin14);
        if (($length > 11 && $length <= 14) || $length == 8) {
            $zeros = 18 - $length;
            $length = null;
            $fill = '';
            for ($i = 0; $i < $zeros; $i++) {
                $fill .= '0';
            }
            $this->gtin14 = $fill . $this->gtin14;
            $fill = null;
            $this->valid = true;
            if (!$this->checkDigitValid()) {
                $this->valid = false;
            } elseif (substr($this->gtin14, 5, 1) > 2) {
                // EAN / JAN / EAN-13 code
                $this->type = self::TYPE_EAN;
            } elseif (substr($this->gtin14, 6, 1) == 0 && substr($this->gtin14, 0, 10) == 0) {
                // EAN-8 / GTIN-8 code
                $this->type = self::TYPE_EAN_8;
            } elseif (substr($this->gtin14, 5, 1) <= 0) {
                // UPC / UCC-12 GTIN-12 code
                if (substr($this->gtin14, 6, 1) == 5) {
                    $this->type = self::TYPE_UPC_COUPON_CODE;
                } else {
                    if (strlen($this->barcode) == 13) {
                        $this->type = self::TYPE_EAN;
                    } else {
                        $this->type = self::TYPE_UPC;
                    }
                }
            } elseif (substr($this->gtin14, 0, 6) == 0) {
                // GTIN-14 code
                $this->type = self::TYPE_GTIN;
            } else {
                // EAN code
                if (substr($this->gtin14, 5, 1) == 2) {
                 $this->type = self::TYPE_EAN_RESTRICTED;
                } else {
                    $this->type = self::TYPE_EAN;
                }
            }
        }
    }
    public function getBarcode(){
        return $this->barcode;
    }
    public function getType(){
        return $this->type;
    }
    public function getGTIN14(){
        return (string)substr($this->gtin14, -14);
    }
    public function isValid(){
        return $this->valid;
    }
    private function checkDigitValid() {
        $calculation = 0;
        for ($i = 0; $i < (strlen($this->gtin14) - 1); $i++) {
            $calculation += $i % 2 ? $this->gtin14[$i] * 1 : $this->gtin14[$i] * 3;
        }
        if (substr(10 - (substr($calculation, -1)), -1) != substr($this->gtin14, -1)) {
            return false;
        } else {
            return true;
        }
    }
}