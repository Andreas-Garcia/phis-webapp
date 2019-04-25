<?php
//******************************************************************************
//                          YiiScientificObjectModel.php 
// SILEX-PHIS
// Copyright © INRA 2017
// Creation date: August 2017
// Contact: morgane.vidal@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
//******************************************************************************
namespace app\models\yiiModels;

use app\models\wsModels\WSActiveRecord;
use app\models\wsModels\WSUriModel;
use app\models\wsModels\WSScientificObjectModel;
use app\models\wsModels\WSConstants;

/**
 * The YII model for the scientific objects. 
 * Implements a customized Active Record (WSActiveRecord, for the web services access)
 * @see app\models\wsModels\WSScientificObjectModel
 * @author Morgane Vidal <morgane.vidal@inra.fr>
 */
class YiiScientificObjectModel extends WSActiveRecord {
    
    /**
     * URI of the scientific object.
     * @example http://www.phenome-fppn.fr/pheno3c/o17000001
     * @var string
     */
    public $uri;
    const URI = "uri";
    
    /**
     * Geometry of the scientific objects. 
     * @example POLYGON((0 0, 10 0, 10 10, 0 10, 0 0))
     * @see https://fr.wikipedia.org/wiki/Well-known_text
     * @var string
     */
    public $geometry;
    const GEOMETRY = "geometry";
    
    /**
     * RDF type of the scientific object. Must be in the ontology. 
     * @Example http://www.opensilex.org/vocabulary/oeso#Plot)
     * @var string
     */
    public $type;
    const RDF_TYPE = "rdfType";
    
    /**
     * URI of the experiment in which the scientific object is.
     * @example http://www.phenome-fppn.fr/diaphen/DIA2017-1)
     * @var experiment 
     */
    public $experiment;
    const EXPERIMENT = "experiment";
    
    /**
     * Year of the scientific object .
     * @example 2017
     * @var string
     */
    public $year;
    
    /**
     * File for the scientific objects creation by CSV file.
     * @var file
     */
    public $file;
    
    /** 
     * Alias of the plot.
     * @exmaple 2/DZ_PG_30/ZM4361/WW/1/DIA2017-05-19
     * @var string
     */
    public $alias;
    const ALIAS = "alias";
    
    public $species;
    const SPECIES = "species";
    
    public $variety;
    const VARIETY = "variety";
    
    public $modality;
    const MODALITY = "modality";
    
    public $replication;
    const REPLICATION = "replication";
    
    public $parent;
    const ISPARTOF = "ispartof";

    /**
     * Initializes wsModel. In this class, wsModel is a WSScientificObjectModel.
     * @param string $pageSize number of elements per page (limited to 150 000)
     * @param string $page number of the current page 
     */
    public function __construct($pageSize = null, $page = null) {
        $this->wsModel = new WSScientificObjectModel();
        $this->pageSize = ($pageSize !== null || $pageSize === "") ? $pageSize : null;
        $this->page = ($page !== null || $page != "") ? $page : null;
    }
       
    /**
     * Allows to fill the attributes with the information in the array given.
     * @param array $array array key => value which contains the metadata of 
     * an scientific object.
     * @throws Exception
     */
    protected function arrayToAttributes($array) {
        throw new Exception('Not implemented');
    }

    /**
     * Creates an array representing the scientific object.
     * @return array with the attributes. 
     */
    public function attributesToArray() {
        $elementForWebService = parent::attributesToArray();
        $elementForWebService[YiiScientificObjectModel::URI] = $this->uri;
        $elementForWebService[YiiScientificObjectModel::EXPERIMENT] = $this->experiment;
        $elementForWebService[YiiScientificObjectModel::ALIAS] = $this->alias;
        $elementForWebService[YiiScientificObjectModel::RDF_TYPE] = $this->type;
        $elementForWebService[YiiScientificObjectModel::GEOMETRY] = $this->geometry;
        $elementForWebService[YiiScientificObjectModel::SPECIES] = $this->species;
        $elementForWebService[YiiScientificObjectModel::VARIETY] = $this->variety;
        $elementForWebService[YiiScientificObjectModel::MODALITY] = $this->modality;
        $elementForWebService[YiiScientificObjectModel::REPLICATION] = $this->replication;
        $elementForWebService[YiiScientificObjectModel::ISPARTOF] = $this->parent;
        
        return $elementForWebService;
    }
    
    /**
     * Calls web service and return the list of object types of the ontology.
     * @see app\models\wsModels\WSUriModel::getDescendants($sessionToken, $uri, $params)
     * @return list of the sensors types
     */
    public function getObjectTypes($sessionToken) {
        $scientificObjectConceptUri = "http://www.opensilex.org/vocabulary/oeso#ScientificObject";
        $params = [];
        if ($this->pageSize !== null) {
           $params[WSConstants::PAGE_SIZE] = $this->pageSize; 
        }
        if ($this->page !== null) {
            $params[WSConstants::PAGE] = $this->page;
        }
        
        $wsUriModel = new WSUriModel();
        $requestRes = $wsUriModel->getDescendants($sessionToken, $scientificObjectConceptUri, $params);
        
        if (!is_string($requestRes)) {
            if (isset($requestRes[WSConstants::TOKEN_INVALID])) {
                return WSConstants::TOKEN;
            } else {
                return $requestRes;
            }
        } else {
            return $requestRes;
        }
    }
    
    /**
     * Calls web service and return the list of object types of the ontology.
     * @see app\models\wsModels\WSUriModel::getDescendants($sessionToken, $uri, $params)
     * @return list of the sensors types
     */
    public function getExperiments($sessionToken) {
        $params = [];
        if ($this->pageSize !== null) {
           $params[WSConstants::PAGE_SIZE] = $this->pageSize; 
        }
        if ($this->page !== null) {
            $params[WSConstants::PAGE] = $this->page;
        }
        
        $wsUriModel = new WSUriModel();
        $requestRes = $wsUriModel->getDescendants($sessionToken, $scientificObjectConceptUri, $params);
        
        if (!is_string($requestRes)) {
            if (isset($requestRes[WSConstants::TOKEN_INVALID])) {
                return WSConstants::TOKEN;
            } else {
                return $requestRes;
            }
        } else {
            return $requestRes;
        }
    }
    
    /**
     * Gets a fixed species URI list (while the species service is not implemented). 
     * @return list of the species URI.
     */
    public function getSpeciesUriList() {
        return [
            "http://www.phenome-fppn.fr/id/species/betavulgaris", 
            "http://www.phenome-fppn.fr/id/species/brassicanapus",
            "http://www.phenome-fppn.fr/id/species/canabissativa",
            "http://www.phenome-fppn.fr/id/species/glycinemax",
            "http://www.phenome-fppn.fr/id/species/gossypiumhirsutum",
            "http://www.phenome-fppn.fr/id/species/helianthusannuus",
            "http://www.phenome-fppn.fr/id/species/linumusitatissum",
            "http://www.phenome-fppn.fr/id/species/lupinusalbus",
            "http://www.phenome-fppn.fr/id/species/ordeumvulgare",
            "http://www.phenome-fppn.fr/id/species/orizasativa",
            "http://www.phenome-fppn.fr/id/species/pennisetumglaucum",
            "http://www.phenome-fppn.fr/id/species/pisumsativum",
            "http://www.phenome-fppn.fr/id/species/populus",
            "http://www.phenome-fppn.fr/id/species/sorghumbicolor",
            "http://www.phenome-fppn.fr/id/species/teosinte",
            "http://www.phenome-fppn.fr/id/species/triticumaestivum",
            "http://www.phenome-fppn.fr/id/species/triticumturgidum",
            "http://www.phenome-fppn.fr/id/species/viciafaba",
            "http://www.phenome-fppn.fr/id/species/zeamays",
            "http://www.phenome-fppn.fr/id/species/maize"
        ];
    }
}
