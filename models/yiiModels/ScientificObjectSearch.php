<?php
//******************************************************************************
//                            ScientificObjectSearch.php 
// SILEX-PHIS
// Copyright © INRA 2017
// Creation date: Oct. 2017
// Contact: morgane.vidal@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
//******************************************************************************
namespace app\models\yiiModels;

use app\models\yiiModels\YiiScientificObjectModel;
use yii\data\ArrayDataProvider;
use app\models\wsModels\WSConstants;

/**
 * Implements the search action for the scientific objects.
 * @author Morgane Vidal <morgane.vidal@inra.fr>
 */
class ScientificObjectSearch extends YiiScientificObjectModel {
    //SILEX:refactor
    //create a trait (?) with methods search and jsonListOfArray and use it in 
    //each class ElementNameSearch
    //\SILEX:refactor
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[self::URI, self::EXPERIMENT, self::ALIAS, self::TYPE], 'safe'],
        ];
    }
    
    /**
     * @param array $sessionToken used for the data access
     * @param string $params search params
     * @return mixed DataProvider of the result 
     *               or string "token" if the user needs to log in
     */
    public function search($sessionToken, $params) {
        
        //1. load the searched params 
        $this->load($params);
        
        if (isset($params[YiiModelsConstants::PAGE])) {
            $this->page = $params[YiiModelsConstants::PAGE];
        }
        
        //2. Check validity of search data
        if (!$this->validate()) {
            return new ArrayDataProvider();
        }
        
        //3. Request to the web service and return result        
        $findResult = $this->find($sessionToken, $this->attributesToArray());
        
        if (is_string($findResult)) {
            return $findResult;
        } else if (isset($findResult->{WSConstants::METADATA}->{WSConstants::STATUS}[0]->{WSConstants::EXCEPTION}->{WSConstants::DETAILS}) 
                    && $findResult->{WSConstants::METADATA}->{WSConstants::STATUS}[0]->{WSConstants::EXCEPTION}->{WSConstants::DETAILS} === 
                    WSConstants::TOKEN_INVALID) {
            return WSConstants::TOKEN_INVALID;
        } else {
            $resultSet = $this->jsonListOfArraysToArray($findResult);
            return new ArrayDataProvider([
                'models' => $resultSet,
                'pagination' => [
                    'pageSize' => $this->pageSize,
                    'totalCount' => $this->totalCount
                ],
                //SILEX:info
                //totalCount must be there too to get the pagination in GridView
                'totalCount' => $this->totalCount
                //\SILEX:info
            ]);
        }
    }
}