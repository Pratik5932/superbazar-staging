<?php
/**
 * Copyright 2015 iPragmatech. All rights reserved.
 * Author: Manish Kumar
 * Date: 28/3/17
 * Time: 10:10 AM
 */

namespace Ipragmatech\Ipreview\Model;
use Ipragmatech\Ipreview\Api\Data\RatingInterface;


class RatingData implements RatingInterface {

    private $ratingId;
    private $ratingCode;
    private $ratingValue;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->ratingCode = '';
        $this->ratingId ='';
        $this->ratingValue ='';
    }

    /**
     * Get the rating_id field.
     *
     * @api
     * @return int The name field.
     */
    public function getRatingId(){
        return $this->ratingId;
    }

    /**
     * Set the rating_id field.
     *
     * @api
     * @param $value int The new name field.
     * @return null
     */
    public function setRatingId($value){
        $this->ratingId = $value;
    }


    /**
     * Get the rating code field.
     *
     * @api
     * @return string The province field.
     */
    public function getRatingCode(){
        return $this->ratingCode;
    }

    /**
     * Set the rating code field.
     *
     * @api
     * @param $value string The new province field.
     * @return null
     */
    public function setRatingCode($value){
        $this->ratingCode = $value;
    }

    /**
     * Get the rating value field.
     *
     * @api
     * @return int The name field.
     */
    public function getRatingValue(){
        return $this->ratingValue;
    }

    /**
     * Set the rating value field.
     *
     * @api
     * @param $value int The new name field.
     * @return null
     */
    public function setRatingValue($value){
        $this->ratingValue = $value;
    }
}