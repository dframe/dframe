<?php
/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Asset;

use Dframe\BaseException;
use Dframe\Router;
use Assetic\Asset\FileAsset;
use Assetic\Filter\CssImportFilter;
use Assetic\Filter\CssRewriteFilter;
use Assetic\Filter\PhpCssEmbedFilter;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetReference;
use Assetic\Filter\GoogleClosure;
use Patchwork\JSqueeze;

set_time_limit(120);

/**
 * Short Description
 *
 * @author Sławek Kaleta <slaszka@gmail.com>
 */
class Assetic extends Router
{

    private function _checkDir($path)
    {
        if (!is_dir($path)) {
            if (!mkdir($path)) {
                throw new BaseException('Unable to create'.$path);
            }
        }

    }

    public function assetJs($sUrl = null, $path = null, $compress = true)
    {

        if (is_null($path)) {
            $path = 'assets';
            if (isset($this->aRouting['assetsPath']) AND !empty($this->aRouting['assetsPath'])) {
                $path = $this->aRouting['assetsPath'];
                $this->_checkDir($path); // Create Dir if not exist
            }
        }

        //Podstawowe sciezki
        $srcPath =  APP_DIR.'View/assets/'.$sUrl;
        $dstPath =  APP_DIR.'../web/'.$path.'/'.$sUrl;
        //Kopiowanie pliku jezeli nie istnieje
        if (!file_exists($dstPath)) {
            if (!file_exists($srcPath)) {
                return $srcPath;
            }

            //Rekonstruujemy sciezki
            $relDir = explode('/', $sUrl);
            array_pop($relDir);
            $subDir = "";
            foreach ($relDir as $dir) {
                $subDir .= "/".$dir;
                $this->_checkDir($path.$subDir); // Create Dir if not exist
            }

            $savePath = APP_DIR.'../web/'.$path;
            if (!is_writable($savePath)) {
                throw new BaseException('Unable to get an '.$savePath);
            }

            $js = file_get_contents($srcPath);

            if (ini_get('display_errors') == "off") {
                if ($compress === true) {
                    $jSqueeze = new JSqueeze();
                    $js = $jSqueeze->squeeze($js, true, true, false);
                }
            }

            if (!file_put_contents($dstPath, $js)) {
                $msg = date('Y-m-d h:m:s') . ' :: Unable to copy an asset From: ' . $srcPath.  ' TO ' . $dstPath . "\n";
                $out = fopen(APP_DIR.'/View/logs/router.txt', "w");
                fwrite($out, $str);
                fclose($out);
            }

        }

        //Zwrocenie linku do kopii
        $sExpressionUrl = $sUrl;
        $sUrl = $this->requestPrefix.HTTP_HOST.'/'.$path.'/';
        $sUrl .= $sExpressionUrl;

        return $sUrl;
    }

    public function assetCss($sUrl = null, $path = null)
    {

        if (is_null($path)) {
            $path = 'assets';
            if (isset($this->aRouting['assetsPath']) AND !empty($this->aRouting['assetsPath'])) {
                $path = $this->aRouting['assetsPath'];
                $this->_checkDir($path);
            }
        }

        //Podstawowe sciezki
        $srcPath = APP_DIR.'View/assets/'.$sUrl;
        $dstPath = APP_DIR.'../web/'.$path.'/'.$sUrl;
        //Kopiowanie pliku jezeli nie istnieje
        if (!file_exists($dstPath)) {
            if (!file_exists($srcPath)) {
                return '';
            }

            //Rekonstruujemy sciezki
            $relDir = explode('/', $sUrl);
            array_pop($relDir);
            $subDir = "";
            foreach ($relDir as $dir) {
                $subDir .= "/".$dir;
                $this->_checkDir($path.$subDir); // Create Dir if not exist
            }

            $savePath = APP_DIR.'../web/'.$path;
            if (!is_writable($savePath)) {
                throw new BaseException('Unable to get an '.$savePath);
            }

            $css = new AssetCollection(
                array(
                new FileAsset($srcPath),
                ), array(
                // Windows Java
                //new Yui\CssCompressorFilter('C:\yuicompressor-2.4.7\build\yuicompressor-2.4.7.jar', 'java'),
                new CssImportFilter(),
                new CssRewriteFilter(),
                new PhpCssEmbedFilter(),
                new CssMinFilter(),
                )
            );

            preg_match_all("/url\('([^\)]+?\.(woff2|woff|eot|ttf|svg))/", $css->dump(), $m);

            foreach ($m['1'] as $key => $url) {

                if (file_exists(APP_DIR.'View/assets/'.$subDir.'/'.$url)) {

                    //var_dump(appDir.'../app/View/assets/'.$subDir.'/'.$url);

                    //Rekonstruujemy sciezki
                    $relDir = explode('/', $subDir.'/'.$url);
                    $endFile = end($relDir);

                    array_pop($relDir);
                    $subDir = "";
                    $i = 0;
                    foreach ($relDir as $key => $dir) {
                        $i++;
                        if ($i < 2) {
                            continue;
                        }

                        $subDir .= "/".$dir;
                        $fileDst = appDir.$path.$subDir;
                        $this->_checkDir($path.$subDir);

                    }

                    $sourceCopyFile = APP_DIR.'View/assets/'.$subDir.'/'.$url;
                    // var_dump($sourceCopyFile);
                    $file = file_get_contents($sourceCopyFile);
                    if (!file_put_contents($fileDst.'/'.$endFile, $file)) {
                        $msg = date('Y-m-d h:m:s') . ' :: Unable to copy an asset From: '.$srcPath.' TO '.$dstPath . "\n";
                        $out = fopen(APP_DIR.'/View/logs/router.txt', "w");
                        fwrite($out, $str);
                        fclose($out);
                    }
                }


            }
            //file_put_contents($dstPath, $css->dump());
            if (!file_put_contents($dstPath, $css->dump())) {
                $msg = date('Y-m-d h:m:s') . ' :: Unable to copy an asset From: '.$srcPath.' TO '.$dstPath . "\n";
                $out = fopen(APP_DIR.'/View/logs/router.txt', "w");
                fwrite($out, $str);
                fclose($out);
            }


            //if ($copy === false);
            //   throw new BaseException('Unable to copy an asset'. $dstPath);
        }

        //Zwrocenie linku do kopii
        $sExpressionUrl = $sUrl;
        $sUrl = $this->requestPrefix.HTTP_HOST.'/'.$path.'/';
        $sUrl .= $sExpressionUrl;

        return $sUrl;
    }
}
