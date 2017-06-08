<?php
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

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE
 *
 */

set_time_limit(120);

class Assetic extends Router
{

    private function checkDir($path){
        if(!is_dir($path)){
            if(!mkdir($path))
                throw new BaseException('Unable to create'.$path);

        }

    }

    public function assetJs($sUrl = null, $path = null, $compress = true){


        if(is_null($path)){
            $path = 'assets';
            if(isset($this->aRouting['assetsPath']) AND !empty($this->aRouting['assetsPath'])){
                $path = $this->aRouting['assetsPath'];
                $this->checkDir($path); // Create Dir if not exist
            }
        }


        //Podstawowe sciezki
        $srcPath = appDir.'../app/View/assets/'.$sUrl;
        $dstPath = appDir.$path.'/'.$sUrl;
        //Kopiowanie pliku jezeli nie istnieje
        if(!file_exists($dstPath)){
            if(!file_exists($srcPath))
                return $srcPath;

            //Rekonstruujemy sciezki
            $relDir = explode('/', $sUrl);
            array_pop($relDir);
            $subDir = "";
            foreach ($relDir as $dir) {
                $subDir .= "/".$dir;
                $this->checkDir($path.$subDir); // Create Dir if not exist
            }

            if(!is_writable(appDir.$path))
                throw new BaseException('Unable to get an app/View/'.$path);

            $js = file_get_contents($srcPath);

            if(ini_get('display_errors') == "off"){
                if($compress === true){
                   $jSqueeze = new JSqueeze();
                   $js = $jSqueeze->squeeze($js, true, true, false);
                }
            }

            if(!file_put_contents($dstPath, $js)){
                $msg = 'Unable to copy an asset From: '.$srcPath.' TO '.$dstPath;
                file_put_contents(appDir.'../app/View/logs/router.txt', date( 'Y-m-d h:m:s' ) . ' :: ' . $msg . "\n", FILE_APPEND);
            }

        }

        //Zwrocenie linku do kopii
        $sExpressionUrl = $sUrl;
        $sUrl = $this->requestPrefix.HTTP_HOST.'/'.$path.'/';
        $sUrl .= $sExpressionUrl;

        return $sUrl;
    }

    public function assetCss($sUrl = null, $path = null){

        if(is_null($path)){
            $path = 'assets';
            if(isset($this->aRouting['assetsPath']) AND !empty($this->aRouting['assetsPath'])){
                $path = $this->aRouting['assetsPath'];
                $this->checkDir($path);
            }
        }

        //Podstawowe sciezki
        $srcPath = appDir.'../app/View/assets/'.$sUrl;
        $dstPath = appDir.$path.'/'.$sUrl;
        //Kopiowanie pliku jezeli nie istnieje
        if(!file_exists($dstPath)){
            if(!file_exists($srcPath))
                return '';

            //Rekonstruujemy sciezki
            $relDir = explode('/', $sUrl);
            array_pop($relDir);
            $subDir = "";
            foreach ($relDir as $dir) {
                $subDir .= "/".$dir;
                $this->checkDir($path.$subDir); // Create Dir if not exist
            }

            if(!is_writable(appDir.$path))
                throw new BaseException('Unable to get an app/View/'.$path);

            $css = new AssetCollection(array(
                new FileAsset($srcPath),
            ), array(
                // Windows Java
                //new Yui\CssCompressorFilter('C:\yuicompressor-2.4.7\build\yuicompressor-2.4.7.jar', 'java'),
                new CssImportFilter(),
                new CssRewriteFilter(),
                new PhpCssEmbedFilter(),
                new CssMinFilter(),
            ));

            preg_match_all("/url\('([^\)]+?\.(woff2|woff|eot|ttf|svg))/", $css->dump(), $m);

            foreach ($m['1'] as $key => $url) {

                if(file_exists(appDir.'../app/View/assets/'.$subDir.'/'.$url)){

                    //var_dump(appDir.'../app/View/assets/'.$subDir.'/'.$url);

                    //Rekonstruujemy sciezki
                    $relDir = explode('/',$subDir.'/'.$url);
                    $endFile = end($relDir);

                    array_pop($relDir);
                    $subDir = "";
                    $i = 0;
                    foreach ($relDir as $key => $dir) {
                        $i++;
                        if($i < 2)
                            continue;

                        $subDir .= "/".$dir;
                        $fileDst = appDir.$path.$subDir;
                        $this->checkDir($path.$subDir);

                    }

                    $sourceCopyFile = appDir.'../app/View/assets/'.$subDir.'/'.$url;
                    // var_dump($sourceCopyFile);
                    $file = file_get_contents($sourceCopyFile);
                    if(!file_put_contents(appDir.$fileDst.'/'.$endFile, $file)){
                        $msg = 'Unable to copy an asset From: '.$srcPath.' TO '.$dstPath;
                        file_put_contents(appDir.'../app/View/logs/router.txt', date( 'Y-m-d h:m:s' ) . ' :: ' . $msg . "\n", FILE_APPEND);
            
                    }
                }


            }
            //file_put_contents($dstPath, $css->dump());
            if(!file_put_contents($dstPath, $css->dump())){
                $msg = 'Unable to copy an asset From: '.$srcPath.' TO '.$dstPath;
                file_put_contents(appDir.'../app/View/logs/router.txt', date( 'Y-m-d h:m:s' ) . ' :: ' . $msg . "\n", FILE_APPEND);
            }


            //if($copy === false);
            //   throw new BaseException('Unable to copy an asset'. $dstPath);
        }

        //Zwrocenie linku do kopii
        $sExpressionUrl = $sUrl;
        $sUrl = $this->requestPrefix.HTTP_HOST.'/'.$path.'/';
        $sUrl .= $sExpressionUrl;

        return $sUrl;
    }
}
