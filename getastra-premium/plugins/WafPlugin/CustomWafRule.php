<?php

namespace AstraPrefixed;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use AstraPrefixed\Expose\Filter;
/**
 * Description of CustomWafRule.
 *
 * @author aditya
 */
class CustomWafRule extends Filter
{
    //put your code here
    private $iri;
    public function __construct(array $data = null)
    {
        parent::__construct($data);
        $this->iri = $data['@id'];
    }
    public function getIri()
    {
        return $this->iri;
    }
}
/**
 * Description of CustomWafRule.
 *
 * @author aditya
 */
\class_alias('AstraPrefixed\\CustomWafRule', 'CustomWafRule', \false);
