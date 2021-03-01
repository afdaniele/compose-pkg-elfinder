<?php

use \system\classes\Configuration;
use \system\classes\Core;


$js_path = sprintf("%sdata/elfinder/js", Configuration::$BASE);
$connector_php = Core::getAPIurl("elfinder", "connector");

$this_pkg = "elfinder";
$requirejs_ver = "2.3.6";
$jquery_ver = "3.4.1";
$jquery_ver_legacy = "1.12.4";
$jquery_ui_ver = "1.12.1";

$requirejs_js = Core::getJSscriptURL(sprintf('require.%s.min.js', $requirejs_ver), $this_pkg);
$jquery_ui_css = Core::getCSSstylesheetURL(sprintf('jquery-ui.%s.css', $jquery_ui_ver), $this_pkg);
?>


<style type="text/css">
    #page_container{
      min-width: 100%;
    }
    
    ._ctheme_content {
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        border-top: 1px solid black;
        border-left: 1px solid black;
    }
    
    #elfinder {
        width: 100%;
        height: 100%;
        position: absolute;
        bottom: 0;
        top: 0;
        left: 0;
        right: 0;
    }
    
    /* Disable resize icon at the bottom-right corner of elFinder */
    .ui-resizable-handle {
        display:none !important;
    }
</style>


<!-- Require JS (REQUIRED) -->
<!-- Rename "main.default.js" to "main.js" and edit it if you need configure elFInder options or any things -->
<script type="application/javascript" src="<?php echo $requirejs_js ?>"></script>
<script type="application/javascript">
    
    (function(){
        "use strict";
        var
            // Detect language (optional)
            lang = (function() {
                var locq = window.location.search,
                    map = {
                        'pt' : 'pt_BR',
                        'ug' : 'ug_CN',
                        'zh' : 'zh_CN'
                    },
                    full = {
                        'zh_tw' : 'zh_TW',
                        'zh_cn' : 'zh_CN',
                        'fr_ca' : 'fr_CA'
                    },
                    fullLang, locm, lang;
                if (locq && (locm = locq.match(/lang=([a-zA-Z_-]+)/))) {
                    // detection by url query (?lang=xx)
                    fullLang = locm[1];
                } else {
                    // detection by browser language
                    fullLang = (navigator.browserLanguage || navigator.language || navigator.userLanguage || '');
                }
                fullLang = fullLang.replace('-', '_').substr(0,5).toLowerCase();
                if (full[fullLang]) {
                    lang = full[fullLang];
                } else {
                    lang = (fullLang || 'en').substr(0,2);
                    if (map[lang]) {
                        lang = map[lang];
                    }
                }
                return lang;
            })(),
            
            // Start elFinder (REQUIRED)
            start = function(elFinder, editors, config) {
                // load jQueryUI CSS
                elFinder.prototype.loadCss("<?php echo $jquery_ui_css ?>");
                
                $(function() {
                    var optEditors = {
                            commandsOptions: {
                                edit: {
                                    editors: Array.isArray(editors)? editors : []
                                }
                            }
                        },
                        opts = {};
                    
                    // Interpretation of "elFinderConfig"
                    if (config && config.managers) {
                        $.each(config.managers, function(id, mOpts) {
                            opts = Object.assign(opts, config.defaultOpts || {});
                            // editors marges to opts.commandOptions.edit
                            try {
                                mOpts.commandsOptions.edit.editors = mOpts.commandsOptions.edit.editors.concat(editors || []);
                            } catch(e) {
                                Object.assign(mOpts, optEditors);
                            }
                            // Make elFinder
                            $('#' + id).elfinder(
                                // 1st Arg - options
                                $.extend(true, { lang: lang }, opts, mOpts || {}),
                                // 2nd Arg - before boot up function
                                function(fm, extraObj) {
                                    // `init` event callback function
                                    fm.bind('init', function() {
                                        // Optional for Japanese decoder "encoding-japanese"
                                        if (fm.lang === 'ja') {
                                            require(
                                                [ 'encoding-japanese' ],
                                                function(Encoding) {
                                                    if (Encoding && Encoding.convert) {
                                                        fm.registRawStringDecoder(function(s) {
                                                            return Encoding.convert(s, {to:'UNICODE',type:'string'});
                                                        });
                                                    }
                                                }
                                            );
                                        }
                                    });
                                }
                            );
                        });
                    } else {
                        alert('"elFinderConfig" object is wrong.');
                    }
                });
            },
            
            // JavaScript loader (REQUIRED)
            load = function() {
                require(
                    [
                        'elfinder',
                        'extras/editors.default.min',              // load text, image editors
                        'elFinderConfig'
                    ],
                    start,
                    function(error) {
                        alert(error.message);
                    }
                );
            },
            
            // is IE8 or :? for determine the jQuery version to use (optional)
            old = (typeof window.addEventListener === 'undefined' && typeof document.getElementsByClassName === 'undefined')
                   ||
                  (!window.chrome && !document.unqueID && !window.opera && !window.sidebar && 'WebkitAppearance' in document.documentElement.style && document.body.style && typeof document.body.style.webkitFilter === 'undefined');
    
        // config of RequireJS (REQUIRED)
        require.config({
            baseUrl : "<?php echo $js_path ?>",
            paths : {
                'jquery': old? "../js_static/jquery.<?php echo $jquery_ver_legacy ?>.min" : "../js_static/jquery.<?php echo $jquery_ver ?>.min",
                'jquery-ui': "../js_static/jquery-ui.1.12.1.min",
                'elfinder' : 'elfinder.min'
            },
            waitSeconds : 10 // optional
        });
    
        // check elFinderConfig and fallback
        // This part don't used if you are using elfinder.html, see elfinder.html
        if (! require.defined('elFinderConfig')) {
            define('elFinderConfig', {
                // elFinder options (REQUIRED)
                // Documentation for client options:
                // https://github.com/Studio-42/elFinder/wiki/Client-configuration-options
                defaultOpts : {
                    url : "<?php echo $connector_php ?>", // connector URL (REQUIRED)
                    height: "auto",
                    commandsOptions : {
                        edit : {
                            extraOptions : {
                                // set API key to enable Creative Cloud image editor
                                // see https://console.adobe.io/
                                creativeCloudApiKey : '',
                                // browsing manager URL for CKEditor, TinyMCE
                                // uses self location with the empty value
                                managerUrl : ''
                            }
                        },
                        quicklook : {
                            // to enable CAD-Files and 3D-Models preview with sharecad.org
                            sharecadMimes : ['image/vnd.dwg', 'image/vnd.dxf', 'model/vnd.dwf', 'application/vnd.hp-hpgl', 'application/plt', 'application/step', 'model/iges', 'application/vnd.ms-pki.stl', 'application/sat', 'image/cgm', 'application/x-msmetafile'],
                            // to enable preview with Google Docs Viewer
                            googleDocsMimes : ['application/pdf', 'image/tiff', 'application/vnd.ms-office', 'application/msword', 'application/vnd.ms-word', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/postscript', 'application/rtf'],
                            // to enable preview with Microsoft Office Online Viewer
                            // these MIME types override "googleDocsMimes"
                            officeOnlineMimes : ['application/vnd.ms-office', 'application/msword', 'application/vnd.ms-word', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.oasis.opendocument.text', 'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.oasis.opendocument.presentation']
                        }
                    },
                    // bootCalback calls at before elFinder boot up
                    bootCallback : function(fm, extraObj) {
                        /* any bind functions etc. */
                        fm.bind('init', function() {
                            // any your code
                        });
                        // for example set document.title dynamically.
                        var title = document.title;
                        fm.bind('open', function() {
                            var path = '',
                                cwd  = fm.cwd();
                            if (cwd) {
                                path = fm.path(cwd.hash) || null;
                            }
                            document.title = path? path + ':' + title : title;
                        }).bind('destroy', function() {
                            document.title = title;
                        });
                    }
                },
                managers : {
                    // 'DOM Element ID': { /* elFinder options of this DOM Element */ }
                    'elfinder': {}
                }
            });
        }
    
        // load JavaScripts (REQUIRED)
        load();
    })();
</script>

<!-- Element where elFinder will be created (REQUIRED) -->
<div id="elfinder"></div>