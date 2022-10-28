<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Asset;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Filter\CssImportFilter;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter\CssRewriteFilter;
use Assetic\Filter\PhpCssEmbedFilter;
use Dframe\Asset\Exceptions\AsseticException;
use Dframe\Router\Router;
use Patchwork\JSqueeze;

set_time_limit(120);

/**
 * Short Description.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Assetic extends Router
{

    /**
     * @param null|string $url
     * @param null|string $path
     * @param bool        $compress
     *
     * @return null|string
     * @throws AsseticException
     */
    public function assetJs($url = null, $path = null, $compress = true): ?string
    {
        // Basic paths
        $srcPath = $this->srcPath($url);
        $dstPath = $this->dstPath($url, $path);

        // Copying a file if it does not exist
        if (!file_exists($dstPath)) {
            if (!file_exists($srcPath)) {
                return $srcPath;
            }

            $pathInfo = pathinfo($dstPath);
            if (!file_exists($pathInfo['dirname'])) {
                if (!mkdir($pathInfo['dirname'], 0777, true)) {
                    throw new AsseticException('Unable to create' . $path, 403);
                }
            }

            $js = file_get_contents($srcPath);
            if ($compress === true and $this->routeMap['assets']['minifyJsEnabled'] == true) {
                $jSqueeze = new JSqueeze();
                $js = $jSqueeze->squeeze($js, true, true, false);
            }

            if (!file_put_contents($dstPath, $js)) {
                if (!defined('APP_DIR')) {
                    throw new AsseticException('Please Define APP_DIR in Main config.php', 500);
                }

                $msg = date('Y-m-d h:m:s') . ' :: Unable to copy an asset From: ' . $srcPath . ' TO ' . $dstPath . "\n";
                $out = fopen(self::LOG_DIR . self::LOG_FILE_NAME, 'w');
                fwrite($out, $msg);
                fclose($out);
            }
        }

        // Return the link to the copy
        $expressionUrl = $url;
        $url = $this->requestPrefix . $this->routeMap['assets']['cacheUrl'] . $path . '/';
        $url .= $expressionUrl;

        return $url;
    }

    /**
     * @param $url
     *
     * @return string
     */
    protected function srcPath($url): string
    {
        return $this->routeMap['assets']['assetsPath'] . $this->routeMap['assets']['assetsDir'] . '/' . $url;
    }

    /**
     * @param $url
     * @param $path
     *
     * @return string
     * @throws AsseticException
     */
    protected function dstPath($url, $path): string
    {
        if (is_null($path)) {
            $path = 'assets';
            if (isset($this->routeMap['assets']['assetsDir']) and !empty($this->routeMap['assets']['assetsDir'])) {
                $path = $this->routeMap['assets']['assetsDir'];
                $this->checkDir($path);
            }
            $dstPath = $this->routeMap['assets']['cachePath'] . $path . '/' . $url;
        } else {
            $dstPath = $this->routeMap['assets']['cachePath'] . $path;
        }

        return $dstPath;
    }

    /**
     * @param $path
     *
     * @throws AsseticException
     */
    protected function checkDir($path): void
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                throw new AsseticException('Unable to create' . $path, 403);
            }
        }
    }

    /**
     * @param null|string $url
     * @param null|string $path
     * @param bool        $compress
     *
     * @return null|string
     * @throws AsseticException
     */
    public function assetCss($url = null, $path = null, $compress = true): ?string
    {
        // Basic paths
        $srcPath = $this->srcPath($url);
        $dstPath = $this->dstPath($url, $path);

        // Copying a file if it does not exist
        if (!file_exists($dstPath)) {
            if (!file_exists($srcPath)) {
                return '';
            }

            $pathInfo = pathinfo($dstPath);
            if (!file_exists($pathInfo['dirname'])) {
                if (!mkdir($pathInfo['dirname'], 0777, true)) {
                    throw new AsseticException('Unable to create' . $path, 403);
                }
            }

            $args = [];
            //$args[] = new Yui\CssCompressorFilter('C:\yuicompressor-2.4.7\build\yuicompressor-2.4.7.jar', 'java'),

            if ($compress === true) {
                if ($this->routeMap['assets']['minifyCssEnabled'] == true) {
                    $args[] = new CssMinFilter();
                }

                $args[] = new PhpCssEmbedFilter();
                $args[] = new CssRewriteFilter();
                $args[] = new CssImportFilter();
            }

            $css = new AssetCollection([new FileAsset($srcPath),], $args);

            preg_match_all('/url\("([^\)]+?\.(woff2|woff|eot|ttf|svg|png|jpg|jpeg|gif))/', $css->dump(), $m);

            $srcPathInfo = pathinfo($srcPath);

            if (!defined('APP_DIR')) {
                throw new AsseticException('Please Define APP_DIR in Main config.php', 500);
            }

            foreach ($m['1'] as $key => $url) {
                $subPathInfo = pathinfo($pathInfo['dirname'] . '/' . $url);
                if (!file_exists($subPathInfo['dirname'])) {
                    if (!mkdir($subPathInfo['dirname'], 0777, true)) {
                        throw new AsseticException('Unable to create' . $path, 403);
                    }
                }

                if (!copy($srcPathInfo['dirname'] . '/' . $url, $pathInfo['dirname'] . '/' . $url)) {
                    $msg = date(
                            'Y-m-d h:m:s'
                        ) . ' :: Unable to copy an asset From: ' . $srcPathInfo['dirname'] . '/' . $url . ' TO ' . $pathInfo['dirname'] . '/' . $url . "\n";
                    $out = fopen(self::LOG_DIR . self::LOG_FILE_NAME, 'w');
                    fwrite($out, $msg);
                    fclose($out);
                }
            }

            if (!file_put_contents($dstPath, $css->dump())) {
                $msg = date('Y-m-d h:m:s') . ' :: Unable to copy an asset From: ' . $srcPath . ' TO ' . $dstPath . "\n";
                $out = fopen(self::LOG_DIR . self::LOG_FILE_NAME, 'w');
                fwrite($out, $msg);
                fclose($out);
            }
        }

        // Return the link to the copy
        $expressionUrl = $url;
        $url = $this->requestPrefix . $this->routeMap['assets']['cacheUrl'] . $path . '/';
        $url .= $expressionUrl;

        return $url;
    }
}
