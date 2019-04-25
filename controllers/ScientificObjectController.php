<?php
//******************************************************************************
//                       ScientificObjectController.php 
// SILEX-PHIS
// Copyright © INRA 2017
// Creation date: August 2017
// Contact: morgane.vidal@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
//******************************************************************************
 namespace app\controllers;
 
 use Yii;
 use yii\web\Controller;
 use yii\filters\VerbFilter;
 
 use app\models\yiiModels\YiiScientificObjectModel;
 use app\models\yiiModels\ScientificObjectSearch;
 use app\models\yiiModels\YiiExperimentModel;
 use app\models\wsModels\WSConstants;
 use app\models\yiiModels\YiiSpeciesModel;
 use app\models\yiiModels\YiiModelsConstants;

require_once '../config/config.php';
 
/**
 * Scientific object controller.
 * @see yii\web\Controller
 * @see app\models\yiiModels\YiiScientificObjectModel
 * @author Morgane Vidal <morgane.vidal@inra.fr>
 */
 class ScientificObjectController extends Controller {
     
     /**
      * Delimiter character of the CSV files.
      * @var DELIM_CSV
      */
     const DELIM_CSV = ";";
     
     /**
      * Geometry column in the CSV files.
      * @var GEOMETRY
      */
     const GEOMETRY = "Geometry";
     
     /**
      * Experiment URI column in the CSV files
      * @var EXPERIMENT_URI
      */
     
     const EXPERIMENT_URI = "ExperimentURI";
     
     /**
      * Alias column in the CSV files.
      * @var ALIAS
      */
     const ALIAS = "Alias";
     
     /**
      * Species column in the CSV files.
      * @var SPECIES
      */
     const SPECIES = "Species";
     
     /**
      * Variety column for the CSV files.
      * @var VARIETY
      */
     const VARIETY = "Variety";
     
     /**
      * Experimental modalities column for the CSV files.
      * @var EXPERIMENT_MODALITIES
      */
     const EXPERIMENT_MODALITIES = "ExperimentModalities";
     
     /**
      * Replication column for the CSV files.
      * @var REPLICATION
      */
     const REPLICATION = "Replication";
     
    /**
      * Replication column for the CSV files.
      * @var RDF_TYPE
      */
    const RDF_TYPE = "RdfType";
     
    /**
     * Defines the behaviours.
     * @return array
     */
     public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    
    /**
     * Gets the types of scientific object.
     * @return array list of the object types URIs.
     * @example [
     *             "UAV",
     *             "Pot"
     *          ]
     */
    public function getObjectTypes() {
        $model = new YiiScientificObjectModel();
        
        $objectsTypes = [];
        $totalPages = 1;
        for ($i = 0; $i < $totalPages; $i++) {
            $model->page = $i;
            $scientificObjectConcepts = $model->getObjectTypes(Yii::$app->session[WSConstants::ACCESS_TOKEN]);
            if ($scientificObjectConcepts === WSConstants::TOKEN) {
                return WSConstants::TOKEN;
            } else {
                $totalPages = $scientificObjectConcepts[WSConstants::PAGINATION][WSConstants::TOTAL_PAGES];

                foreach ($scientificObjectConcepts[WSConstants::DATA] as $objectType) {
                    $objectsTypes[] = explode("#", $objectType->uri)[1];
                }
            }
        }
        return $objectsTypes;
    }
    
    /**
     * Get the experiments URIs.
     * @return array list of experiments.
     */
    public function getExperimentsURI() {
        $model = new YiiExperimentModel();
        return $model->getExperimentsURIList(Yii::$app->session[WSConstants::ACCESS_TOKEN]);
    }
    
    /**
     * Get the species.
     * @return array list of species.
     */
    public function getSpecies() {
        $speciesModel = new YiiSpeciesModel();
        return $speciesModel->getSpeciesList(Yii::$app->session['access_token']);
    }
    
    /**
     * Gets the CSV file header
     * @return array list of the columns names for a scientific objects file
     */
    private function getHeaderList() {
        return [
            ScientificObjectController::ALIAS, 
            ScientificObjectController::RDF_TYPE,  
            ScientificObjectController::EXPERIMENT_URI, 
            ScientificObjectController::GEOMETRY, 
            ScientificObjectController::SPECIES, 
            ScientificObjectController::VARIETY, 
            ScientificObjectController::EXPERIMENT_MODALITIES, 
            ScientificObjectController::REPLICATION];
    }
    
    /**
     * @param array $csvHeader an array with for example the columns of a CSV file
     * @return boolean true if the required columns are in the $csvHeader 
     *                 false otherwise
     */
    private function existRequiredColumns($csvHeader) {
        return in_array(ScientificObjectController::ALIAS, $csvHeader) 
                && in_array(ScientificObjectController::RDF_TYPE, $csvHeader) 
                && in_array(ScientificObjectController::EXPERIMENT_URI, $csvHeader);
    }
    
    /**
     * Checks if the column names exist in the file. 
     * @param array $csvHeader the header columns list 
     * @return array if error the errors in the file 
     *               else a key value array corresponding to the columns number 
     *                    and their names in the file. 
     *                    e.g : "alias" => 3. The column alias is the third 
     *                          column in the csv file
     */
    private function getCSVHeaderCorrespondancesOrErrors($csvHeader) {
        $headersNamesNumber = null;
        $headersNames = $this->getHeaderList();
        if ($this->existRequiredColumns($csvHeader)) {
            foreach ($headersNames as $headerName) {
                $keyNumer = array_search($headerName, $csvHeader);
                if (is_int($keyNumer)) {
                    $headersNamesNumber[$headerName] = $keyNumer;
                }             }
        } else {
            $headersNamesNumber["Error"][] = Yii::t('app/messages','Required column missing');
        }
        return $headersNamesNumber;
    }
    
    /**
     * @param array $array
     * @return array the array without values equals to ""
     */
    private function getArrayWithoutEmptyValues($array) {
        $toReturn = null;
        foreach($array as $element) {
            if ($element != "") {
                $toReturn[] = $element;
            }
         }
         return $toReturn;
    }
    
    /**
     * Checks the geometry format. The expected format is a Polygon defined by the WKT :  
     * POLYGON ((XX.XXX XX.XXXXX, Y.YYYYY YY.YYYY, ZZ.ZZZZZ ZZ.ZZZZ, ..., XX.XXX XX.XXXXX))
     * For the moment, only polygons accepted. Projection type check (WGS84 required)
     * will be done when inserting the data.
     * @param string $geometry
     * @return true if valid geometry, 
     *         false otherwise
     */
    private function isGeometryOk($geometry) {        
        $explodeByOpenPar = explode("((", $geometry);
        if (count($explodeByOpenPar) === 2) {
            if (strtoupper($explodeByOpenPar[0]) === "POLYGON " 
                    || strtoupper($explodeByOpenPar[0]) === "POLYGON") {
                $explodeByClosePar = explode("))", $explodeByOpenPar[1]);
                if (count($explodeByClosePar) === 2) { // POLYGON (( XXXXXXXX ))
                    $points = explode(",", $explodeByClosePar[0]); // get polygon points
                    
                    $p1 = $this->getArrayWithoutEmptyValues(explode(" ", $points[0]));
                    $p2 = $this->getArrayWithoutEmptyValues(explode(" ", $points[(count($points)-1)]));
                    
                    if ($p1 === $p2) { //The first and the last point are le same
                        foreach ($points as $point) {
                            $latlon = $this->getArrayWithoutEmptyValues(explode(" ", $point));
                            if (count($latlon) === 2) {
                                if (floatval($latlon[0]) && floatval($latlon[1])) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Checks if an experiment exists.
     * @param string $experimentURI
     * @return boolean true if the experiment exists.
     */
    private function existExperiment($experimentURI) {
        $experimentModel = new YiiExperimentModel(null, null);
        $experimentModel->findByURI(Yii::$app->session[WSConstants::ACCESS_TOKEN], $experimentURI);
        
        return $experimentModel->uri !== null;
    }
    
    /**
     * 
     * @param string $species
     * @return boolean true if the specie URI is in the species list.
     */
    private function existsSpecies($species) {
        $aoModel = new YiiScientificObjectModel();
        return in_array($species, $aoModel->getSpeciesUriList());
    }
    
    /**
     * Checks the CSV file.
     * @param array $csvContent CSV contents for the scientific objects creation
     * @return array the errors 
     *               null if no error
     */
    private function getCSVErrors($csvContent) {
        //SILEX:todo
        //create a library for the data type check (isGeometry, isDate, ...)
        //\SILEX:todo
        
        //1. check header
        $headerCheck = $this->getCSVHeaderCorrespondancesOrErrors(str_getcsv($csvContent[0], ScientificObjectController::DELIM_CSV));
        $errors = null;
        if (isset($headerCheck["Error"])) {
            $errors["header"] = $headerCheck["Error"];
        } else { //2. check each cell's content
            $experiments = [];
            for ($i = 1; $i < count($csvContent); $i++) {
                $row = str_getcsv($csvContent[$i], ScientificObjectController::DELIM_CSV);
                If ($row[$headerCheck["Geometry"]] != "") {
                    if (!$this->isGeometryOk($row[$headerCheck["Geometry"]])) {
                        $error = null;
                        $error["line"] = "L." . ($i + 1);
                        $error["column"] = ScientificObjectController::GEOMETRY;
                        $error["message"] = Yii::t('app/messages', 'Bad geometry given') . ". " 
                                . Yii::t('app/messages', 'Expected format') 
                                . " : POLYGON ((1.33 2.33, 3.44 5.66, 4.55 5.66, 6.77 7.88, 1.33 2.33))";
                        $errors[] = $error;
                        }
                }
                if (!in_array($row[$headerCheck[ScientificObjectController::EXPERIMENT_URI]], $experiments)) {
                    if (!$this->existExperiment($row[$headerCheck[ScientificObjectController::EXPERIMENT_URI]])) {                        
                        $error = null;
                        $error["line"] = "L." . ($i + 1);
                        $error["column"] = ScientificObjectController::EXPERIMENT_URI;
                        $error["message"] = Yii::t('app/messages', 'Unknown experiment') . " : " 
                                . $row[$headerCheck[ScientificObjectController::EXPERIMENT_URI]];
                        $errors[] = $error;
                    }
                    $experiments[] = $row[$headerCheck[ScientificObjectController::EXPERIMENT_URI]];
                }               
                if (!$this->existsSpecies($row[$headerCheck["Species"]])) {
                    $error = null;
                    $error["line"] = "L." . ($i + 1);
                    $error["column"] = ScientificObjectController::SPECIES;
                    $error["message"] = Yii::t('app/messages', 'Unknown species') . " : " 
                            . $row[$headerCheck[ScientificObjectController::SPECIES]];
                    $errors[] = $error;
                }
                if ($row[$headerCheck["Alias"]] == "") {
                    $error = null;
                    $error["line"] = "L." . ($i + 1);
                    $error["column"] = ScientificObjectController::ALIAS;
                    $error["message"] = Yii::t('app/messages', 'Alias is missing');
                    $errors[] = $error;
                }
            }
        }
        
        return $errors;      
    }
    
    /**
     * Gets a HTML error message to show with the errors found in the CSV file.
     * @param array $arrayError errors. Expected format :
     *                                     ["L.85"]["Geometry"]["Error message"]
     * @return string the message to show to the user 
     */
    private function getErrorMessageToPrint($arrayError) {
        if (isset($arrayError["header"])) {
            $errorMessage = "<div class=\"alert alert-danger\" role=\"alert\"><b>" . $arrayError["header"][0] . "</b></div>";
        } else {
            $errorMessage = "<div class=\"alert alert-danger\" role=\"alert\"><b>" . Yii::t('app/messages', 'Errors in file') . "</b>"
                            . "<table class=\"table table-hover\">"
                            . "<thead><tr><th>" . Yii::t('app', 'Line') . "</th><th>" . Yii::t('app', 'Column') . "</th><th>" . Yii::t('app', 'Error') . "</th></tr></thead><tbody>";

            foreach ($arrayError as $errorLine) {
                $errorMessage .= "<tr>";
                $errorMessage .= "<th scope=\"row\"><p>" .$errorLine["line"] . "</p></th>";
                $errorMessage .= "<td>" .$errorLine["column"] . "</td>";
                $errorMessage .= "<td>" .$errorLine["message"] . "</td>";
                $errorMessage .= "</tr>";
            }
            $errorMessage .= "</tbody></table></div>";
        }
        
        return $errorMessage;
    }    

    /**
     * Generates the scientific object creation view.
     * @return mixed
     */
    public function actionCreate() {
        $model = new YiiScientificObjectModel();
        
        $objectsTypes = $this->getObjectTypes();
        if ($objectsTypes === WSConstants::TOKEN) {
            return $this->redirect(Yii::$app->urlManager->createUrl("site/login"));
        }
        $experiments = $this->getExperimentsURI();
        if ($experiments === WSConstants::TOKEN) {
            return $this->redirect(Yii::$app->urlManager->createUrl("site/login"));
        }        
        
        $species = $this->getSpecies();
        
        return $this->render('create', [
            'model' => $model,
            'objectsTypes' => json_encode($objectsTypes, JSON_UNESCAPED_SLASHES),
            'experiments' => json_encode($experiments, JSON_UNESCAPED_SLASHES),
            'species' => json_encode($species,JSON_UNESCAPED_SLASHES)
        ]);
    }
    
    /**
     * Creates the given objects.
     * @return string the JSON of the creation return.
     */
    public function actionCreateMultipleScientificObjects() {
        $objects = json_decode(Yii::$app->request->post()["objects"]);
        $sessionToken = Yii::$app->session[WSConstants::ACCESS_TOKEN];
        
        $return = [
            "objectUris" => [],
            "messages" => []
        ];        

        if (count($objects) > 0) {

            foreach ($objects as $object) {
                $scientificObjectModel = new YiiScientificObjectModel();
                
                $scientificObjectModel->alias = $object[1];
                $scientificObjectModel->type = $this->getObjectTypeCompleteUri($object[2]);
                $scientificObjectModel->experiment = $object[3];
                $scientificObjectModel->geometry = $object[4];
                $scientificObjectModel->parent = $object[5];
                $scientificObjectModel->species = $object[6];
                $scientificObjectModel->variety = $object[7];  
                $scientificObjectModel->modality = $object[8];
                $scientificObjectModel->replication = $object[9];
                
                $scientificObject = $scientificObjectModel->attributesToArray();
                $forWebService = $this->getArrayForWebServiceCreate($scientificObject);
                $insertionResult = $scientificObjectModel->insert($sessionToken, $forWebService);
                
                
                if ($insertionResult->{WSConstants::METADATA}->status[0]->exception->type != WSConstants::ERROR) {
                    $return["objectUris"][] = $insertionResult->{WSConstants::METADATA}->{WSConstants::DATA_FILES}[0];
                    $return["messages"][] = "object saved";
                }
                else {
                    $return["objectUris"][] = null;
                    $return["messages"][] = $insertionResult->{WSConstants::METADATA}->status[0]->exception->details;
                }
            }      
        }        
        return json_encode($return, JSON_UNESCAPED_SLASHES);
    }
    
    /**
     * 
     * @param string $objectType
     * @return string the complete vector type URI corresponding to the given 
     * vector type.
     * @example http://www.opensilex.org/vocabulary/oeso#UAV
     */
    private function getObjectTypeCompleteUri($objectType) {
        $objectTypesList = $this->getObjectsTypesUris();
        foreach ($objectTypesList as $objectTypeUri) {
            if (strpos($objectTypeUri, $objectType)) {
                return $objectTypeUri;
            }
        }
        return null;
    }
    
    /**
     * Gets the vectors types (complete URI).
     * @return array list of the vectors types URIs 
     * @example [
     *            "http://www.opensilex.org/vocabulary/oeso#UAV",
     *            "http://www.opensilex.org/vocabulary/oeso#Pot"
     *          ]
     */
    public function getObjectsTypesUris() {
        $model = new YiiScientificObjectModel();
        
        $objectsTypes = [];
        $totalPages = 1;
        for ($i = 0; $i < $totalPages; $i++) {
            $model->page = $i;
            $objectsConcepts = $model->getObjectTypes(Yii::$app->session[WSConstants::ACCESS_TOKEN]);
            if ($objectsConcepts === WSConstants::TOKEN) {
                return WSConstants::TOKEN;
            } else {
                $totalPages = $objectsConcepts[WSConstants::PAGINATION][WSConstants::TOTAL_PAGES];
                foreach ($objectsConcepts[WSConstants::DATA] as $objectType) {
                    $objectsTypes[] = $objectType->uri;
                }
            }
        }
        return $objectsTypes;
    }
    
    /**
     * @param array $fileContent the CSV file content
     * @param array $correspondances the columns numbers corresponding to the 
     * expected columns (if the file columns are not in the good order) 
     * @return array data of the attribute $fileContent in the web service expected format
     */
    private function getArrayForWebServiceCreate($scientificObject) {
        
        if ($scientificObject[YiiScientificObjectModel::ALIAS] != null) {
            $alias["relation"] = Yii::$app->params['rdfsLabel'];
            $alias["value"] = $scientificObject[YiiScientificObjectModel::ALIAS];
            $p["properties"][] = $alias;
        }
        
        $p[YiiScientificObjectModel::RDF_TYPE] = $scientificObject[YiiScientificObjectModel::RDF_TYPE];
        $p[YiiScientificObjectModel::EXPERIMENT] = $scientificObject[YiiScientificObjectModel::EXPERIMENT];
        $p[YiiScientificObjectModel::GEOMETRY] = $scientificObject[YiiScientificObjectModel::GEOMETRY];
        
        if ($scientificObject["ispartof"] != null) {
            $parent["relation"] = Yii::$app->params['isPartOf'];
            $parent["value"] = $scientificObject["ispartof"];
            $p["properties"][] = $parent;
        }
        
        if ($scientificObject["species"] != null) {
            $species[YiiScientificObjectModel::RDF_TYPE] = Yii::$app->params['Species'];
            $species["relation"] = Yii::$app->params['hasSpecies'];
            $species["value"] = $scientificObject[YiiScientificObjectModel::SPECIES];
            $p["properties"][] = $species;
        }
        
        if ($scientificObject[YiiScientificObjectModel::VARIETY] != null) {
            $variety[YiiScientificObjectModel::RDF_TYPE] = Yii::$app->params['Variety'];
            $variety["relation"] = Yii::$app->params['hasVariety'];
            $value = str_replace(" ", "_", $scientificObject[YiiScientificObjectModel::VARIETY]);
            $variety["value"] = $value;
            $p["properties"][] = $variety;
        }
        
        if ($scientificObject[YiiScientificObjectModel::MODALITY] !== null) {
            $modality["relation"] = Yii::$app->params['hasExperimentModalities'];
            $modality["value"] = $scientificObject[YiiScientificObjectModel::MODALITY];
            $p["properties"][] = $modality;
        }
        
        if ($scientificObject[YiiScientificObjectModel::REPLICATION] !== null) {
            $replication["relation"] = Yii::$app->params['hasReplication'];
            $replication["value"] = $scientificObject[YiiScientificObjectModel::REPLICATION];
            $p["properties"][] = $replication;
        }
       
        $forWebService[] = $p;    

        return $forWebService;
    }
    
    /**
     * Renders the scientific objects index.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new ScientificObjectSearch();
        
        //Get the search params and update the page if needed
        $searchParams = Yii::$app->request->queryParams;        
        if (isset($searchParams[YiiModelsConstants::PAGE])) {
            $searchParams[YiiModelsConstants::PAGE]--;
        }
        
        $searchResult = $searchModel->search(Yii::$app->session[WSConstants::ACCESS_TOKEN], $searchParams);
        
        if (is_string($searchResult)) {
            if ($searchResult === WSConstants::TOKEN_INVALID) {
                return $this->redirect(Yii::$app->urlManager->createUrl("site/login"));
            } else {
                return $this->render('/site/error', [
                        'name' => Yii::t('app/messages','Internal error'),
                        'message' => $searchResult]);
            }
        } else {
            //Get the experiments list
            $experimentModel = new YiiExperimentModel();
            $this->view->params['listExperiments'] = 
                    $experimentModel->getExperimentsURIAndLabelList(Yii::$app->session[WSConstants::ACCESS_TOKEN]);
            
            //Get all the types of scientific objects
            $objectsTypes = $this->getObjectsTypesUris();
            if ($objectsTypes === WSConstants::TOKEN) {
                return $this->redirect(Yii::$app->urlManager->createUrl("site/login"));
            }
            
            //Prepare the array for the select of the view
            $scientificObjectsTypesToReturn = [];
            foreach ($objectsTypes as $objectType) {
                $scientificObjectsTypesToReturn[$objectType] = explode("#", $objectType)[1];
            }
            
            return $this->render('index', [
               'searchModel' => $searchModel,
               'dataProvider' => $searchResult,
               'scientificObjectTypes' => $scientificObjectsTypesToReturn
            ]);
        }
    }
    
    /**
     * Allows the user to download the CSV file of a scientific objects search
     * result on the index page.
     * @return mixed 
     */
    public function actionDownloadCsv() {
        $searchModel = new ScientificObjectSearch();
        if (isset($_GET['model'])) {
            $searchParams = $_GET['model'];
            $searchModel->alias = 
                    isset($searchParams[YiiScientificObjectModel::ALIAS]) ? $searchParams[YiiScientificObjectModel::ALIAS] : null;
            $searchModel->type = isset($searchParams["type"]) ? $searchParams["type"] : null;
            $searchModel->experiment = 
                    isset($searchParams[YiiScientificObjectModel::EXPERIMENT]) ? $searchParams[YiiScientificObjectModel::EXPERIMENT] : null;
        } else {
            $searchParams = [];
        }
        
        $searchResult = $searchModel->search(Yii::$app->session[WSConstants::ACCESS_TOKEN], $searchParams);
        
        if (is_string($searchResult)) {
            if ($searchResult === WSConstants::TOKEN_INVALID) {
                return $this->redirect(Yii::$app->urlManager->createUrl("site/login"));
            } else {
                return $this->render('/site/error', [
                        'name' => Yii::t('app/messages','Internal error'),
                        'message' => $searchResult]);
            }
        } else {
            //get all the data (if multiple pages) and write them in a file
            $serverFilePath = \config::path()['documentsUrl'] . "AOFiles/exportedData/" . time() . ".csv";
            
            $headerFile = "ScientificObjectURI" . ScientificObjectController::DELIM_CSV .
                          "Alias" . ScientificObjectController::DELIM_CSV .
                          "RdfType" . ScientificObjectController::DELIM_CSV .
                          "ExperimentURI" . ScientificObjectController::DELIM_CSV . 
                          "\n";
            
            file_put_contents($serverFilePath, $headerFile);
            
            for ($i = 0; $i <= intval($searchModel->totalPages); $i++) {
                //1. call service for each page
                $searchParams[YiiModelsConstants::PAGE] = $i;
                
                //SILEX:TODO
                //Find why the $this->load does not work in this case in the search
                $searchModel->experiment = 
                        isset($_GET['model'][YiiScientificObjectModel::URI]) ? $_GET['model'][YiiScientificObjectModel::URI] : null;
                $searchModel->experiment = 
                        isset($_GET["model"][YiiScientificObjectModel::ALIAS]) ? $_GET["model"][YiiScientificObjectModel::ALIAS] : null;
                $searchModel->experiment = 
                        isset($_GET['model'][YiiScientificObjectModel::EXPERIMENT]) ? $_GET['model'][YiiScientificObjectModel::EXPERIMENT] : null;
                //\SILEX:TODO
                $searchResult = $searchModel->search(Yii::$app->session[WSConstants::ACCESS_TOKEN], $searchParams);
                                
                //2. write in file
                $models = $searchResult->getmodels();
                
                foreach ($models as $model) {
                    $stringToWrite = $model->uri . ScientificObjectController::DELIM_CSV . 
                                     $model->alias . ScientificObjectController::DELIM_CSV .
                                     $model->rdfType . ScientificObjectController::DELIM_CSV .
                                     $model->experiment . ScientificObjectController::DELIM_CSV . 
                                     "\n";
                    
                    file_put_contents($serverFilePath, $stringToWrite, FILE_APPEND);
                }
            }
            Yii::$app->response->sendFile($serverFilePath); 
        }
    }
 }
