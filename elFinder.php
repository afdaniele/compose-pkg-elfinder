<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\packages\elfinder;

use elFinder;
use elFinderConnector;
use system\classes\Configuration;
use \system\classes\Core;


/**
 *   Module for managing AWS SDK Loading
 */
class ElFinderWrapper {
    
    private static $initialized = false;
    private static $TRASH_DIR = "/tmp/elfinder-trash";
    
    // disable the constructor
    private function __construct() {
    }
    
    /** Initializes the module.
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the function succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the function succeded.
     *        The `data` field contains errors when `success` is `FALSE`.
     */
    public static function init(): array {
        if (!self::$initialized) {
            $pkg_dir = Core::getPackageRootDir("elfinder");
            $private_data_dir = join_path($pkg_dir, "data", "private");
            $autoload = join_path($private_data_dir, "composer", "vendor", "autoload.php");
            require_once $autoload;
            //
            self::$initialized = true;
            return ['success' => true, 'data' => null];
        } else {
            return ['success' => true, 'data' => "Module already initialized!"];
        }
    }//init
    
    /** Returns whether the module is initialized.
     *
     * @retval boolean
     *        whether the module is initialized.
     */
    public static function isInitialized(): bool {
        return self::$initialized;
    }//isInitialized
    
    /** Safely terminates the module.
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the function succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the function succeded.
     *        The `data` field contains errors when `success` is `FALSE`.
     */
    public static function close(): array {
        // do stuff
        return ['success' => true, 'data' => null];
    }//close
    
    
    // =======================================================================================================
    // Public functions
    
    public static function getConnector(): array {
        // Documentation for connector options:
        // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
        // get configuration
        $res = Core::getPackageSettings("elfinder");
        if (!$res['success']) return $res;
        // get roots
        $res = $res['data']->get("mounts", []);
        if (!$res['success']) return $res;
        $roots = $res['data'];
        // compile elfinder connector options
        $opts = [
            'roots' => array_map(function ($r){
                return [
                    'driver'        => $r['driver'],                // driver for accessing file system (REQUIRED)
                    'path'          => $r['path'],                  // path to files (REQUIRED)
                    'alias'         => $r['alias'],                 // name of the mountpoint in the file manager
                    'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                    'uploadDeny'    => explode(',', $r['upload']['mime_types_deny']),       // All Mimetypes not allowed to upload
                    'uploadAllow'   => explode(',', $r['upload']['mime_types_allow']),      // All Mimetypes allowed to upload
                    'uploadOrder'   => explode(',', $r['upload']['strategy']),              // Rules order
                ];
            }, array_values($roots))
        ];
        // add a trash folder
        mkdir(ElFinderWrapper::$TRASH_DIR, 0777, true);
        array_push($opts['roots'], [
            'id'            => '1',
			'driver'        => 'Trash',
			'path'          => ElFinderWrapper::$TRASH_DIR,
			'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
			'uploadDeny'    => ['none'],
			'uploadAllow'   => ['all'],
			'uploadOrder'   => ['allow', 'deny']
        ]);
        // create connector
        return [
            'success' => true,
            'data' => new elFinderConnector(new elFinder($opts))
        ];
    }//getConnector
    
    
    // =======================================================================================================
    // Private functions
    
    private static function _formatURL($url) {
        $base = Configuration::$BASE;
        return str_replace('~', $base, str_replace('~/', '~', $url));
    }//_formatURL
    
}//AWS


