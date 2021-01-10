<?php
require_once('config.php');

$lang = isset($_GET['lang']) ? $_GET['lang'] : LANGUAGE;
$lang = substr($lang, 0, 5); // Lang code should be 5 or less characters

$uri = "http://cashregister.localhost/api/translation/$lang/";

// Get translations
$api_cashier = json_decode(file_get_contents($uri . 'CASHIERINTERFACE'), true);
$api_upload = json_decode(file_get_contents($uri . 'FILEUPLOAD'), true);
$api_submit = json_decode(file_get_contents($uri . 'BTNSUBMIT'), true);
$api_input = json_decode(file_get_contents($uri . 'INPUTBOX'), true);
$api_output = json_decode(file_get_contents($uri . 'OUTPUTBOX'), true);
$api_save = json_decode(file_get_contents($uri . 'SAVEASFILE'), true);

// check translations exist for lang code, or fall back to English
$tr_cashier = (isset($api_cashier['text'])) ? $api_cashier['text'] : 'Cashier Interface';
$tr_upload = (isset($api_upload['text'])) ? $api_upload['text'] :
    'Upload your file of amounts to calculate change denominations owed.';
$tr_submit = (isset($api_submit['text'])) ? $api_submit['text'] : 'Submit';
$tr_input = (isset($api_input['text'])) ? $api_input['text'] : 'Input';
$tr_output = (isset($api_output['text'])) ? $api_output['text'] : 'Output';
$tr_save = (isset($api_save['text'])) ? $api_save['text'] : 'Save output as file';

?><!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <title>Creative Cash Draw Solutions</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>

<body id="home">
    <div class="container">
        <div class="row justify-content-center">
            <div class="form-container col-12 col-sm-10 col-md-8 m-3 bg-light text-body">
                <form>
                    <h1 class="text-center">
                        Creative Cash Draw Solutions<br>
                        <small class="text-muted"><?= $tr_cashier ?></small>
                    </h1>
                    <hr class="separator">
                    <div class="form-group" id="errorHandler"></div>
                    <div class="form-group" id="fileGroup">
                        <label for="amountsFile"><?= $tr_upload ?></label>
                        <!-- File input text depends on system language, so we can't use our translations-->
                        <input type="file" class="form-control-file" id="amountsFile">
                    </div>
                    <div class="form-group text-right" id="submitGroup">
                        <button type="button" class="btn btn-primary" id="submitFile"><?= $tr_submit ?></button>
                    </div>
                    <hr class="separator" style="display: none;" id="submissionSeparator">
                    <div class="form-group" style="display: none;" id="inputGroup">
                        <label for="form-input"><?= $tr_input ?></label>
                        <textarea disabled class="form-control" id="form-input" rows="4"></textarea>
                    </div>
                    <div class="form-group" style="display: none;" id="outputGroup">
                        <label for="form-output"><?= $tr_output ?></label>
                        <textarea disabled class="form-control" id="form-output" rows="4"></textarea>
                    </div>
                    <div class="form-group text-right" style="display: none;" id="saveGroup">
                        <button type="button" class="btn btn-primary" id="saveFile"><?= $tr_save ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- load scripts (after content) -->
    <script type="text/javascript" src="node_modules/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="node_modules/popper.js/dist/umd/popper.min.js"></script>
    <script type="text/javascript" src="node_modules/bootstrap/dist/js/bootstrap.min.js" ></script>

    <script type="text/javascript" src="js/Register.js" ></script>
    <script type="text/javascript">
        // start Register
        new Register();
    </script>
</body>
</html>
