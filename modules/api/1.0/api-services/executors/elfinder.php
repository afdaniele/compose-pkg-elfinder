<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use \system\packages\elfinder\ElFinderWrapper;
use \system\classes\Core;


function execute(&$service, &$actionName, &$arguments) {
    $action = $service['actions'][$actionName];
    Core::startSession();
    //
    switch ($actionName) {
        case 'connector':
            $connector = ElFinderWrapper::getConnector();
            try {
                $connector->run();
            } catch (Exception $e) {
                return response400BadRequest($e->getMessage());
            }
            die();
            break;
        //
        default:
            return response404NotFound(sprintf("The command '%s' was not found", $actionName));
            break;
    }
}//execute

?>
