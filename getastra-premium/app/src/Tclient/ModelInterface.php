<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Tclient;

/**
 * Description of ModelInterface.
 *
 * @author aditya
 */
/**
 * Interface abstracting model access.
 *
 * @author  OpenAPI Generator team
 */
interface ModelInterface
{
    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName();
    /**
     * Array of property to type mappings. Used for (de)serialization.
     *
     * @return array
     */
    public static function openAPITypes();
    /**
     * Array of property to format mappings. Used for (de)serialization.
     *
     * @return array
     */
    public static function openAPIFormats();
    /**
     * Array of attributes where the key is the local name, and the value is the original name.
     *
     * @return array
     */
    public static function attributeMap();
    /**
     * Array of attributes to setter functions (for deserialization of responses).
     *
     * @return array
     */
    public static function setters();
    /**
     * Array of attributes to getter functions (for serialization of requests).
     *
     * @return array
     */
    public static function getters();
    /**
     * Show all the invalid properties with reasons.
     *
     * @return array
     */
    public function listInvalidProperties();
    /**
     * Validate all the properties in the model
     * return true if all passed.
     *
     * @return bool
     */
    public function valid();
}
