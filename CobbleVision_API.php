<?php

use GuzzleHttp\Client;

###################################################
#Preparation of Variables and Environment Settings.
###################################################

$environmentType = True
public $serverAdress = "https://cobblevision.com"

public $valid_price_categories = array(1 => "high", 2 => "medium", 3 => "low")
public $valid_job_types = array(1 => "QueuedJob")

public $debugging = False

public $apiUserName = "";
public $apiToken = "";
  
if ($environmentType == False || $environmentType === "demo"){
  public $BaseURL = "https://www.cobblevision.com"
}else{
  public $BaseURL = serverAdress + "/api/"
}

###################################################
## Functions for modifying environment setup
###################################################

# Function allows you to set the Username and Token for CobbleVision
# @function setApiAuth()
# @param {String} apiusername
# @param {String} apitoken
# @returns {Boolean} Indicating success of setting Api Auth.
function setApiAuth(apiusername, apitoken){
 try{
   $this -> apiUserName = $apiusername
   $this -> apiToken = $apiToken
   return True
 }catch(Exception $e){
  printf($e->getMessage())
  exit()
 }
}

# Function allows you to set the debugging variable
# @function setDebugging()
# @param {Boolean} debugBool
# @returns {Boolean} Indicating success of setting Api Auth.
function setDebugging(debugBool){
   try{
     $this -> debugging = debugBool
     return True
   }catch(Exception $e){
    printf($e -> getMessage())
    exit()
   }
}

#####################################################
# Functions for interacting with the CobbleVision API
#####################################################

# Return of the following functions is specified within this type description
# @typedef {Object} Response
# @method {Integer} getStatusCode() Returns Status Code of Response
# @method {String} getBody() Returns Body of Response
# @method {Object} getHeaders() Returns Headers of Response

# This function uploads a media file to CobbleVision. You can find it after login in your media storage. Returns a response object with body, response and headers properties, deducted from npm request module
# @async
# @function uploadMediaFile()  
# @param {string} price_category - Either high, medium, low
# @param {boolean} publicBool - Make Media available publicly or not?
# @param {string} name - Name of Media (Non Unique)
# @param {array} tags - Tag Names for Media - Array of Strings
# @param {string} file - Result from readFile;
# @returns {Response} This return the UploadMediaResponse. The body is in JSON format.
function uploadMediaFile(price_category, publicBool, name, tags, file){
  try{
    $endpoint = "media"
      
    if(substr(($this->BaseURL)-1) != "/"){
      throw new Exception("Cobble BaseURL must end on slash!")
    }
       
    $keyArray = array(1 => "price_category", 2 => "publicBool", 3 => "name", 4 => "tags", 5 => "Your API Username", 6 => "Your Api Token")
    $valueArray = array(1 => $price_category, 2 => $publicBool, 3 => $name, 4 => $tags, 5 => ($this -> apiUserName), 6 => ($this -> apiToken))
    $typeArray = array(1 => "string", 2 => "boolean", 3 => "string", 4 => "array", 5 => "string", 6 => "string")
       
    try{
      checkTypeOfParameter($valueArray, $typeArray)
    }catch(Exception $e){
      $err_message = intval($e -> GetMessage())
      if(gettype($err_message) != "Integer"){
         throw new Exception("The provided data is not valid: " + $keyArray[$err_message] + " is not of type " + $typeArray[$err_message])
      }else{
        throw new Exception($e -> GetMessage())
    }
      
    if(!array_search[$price_category, $valid_price_categories]){
      throw new Exception("Price Category is in wrong format!")
    }
    
    #Unfortunately PHP does not support filebuffers or UInt8Array
    $jsonObject = array(
       "price_category" => $price_category,
       "public" => $publicBool,
       "name" => $name,
       "tags" => $tags,
       "file" => mb_convert.encoding($file, "iso-8859-1", "utf-8")
    )
    
    $client = new GuzzleHTTP\Client()
    $res = $client -> request("POST", ($this -> BaseURL) + $endpoint, ["headers" => array("Accept" => "application/json", "Content-Type" => "application/json"), "auth" => array($this -> apiUserName => $this -> apiPassword), "json" => $jsonObject])
    
    if(($this -> debugging) == True){
      echo $res -> getStatusCode()
      echo $res -> getBody()
    }
    
    return $res
   }catch(Exception $e){
      
     if($this -> debugging){
       echo ($e -> GetMessage())
     }
      
     throw new Exception($e -> GetMessage())
   }   
}

# This function deletes Media from CobbleVision
# @async
# @function deleteMediaFile()  
# @param {array} IDArray Array of ID's as Strings
# @returns {Response} This return the DeleteMediaResponse. The body is in JSON format.

function deleteMediaFile(IDArray){
  try{
    $endpoint = "media"
      
    if(substr(($this->BaseURL)-1) != "/"){
      throw new Exception("Cobble BaseURL must end on slash!")
    }
       
    $keyArray = array(1 => "IDArray", 2 => "Your API Username", 3 => "Your Api Token")
    $valueArray = array(1 => $IDArray, 2 => ($this -> apiUserName), 3 => ($this -> apiToken))
    $typeArray = array(1 => "array", 2 => "string", 3 => "string")
       
    try{
      checkTypeOfParameter($valueArray, $typeArray)
    }catch(Exception $e){
      $err_message = intval($e -> GetMessage())
      if(gettype($err_message) != "Integer"){
         throw new Exception("The provided data is not valid: " + $keyArray[$err_message] + " is not of type " + $typeArray[$err_message])
      }else{
        throw new Exception($e -> GetMessage())
    }
      
    $invalidMediaIDList = checkIDArrayForInvalidValues($IDArray)
    
    if(count($invalidMediaIDList) > 0){
      throw new Exception("You provided invalid Media IDs. Please check your input!")
    }
    
    #Unfortunately PHP does not support filebuffers or UInt8Array
    $client = new GuzzleHTTP\Client()
      
    $res = $client -> request("DELETE", ($this -> BaseURL) + $endpoint + "?id=" + json_encode($IDArray), ["headers" => array("Accept" => "application/json", "Content-Type" => "application/json"), "auth" => array($this -> apiUserName => $this -> apiPassword)])
    
    if(($this -> debugging) == True){
      echo $res -> getStatusCode()
      echo $res -> getBody()
    }
    
    return $res
   }catch(Exception $e){
      
     if($this -> debugging){
       echo ($e -> GetMessage())
     }
      
     throw new Exception($e -> GetMessage())
   }   
}
       
# Launch a calculation with CobbleVision's Web API. Returns a response object with body, response and headers properties, deducted from npm request module;
# @async
# @function launchCalculation() 
# @param {array} algorithms Array of Algorithm Names
# @param {array} media Array of Media ID's  
# @param {string} type Type of Job - Currently Always "QueuedJob"
# @param {string} [notificationURL] Optional - Notify user upon finishing calculation!
# @returns {Response} This returns the LaunchCalculationResponse. The body is in JSON format.  

function launchCalculation(algorithms, media, type, notificationURL){
  try{
    $endpoint = "calculation"
      
    if(substr(($this->BaseURL)-1) != "/"){
      throw new Exception("Cobble BaseURL must end on slash!")
    }

    $keyArray = array(1 => "algorithms", 2 => "media", 3 => "type", 4 => "notificationURL", 5 => "Your API Username", 6 => "Your Api Token")
    $valueArray = array(1 => $algorithms, 2 => $media, 3 => $type, 4 => $notificationURL, 5 => ($this -> apiUserName), 6 => ($this -> apiToken))
    $typeArray = array(1 => "array", 2 => "array", 3 => "string", 4 => "string", 5 => "string", 6 => "string")   
       
    try{
      checkTypeOfParameter($valueArray, $typeArray)
    }catch(Exception $e){
      $err_message = intval($e -> GetMessage())
      if(gettype($err_message) != "Integer"){
         throw new Exception("The provided data is not valid: " + $keyArray[$err_message] + " is not of type " + $typeArray[$err_message])
      }else{
        throw new Exception($e -> GetMessage())
    }
      
    if(!array_search[$type, $valid_job_types]){
      throw new Exception("Calculation Type is not valid!")
    }
    
    $invalidAlgorithmIDs = checkIDArrayForInvalidValues($algorithms)
    if(count($invalidAlgorithmIDs) > 0){
      throw new Exception("You provided invalid Algorithm IDs. Please check your input!")
    }
      
    $invalidMediaIDs = checkIDArrayForInvalidValues($media)
    if(count($invalidMediaIDs) > 0){
      throw new Exception("You provided invalid Media IDs. Please check your input!")
    }
    
    #Unfortunately PHP does not support filebuffers or UInt8Array
    $client = new GuzzleHTTP\Client()
   
    $jsonObject = array(
       "algorithms" => $algorithms,
       "media" => $media,
       "type" => $type,
       "notificationURL" => $notificationURL,
    )  
      
    $res = $client -> request("POST", ($this -> BaseURL), ["headers" => array("Accept" => "application/json", "Content-Type" => "application/json"), "auth" => array($this -> apiUserName => $this -> apiPassword), "json" => $jsonObject]])
    
    if(($this -> debugging) == True){
      echo $res -> getStatusCode()
      echo $res -> getBody()
    }
    
    return $res
   }catch(Exception $e){
      
     if($this -> debugging){
       echo ($e -> GetMessage())
     }
      
     throw new Exception($e -> GetMessage())
   }   
}
       
# This function waits until the given calculation ID's are ready to be downloaded!
# @async
# @function waitForCalculationCompletion() 
# @param {array} calculationIDArray Array of Calculation ID's
# @returns {Response} This returns the WaitForCalculationResponse. The body is in JSON format.   

function waitForCalculationCompletion(calculationIDArray){
  try{
    $endpoint = "calculation"
      
    if(substr(($this->BaseURL)-1) != "/"){
      throw new Exception("Cobble BaseURL must end on slash!")
    }

    $keyArray = array(1 => "algorithms", 2 => "media", 3 => "type", 4 => "notificationURL", 5 => "Your API Username", 6 => "Your Api Token")
    $valueArray = array(1 => $algorithms, 2 => $media, 3 => $type, 4 => $notificationURL, 5 => ($this -> apiUserName), 6 => ($this -> apiToken))
    $typeArray = array(1 => "array", 2 => "array", 3 => "string", 4 => "string", 5 => "string", 6 => "string")   
       
    try{
      checkTypeOfParameter($valueArray, $typeArray)
    }catch(Exception $e){
      $err_message = intval($e -> GetMessage())
      if(gettype($err_message) != "Integer"){
         throw new Exception("The provided data is not valid: " + $keyArray[$err_message] + " is not of type " + $typeArray[$err_message])
      }else{
        throw new Exception($e -> GetMessage())
    }
      
    if(!array_search[$type, $valid_job_types]){
      throw new Exception("Calculation Type is not valid!")
    }
    
    $invalidAlgorithmIDs = checkIDArrayForInvalidValues($algorithms)
    if(count($invalidAlgorithmIDs) > 0){
      throw new Exception("You provided invalid Algorithm IDs. Please check your input!")
    }
      
    $invalidMediaIDs = checkIDArrayForInvalidValues($media)
    
    if(count($invalidMediaIDs) > 0){
      throw new Exception("You provided invalid Media IDs. Please check your input!")
    }
    
    $calculationFinishedBool = False
    
      while($calculationFinishedBool == False){
    
      #Unfortunately PHP does not support filebuffers or UInt8Array
      $client = new GuzzleHTTP\Client()
      $res = $client -> request("GET", ($this -> BaseURL) + $endpoint + "?id=" + json_encode($calculationIDArray), ["headers" => array("Accept" => "application/json", "Content-Type" => "application/json"), "auth" => array($this -> apiUserName => $this -> apiPassword)])
    
      $result = $res -> getBody()
      $result = json_decode($result)
      
      if(is_array($result -> getBody())){
        for($i = 0; $i < count($result); $i++){
          if(property_exists($result[i]), "status"))}
            if($result[i] -> status === "finished"){
              calculationFinishedBool = True
            }else
              calculationFinishedBool = False
              break;
            }
          }
        }
      }else{
        if(property_exists($result, "error")){
          $calculationFinishedBool = True
        }
      }
  
      if(calculationFinishedBool == False){
        sleep(3)
      }
    }
  
    if(($this -> debugging) == True){
      echo $res -> getStatusCode()
      echo $res -> getBody()
    }
    
    return $res
   }catch(Exception $e){
      
     if($this -> debugging){
       echo ($e -> GetMessage())
     }
      
     throw new Exception($e -> GetMessage())
   }   
}

# This function deletes Result Files or calculations in status "waiting" from CobbleVision. You cannot delete finished jobs beyond their result files, as we keep them for billing purposes.
# @async
# @function deleteCalculation()
# @param {array} IDArray Array of ID's as Strings
# @returns {Response} This returns the DeleteCalculationResponse. The body is in JSON format.
       
function deleteCalculation(IDArray){
  try{
    $endpoint = "calculation"
      
    if(substr(($this->BaseURL)-1) != "/"){
      throw new Exception("Cobble BaseURL must end on slash!")
    }
       
    $keyArray = array(1 => "IDArray", 2 => "Your API Username", 3 => "Your Api Token")
    $valueArray = array(1 => $IDArray, 2 => ($this -> apiUserName), 3 => ($this -> apiToken))
    $typeArray = array(1 => "array", 2 => "string", 3 => "string")
       
    try{
      checkTypeOfParameter($valueArray, $typeArray)
    }catch(Exception $e){
      $err_message = intval($e -> GetMessage())
      if(gettype($err_message) != "Integer"){
         throw new Exception("The provided data is not valid: " + $keyArray[$err_message] + " is not of type " + $typeArray[$err_message])
      }else{
        throw new Exception($e -> GetMessage())
    }
      
    $invalidCalculationList = checkIDArrayForInvalidValues($IDArray)
      
    if(count($invalidCalculationList) > 0){
      throw new Exception("You provided invalid Calculation IDs. Please check your input!")
    }
    
    #Unfortunately PHP does not support filebuffers or UInt8Array
    $client = new GuzzleHTTP\Client()
      
    $res = $client -> request("DELETE", ($this -> BaseURL) + $endpoint + "?id=" + json_encode($IDArray) + "&returnOnlyStatusBool=True", ["headers" => array("Accept" => "application/json", "Content-Type" => "application/json"), "auth" => array($this -> apiUserName => $this -> apiPassword)])
    
    if(($this -> debugging) == True){
      echo $res -> getStatusCode()
      echo $res -> getBody()
    }
    
    return $res
   }catch(Exception $e){
      
     if($this -> debugging){
       echo ($e -> GetMessage())
     }
      
     throw new Exception($e -> GetMessage())
   }   
}       
       
# Get Calculation Result with CobbleVision's Web API. Returns a response object with body, response and headers properties, deducted from npm request module;
# @async
# @function getCalculationResult()
# @param {array} IDArray ID of calculation to return result Array 
# @param {boolean} returnOnlyStatusBool Return full result or only status? See Doc for more detailed description!
# @returns {Response} This returns the GetCalculationResult. The body is in json format.

function getCalculationResult(IDArray, returnOnlyStatusBool){
  try{
    $endpoint = "calculation"
      
    if(substr(($this->BaseURL)-1) != "/"){
      throw new Exception("Cobble BaseURL must end on slash!")
    }
       
    $keyArray = array(1 => "IDArray", 2 => "returnOnlyStatusBool", 3 => "Your API Username", 4 => "Your Api Token")
    $valueArray = array(1 => $IDArray, 2 => $returnOnlyStatusBool, 3 => ($this -> apiUserName), 4 => ($this -> apiToken))
    $typeArray = array(1 => "array", 2 => "boolean", 3 => "string", 4 => "string")
       
    try{
      checkTypeOfParameter($valueArray, $typeArray)
    }catch(Exception $e){
      $err_message = intval($e -> GetMessage())
      if(gettype($err_message) != "Integer"){
         throw new Exception("The provided data is not valid: " + $keyArray[$err_message] + " is not of type " + $typeArray[$err_message])
      }else{
        throw new Exception($e -> GetMessage())
    }
    
    $invalidCalculationList = checkIDArrayForInvalidValues($IDArray)
      
    if(count($invalidCalculationList) > 0){
      throw new Exception("You provided invalid Calculation IDs. Please check your input!")
    }    
    
    $client = new GuzzleHTTP\Client()
    $res = $client -> request("GET", ($this -> BaseURL) + $endpoint + "?id=" + json_encode($IDArray) + "&returnOnlyStatusBool=" + json_encode($returnOnlyStatusBool), ["headers" => array("Accept" => "application/json", "Content-Type" => "application/json"), "auth" => array($this -> apiUserName => $this -> apiPassword)])
    
    if(($this -> debugging) == True){
      echo $res -> getStatusCode()
      echo $res -> getBody()
    }
    
    return $res
   }catch(Exception $e){
      
     if($this -> debugging){
       echo ($e -> GetMessage())
     }
      
     throw new Exception($e -> GetMessage())
   }   
}

# Request your calculation result by ID with the CobbleVision API. Returns a response object with body, response and headers properties, deducted from npm request module;
# @async
# @function getCalculationVisualization()
# @param {array} id ID of calculation to return result/check String
# @param {boolean} returnBase64Bool Return Base64 String or image buffer as string?
# @param {integer} width target width of visualization file
# @param {integer} height target height of visualization file
# @returns {Response} This returns the GetCalculationVisualization Result. The body is in binary format.

function getCalculationVisualization(id, returnBase64Bool, width, height){
  try{
    $endpoint = "calculation/visualization"
      
    if(substr(($this->BaseURL)-1) != "/"){
      throw new Exception("Cobble BaseURL must end on slash!")
    }
       
    $keyArray = array(1 => "id", 2 => "returnBase64Bool", 3 => "width", 4 => "height", 5 => "Your API Username", 6 => "Your Api Token")
    $valueArray = array(1 => $id, 2 => $returnBase64Bool, 3 => $width, 4 => $height, 5 => ($this -> apiUserName), 6 => ($this -> apiToken))
    $typeArray = array(1 => "string", 2 => "boolean", 3 => "integer", 4 => "integer", 5 => "string", 6 => "string")
       
    try{
      checkTypeOfParameter($valueArray, $typeArray)
    }catch(Exception $e){
      $err_message = intval($e -> GetMessage())
      if(gettype($err_message) != "Integer"){
         throw new Exception("The provided data is not valid: " + $keyArray[$err_message] + " is not of type " + $typeArray[$err_message])
      }else{
        throw new Exception($e -> GetMessage())
    }
    
    $invalidCalculationList = checkIDArrayForInvalidValues([$id])
      
    if(count($invalidCalculationList) > 0){
      throw new Exception("You provided an invalid Calculation ID. Please check your input!")
    }    
    
    $client = new GuzzleHTTP\Client()
      
    $res = $client -> request("GET", ($this -> BaseURL) + $endpoint + "?id=" + json_encode($IDArray) + "&returnOnlyStatusBool=" + json_encode($returnOnlyStatusBool), ["headers" => array("Accept" => "application/json", "Content-Type" => "application/json"), "auth" => array($this -> apiUserName => $this -> apiPassword)])
    
    if(($this -> debugging) == True){
      echo $res -> getStatusCode()
      echo $res -> getBody()
    }
    
    return $res
   }catch(Exception $e){
      
     if($this -> debugging){
       echo ($e -> GetMessage())
     }
      
     throw new Exception($e -> GetMessage())
   }   
} 
  
>
