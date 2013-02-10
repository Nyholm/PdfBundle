<?php
/**
 * Siphoc
 *
 * @author      Jelmer Snoeck <jelmer@siphoc.com>
 * @copyright   2013 Siphoc
 * @link        http://siphoc.com
 */

namespace Siphoc\PdfBundle\Util;

use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

/**
 * Convert a view to a proper inline CSS html page.
 *
 * @author Jelmer Snoeck <jelmer@siphoc.com>
 */
class CssToInline
{
    /**
     * The converter used for the inline replacement.
     *
     * @var CssToInlineStyles
     */
    protected $converter;

    /**
     * The basepath for our css files. This is basically the /web folder.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Follow external stylesheet files or not?
     *
     * @var boolean
     */
    protected $externalStylesheets = false;

    /**
     * Initiate the CssToInline converter for Symfony2.
     *
     * @param CssToInlineStyles $converter
     */
    public function __construct(CssToInlineStyles $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Disable the usage of the <link stylesheets> tag in our HTML.
     *
     * @return CssToInline
     */
    public function disableExternalStylesheets()
    {
        $this->externalStylesheets = false;

        return $this;
    }

    /**
     * Enable the usage of the <link stylesheets> tag in our HTML.
     *
     * @return CssToInline
     */
    public function enableExternalStylesheets()
    {
        $this->externalStylesheets = true;

        return $this;
    }

    /**
     * Extract the external stylesheets from the specified HTML if the option is
     * enabled. If the stylesheet is not in the form of a url, prepend our
     * basePath.
     *
     * @TODO: Improve regex to contain a wider range of valid link syntaxes.
     *
     * @param string $html
     * @return array
     */
    public function extractExternalStylesheets($html)
    {
        $matches = array();

        preg_match_all(
            '/<link rel="stylesheet" href="(?P<links>.*)">/',
            $html, $matches
        );

        return $this->createStylesheetPaths($matches['links']);
    }

    /**
     * Check if a stylesheet is a local stylesheet or an external stylesheet. If
     * it is a local stylesheet, prepend our basepath to the link so we can
     * properly fetch the data to insert.
     *
     * @param array $stylesheets
     * @return array
     */
    private function createStylesheetPaths(array $stylesheets)
    {
        $sheets = array();

        foreach ($stylesheets as $key => $sheet) {
            if (!$this->isExternalStylesheet($sheet)) {
                $sheet = $this->getBasePath() . $sheet;
            }

            $sheets[] = $sheet;
        }

        return $sheets;
    }

    /**
     * Check if the given string is a string for a local stylesheet or an
     * external stylesheet.
     *
     * @TODO: Improve regex to contain bigger range of urls.
     *
     * @param string $url
     * @return boolean
     */
    private function isExternalStylesheet($url)
    {
        if (1 === preg_match('/(http|https):\/\//', $url)) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the BasePath used for this inline action.
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Retrieve the previously set converter.
     *
     * @return CssToInlineStyles
     */
    public function getConverter()
    {
        return $this->converter;
    }

    /**
     * Set the base path we'll use to fetch our css files from.
     *
     * @param string $basePath      The base path where our css files are.
     * @return CssToInline
     */
    public function setBasePath($basePath)
    {
        $this->basePath = (string) $basePath;

        return $this;
    }

    /**
     * Are we allowed to follow <link stylesheet> tags to include these
     * stylesheets in our page?
     *
     * @return boolean
     */
    public function useExternalStylesheets()
    {
        return $this->externalStylesheets;
    }
}
