<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\packages\elfinder;

use elFinder;
use elFinderConnector;
use \system\classes\Core;


/**
 *   Module for managing AWS SDK Loading
 */
class ElFinderWrapper {
    
    private static $initialized = false;
    
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
    
    public static function getConnector(): \elFinderConnector {
        // Documentation for connector options:
        // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
        $opts = [
            // 'debug' => true,
            'roots' => [
                // Items volume
                [
                    'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                    'path'          => '/tmp/',                 // path to files (REQUIRED)
                    'URL'           => dirname($_SERVER['PHP_SELF']) . '/../files/', // URL to files (REQUIRED)
                    'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                    'uploadDeny'    => ['all'],                // All Mimetypes not allowed to upload
                    'uploadAllow'   => ['image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'], // Mimetype `image` and `text/plain` allowed to upload
                    'uploadOrder'   => ['deny', 'allow'],      // allowed Mimetype `image` and `text/plain` only
                ],
                // Trash volume
                [
                    'id'            => '1',
                    'driver'        => 'Trash',
                    'path'          => '/tmp/',
                    'tmbURL'        => dirname($_SERVER['PHP_SELF']) . '/../files/.trash/.tmb/',
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                    'uploadDeny'    => ['all'],                // Recomend the same settings as the original volume that uses the trash
                    'uploadAllow'   => ['image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'], // Same as above
                    'uploadOrder'   => ['deny', 'allow'],      // Same as above
                ],
            ]
        ];
        // create connector
        return new elFinderConnector(new elFinder($opts));
    }//getConnector
    
    
    // =======================================================================================================
    // Private functions
    
    
}//AWS


