<?php
namespace Redgecko\Magnalister\Controller\Adminhtml\Iframe;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\Dir;
use Magento\Framework\View\Result\PageFactory;
use ML;
use MLHttp;
use MLMessage;
use MLSetting;
use Throwable;

class Index extends Action {
    protected $_publicActions = ['index'];
    protected $resultPageFactory = false;

    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->prepareWritablePaths();
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute() {
        echo $this->DisplayMagnalister();
    }

    /**
     * @return string
     */
    public function DisplayMagnalister() {
        try {
            $objectManager = ObjectManager::getInstance();
            $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
            /**  @var $productMetadata Magento\Framework\App\ProductMetadataInterface */
            $sMagentoReversion = $productMetadata->getEdition();

            define("MLMAGNETOREVERSION", $productMetadata->getEdition());
            define("MLMAGENTOVERSION", $productMetadata->getVersion());

            $dir = $objectManager->get('Magento\Framework\Module\Dir');
            /**  @var $dir Dir */
            $appPath = $dir->getDir('Redgecko_Magnalister');
            if (file_exists($appPath.'/../MagnalisterLibrary/Core/ML.php')) {
                $_PluginPath = $appPath.'/../MagnalisterLibrary/Core/ML.php';
            }elseif (file_exists($appPath . '/../magnalisterlibrary/Core/ML.php')) {
                $_PluginPath = $appPath . '/../magnalisterlibrary/Core/ML.php';
            }
            $debugPrint = '';
            if (file_exists($_PluginPath)) {
                require_once($_PluginPath);
                $this->restoreIndividualProgrammings($appPath);
                $output = ML::gi()->run();
                $sClientVersion = MLSetting::gi()->get('sClientBuild');
                $MLCss = '';
                $MLJs = '';
                foreach (array_unique(MLSetting::gi()->get('aCss')) as $sFile) {
                    try {
                        $MLCss .= '
                        <link rel="stylesheet" type="text/css" href="' . MLHttp::gi()->getResourceUrl('css_' . $sFile) . '?' . $sClientVersion . '">';
                    } catch (Exception $ex) {
                        if (MLSetting::gi()->blDebug) {
                            MLMessage::gi()->addDebug($ex);
                        }
                    }
                }

                foreach (array_unique(MLSetting::gi()->get('aJs')) as $sFile) {
                    try {
                        $MLJs .= '
                    <script src="' . MLHttp::gi()->getResourceUrl('js_' . $sFile) . '?' . $sClientVersion . '" type="text/javascript"></script>';
                    } catch (Exception $ex) {
                        if (MLSetting::gi()->blDebug) {
                            MLMessage::gi()->addDebug($ex);
                        }
                    }
                }
                $MLBodyClass = implode(' ', MLSetting::gi()->get('aBodyClasses'));

            }
            header('Content-Type: text/html; charset=utf-8');
        } catch (Throwable $ex) {
            $blShowError = isset(ObjectManager::getInstance()->get('\Magento\Framework\App\RequestInterface')->getParams()['ml-debug']) || (class_exists('MLSetting') && MLSetting::gi()->blDebug);
            return ('<!DOCTYPE html>
            <html>
                <head>
                    <meta charset="utf-8">
                    <title>magnalister exception</title>
                </head>
                <body>' . ($blShowError ?
                    $ex->getMessage().'<br>'.
                    $ex->getFile().'<br>'.
                    $ex->getLine().'<pre>'.
                    $ex->getTraceAsString().'</pre>' :
                    'An error occurs, please contact <a href="https://www.magnalister.com/kontakt" class="ml-js-noBlockUi" target="_blank">magnalister-Service</a>
                ').'</body>
            </html>
        ');
        }


        return ' <!DOCTYPE html>            <html>                <head>                    <meta charset="utf-8">                    <title>magnalister Admin</title>                                       ' . $MLCss . '                    ' . $MLJs . '                </head>                <body class="' . $MLBodyClass . '">                    ' . $output . '                    <pre>' . $debugPrint . '</pre>                 </body>            </html>        ';

        //return ' ' . $output . '<pre>' . $debugPrint . '</pre>';
    }

    /**
     * This give back the option for individual programmings
     *  Store them into log folder /var/log/RedMagnalisterSW6/10_Cutomer
     *      this script will copy it to ML Codepool directory
     *
     */
    private function restoreIndividualProgrammings($appPath) {
        $_PluginPath = $appPath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'magnalisterlibrary'.DIRECTORY_SEPARATOR;
        foreach (array('00_Dev', '10_Customer') as $directory) {
            if (file_exists(MAGNALISTER_LOG_DIRECTORY.$directory) && !file_exists($_PluginPath.'Codepool'.DIRECTORY_SEPARATOR.$directory)) {
                \MLHelper::getFilesystemInstance()->cp(MAGNALISTER_LOG_DIRECTORY . $directory, $_PluginPath . 'Codepool' . DIRECTORY_SEPARATOR . $directory);
            }
        }
    }

    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Redgecko_Magnalister::menu');
    }


    private function prepareWritablePaths() {
        $objectManager = ObjectManager::getInstance();
        $directory = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');

        $sWritablePath = $directory->getPath('cache').DIRECTORY_SEPARATOR.'magnalister'.DIRECTORY_SEPARATOR;
        $sLogPath = $directory->getPath('log').DIRECTORY_SEPARATOR.'RedMagnalisterMG2'.DIRECTORY_SEPARATOR;

        if (!is_dir($sWritablePath)) {
            mkdir($sWritablePath);
        }
        if (is_dir($sWritablePath)) {
            define('MAGNALISTER_WRITABLE_DIRECTORY', $sWritablePath);
        }

        if (!is_dir($sLogPath)) {
            mkdir($sLogPath);
        }

        if (is_dir($sLogPath)) {
            define('MAGNALISTER_LOG_DIRECTORY', $sLogPath);
        }

    }
}
