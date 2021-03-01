<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use system\classes\Core;
use system\classes\Utils;
use system\packages\elfinder\ElFinderWrapper;

require_once $GLOBALS['__SYSTEM__DIR__'] . 'templates/forms/SmartForm.php';


$pkg = "elfinder";


$res = Core::getPackageSettings($pkg);
$package_settings = $res['data'];


$config_schema = $package_settings->getSchema();
$config_values = $package_settings->asArray(true);

echoArray($config_schema->defaults());

// create and render form from schema and values
$form = new SmartForm($config_schema, $config_values);
//$form->render();

//$conn = ElFinderWrapper::getConnector();


?>


<button type="button" class="btn btn-success" id="show-data-button" style="float:right">
    <span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span>
    Show data
</button>
<br/>
<br/>
<br/>

<pre id="__data"></pre>

<script type="text/javascript">
    $('#show-data-button').on('click', function(){
        let form = ComposeForm.get("<?php echo $form->formID ?>");
        // get form data
        let data = form.serialize();
        // show data
        $("#__data").html(JSON.stringify(data, null, 4));
    });
</script>
