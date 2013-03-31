<?php

function __autoload($strClassName) {
    $arrClassPath = explode('\\', $strClassName);
    $strFilename = array_pop($arrClassPath) . '.php';
    $arrSubFolders = array('/', '/_Abstract/', '/_Interfaces/', '/_Traits/');
    $strNamespacePath = '' . implode('/', $arrClassPath);

    foreach ($arrSubFolders as $strSubFolder) {
        $strFilepath = MERCILESS_CLASSDIR . '/' . $strNamespacePath . $strSubFolder . $strFilename;
        if (file_exists($strFilepath)) {
            require $strFilepath;
            break;
        }
    }
}
