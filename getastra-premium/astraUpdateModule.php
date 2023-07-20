<?php

namespace AstraPrefixed;

if (\defined('ASTRA_UPDATER_CURRENT_DIR')) {
    return;
}
\define('ASTRA_UPDATER_CURRENT_DIR', \dirname(__FILE__));
\define('ASTRA_UPDATER_OLD_FILES_DIR', \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'astraVar' . \DIRECTORY_SEPARATOR . 'old_files');
\define('ASTRA_UPDATER_IGNORE', ['astraUpdateModule.php', '.update2', '.vscode', 'astraStorage']);
\define('ASTRA_UPDATER_EXTRACT_DIR', \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'astraVar' . \DIRECTORY_SEPARATOR . 'extract_updater');
\define('ASTRA_UPDATER_LOCK_FILE', \ASTRA_UPDATER_CURRENT_DIR . \DIRECTORY_SEPARATOR . '.update2');
$astraUpdateScriptErrors = [];
//will be used as a globalvariable
$astraVersionFile = \ASTRA_UPDATER_EXTRACT_DIR . \DIRECTORY_SEPARATOR . '.version';
if (\file_exists($astraVersionFile)) {
    $astraNewVersion = \file_get_contents($astraVersionFile);
} else {
    $astraNewVersion = 'Unknown version';
}
if (!\file_exists(\ASTRA_UPDATER_LOCK_FILE)) {
    //echo "lock file not found exiting \n";
    return;
}
if (!\function_exists('AstraPrefixed\\astraCpy')) {
    function astraCpy($source, $dest, $keepCurrentScriptInSource = \true)
    {
        if (\is_dir($source)) {
            $dir_handle = \opendir($source);
            while ($file = \readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (\is_dir($source . "/" . $file)) {
                        if (!\is_dir($dest . "/" . $file)) {
                            \mkdir($dest . "/" . $file, 0755, \true);
                        }
                        astraCpy($source . "/" . $file, $dest . "/" . $file);
                        //recursive call
                    } else {
                        if (\strpos($dest . "/" . $file, 'astraUpdateModule.php') === \false) {
                            //if(astraNotPresentInIgnoreList($dest."/".$file)){
                            $result = \rename($source . "/" . $file, $dest . "/" . $file);
                            if (!$result) {
                                //$GLOBALS['astraUpdateScriptErrors'][] = "rename() operation failed, source - {$source}/{$file}, destination - {$dest}/{$file}";
                                $GLOBALS['astraUpdateScriptErrors'][] = "Astra plugin file with path - {$source}/{$file} ; could not be renamed{copied} to destination path - {$dest}/{$file} ; during update";
                            }
                        } else {
                            if ($keepCurrentScriptInSource) {
                                $result2 = \copy($source . "/" . $file, $dest . "/" . $file);
                                if (!$result2) {
                                    //$GLOBALS['astraUpdateScriptErrors'][] = "copy() operation failed, source - {$source}/{$file}, destination - {$dest}/{$file}";
                                    $GLOBALS['astraUpdateScriptErrors'][] = "Astra plugin file with path - {$source}/{$file} ; could not be copied to destination path - {$dest}/{$file} ; during update";
                                }
                            }
                        }
                    }
                }
            }
            \closedir($dir_handle);
        } else {
            if (\strpos($dest, 'astraUpdateModule.php') === \false) {
                //if(astraNotPresentInIgnoreList($dest)){
                \rename($source, $dest);
            }
        }
    }
}
if (!\function_exists('AstraPrefixed\\astraRrmdir')) {
    /**
     *  function to delete old directories and files.
     *  basically it recursively empties the ASTRAROOT so that new files can be copied
     */
    function astraRrmdir($astraDir)
    {
        if (\is_dir($astraDir)) {
            $objects = \scandir($astraDir);
            foreach ($objects as $object) {
                if ('.' != $object && '..' != $object) {
                    if (\is_dir($astraDir . \DIRECTORY_SEPARATOR . $object) && !\is_link($astraDir . '/' . $object)) {
                        astraRrmdir($astraDir . \DIRECTORY_SEPARATOR . $object);
                        //recursive call
                    } else {
                        $file = $astraDir . \DIRECTORY_SEPARATOR . $object;
                        if (astraNotPresentInIgnoreList($file)) {
                            $result = \unlink($file);
                            if (!$result) {
                                //$GLOBALS['astraUpdateScriptErrors'][] = "unlink() operation failed for file - {$file}";
                                $GLOBALS['astraUpdateScriptErrors'][] = "Astra existing plugin file could not be deleted during update. Filepath -{$file}";
                            }
                        }
                    }
                }
            }
            if ($astraDir !== \ASTRA_UPDATER_CURRENT_DIR && astraNotPresentInIgnoreList($astraDir)) {
                //making sure to not delete root astra plugin folder
                $deleteDirResult = \rmdir($astraDir);
                if (!$deleteDirResult) {
                    $GLOBALS['astraUpdateScriptErrors'][] = "Astra existing directory could not be deleted during update. Dirpath -{$astraDir}";
                }
            }
        }
    }
}
if (!\function_exists('AstraPrefixed\\astraNotPresentInIgnoreList')) {
    /**
     * return true if not found in ignoreDir
     * false if found in ignore dir
     */
    function astraNotPresentInIgnoreList($filePath, array $ignoredList = \ASTRA_UPDATER_IGNORE)
    {
        foreach ($ignoredList as $ignoredFileSlug) {
            if (\strpos($filePath, $ignoredFileSlug) !== \false) {
                return \false;
            } else {
                continue;
                //continue if not found
            }
        }
        return \true;
    }
}
//first step remove lock file so that next request doesnt trigger this process again
\unlink(\ASTRA_UPDATER_LOCK_FILE);
//astraCpy(ASTRA_UPDATER_CURRENT_DIR,ASTRA_UPDATER_OLD_FILES_DIR); //operation not permitted error
//echo "copying old files done \n";
astraRrmdir(\ASTRA_UPDATER_CURRENT_DIR);
//echo "current dir cleared \n";
astraCpy(\ASTRA_UPDATER_EXTRACT_DIR, \ASTRA_UPDATER_CURRENT_DIR, \false);
//echo "new files copied";
if (isset($GLOBALS['astraUpdateScriptErrors']) && \is_array($GLOBALS['astraUpdateScriptErrors']) && \count($GLOBALS['astraUpdateScriptErrors']) > 0) {
    //assuming ASTRA ROOT is writable
    //write update to a text file in ASTRA ROOT
    $errorFileName = \ASTRA_UPDATER_CURRENT_DIR . \DIRECTORY_SEPARATOR . 'updateModuleErrors.txt';
    \file_put_contents($errorFileName, \json_encode($GLOBALS['astraUpdateScriptErrors']));
} else {
    //update successfull
    $updateSuccessFileName = \ASTRA_UPDATER_CURRENT_DIR . \DIRECTORY_SEPARATOR . 'updateSuccess.txt';
    $message['message'] = 'Astra Plugin update completed at - ' . \date('Y-m-d H:i:s');
    $message['version'] = $astraNewVersion;
    \file_put_contents($updateSuccessFileName, \json_encode($message));
}
