<?php
namespace Dframe\Asset;

use Dframe\BaseException;
use Dframe\Router;

use Assetic\Asset\FileAsset;
use Assetic\Filter\CssImportFilter;
use Assetic\Filter\CssRewriteFilter;
use Assetic\Filter;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetReference;
use Assetic\Filter\GoogleClosure;

/**
* Slawek Kaleta
*/

class Assetic extends Router
{

    private function checkDir($path){
        if(!is_dir(appDir.$path)){
            if(!mkdir(appDir.$path)){
                throw new BaseException('Unable to create'.appDir.$path);
            }
        }

    }

    public function assetJs($sUrl = null, $path = null){

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
                return $srcPath;

            //Rekonstruujemy sciezki
            $relDir = explode('/', $sUrl);
            array_pop($relDir);
            $subDir = "";
            foreach ($relDir as $dir) {
                $subDir .= "/".$dir;
                $fileDst = appDir.$path.$subDir;

                if(!is_dir($fileDst)){
                    if(!mkdir($fileDst)){
                        throw new BaseException('Unable to create new directory');
                    }
                }
            }


            if(!is_writable(appDir.$path))
                throw new BaseException('Unable to get an app/view/'.$path);

                $css = new AssetCollection(array(
                    new FileAsset($srcPath),
                ), array(
                    new GoogleClosure\CompilerApiFilter(),
                    //new DartFilter(),
                ));

                file_put_contents($dstPath, $css->dump());

                //if(!file_put_contents($dstPath, $css->dump()));
                //    throw new BaseException('Unable to copy an asset');

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
                $fileDst = appDir.$path.$subDir;

                if(!is_dir($fileDst)){
                    if(!mkdir($fileDst)){
                        throw new BaseException('Unable to create new directory');
                    }
                }
            }


            if(!is_writable(appDir.$path))
                throw new BaseException('Unable to get an app/view/'.$path);

                $css = new AssetCollection(array(
                    new FileAsset($srcPath),
                ), array(
                    // Windows Java
                    //new Yui\CssCompressorFilter('C:\yuicompressor-2.4.7\build\yuicompressor-2.4.7.jar', 'java'),
                    new CssImportFilter(),
                    new CssRewriteFilter(),
                ));

                file_put_contents($dstPath, $css->dump());

                //if(!file_put_contents($dstPath, $css->dump()));
                //    throw new BaseException('Unable to copy an asset');

            }
           
        //Zwrocenie linku do kopii
        $sExpressionUrl = $sUrl;
        $sUrl = $this->requestPrefix.HTTP_HOST.'/'.$path.'/';
        $sUrl .= $sExpressionUrl;
        
        return $sUrl;
    }
}