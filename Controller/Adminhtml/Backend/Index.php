<?php



namespace Redgecko\Magnalister\Controller\Adminhtml\Backend;

use ML;
use MLSetting;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        //checking if the magento 2 user have the permissions to access magnalister plugin
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $authorization = $objectManager->get(\Magento\Framework\AuthorizationInterface::class);
        if (!$authorization->isAllowed('Redgecko_Magnalister::menu_item')) {
            throw new \Magento\Framework\Exception\LocalizedException(__('You are not authorized to access magnalister plugin. 
            Please contact the administrator to provide access in System -> User Roles -> Role Name -> Role Resources'));
        }
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->prepareWritablePaths();
    }
    public function execute()
    {
        //echo 'test';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $dir = $objectManager->get('Magento\Framework\Module\Dir');
        /**  @var $dir \Magento\Framework\Module\Dir */


        $appPath = $dir->getDir('Redgecko_Magnalister');
        if (file_exists($appPath . '/../MagnalisterLibrary/Core/ML.php')) {
            $_PluginPath = $appPath . '/../MagnalisterLibrary/Core/ML.php';
        }elseif (file_exists($appPath . '/../magnalisterlibrary/Core/ML.php')) {
            $_PluginPath = $appPath . '/../magnalisterlibrary/Core/ML.php';
        }

        if (file_exists($_PluginPath)) {
            require_once($_PluginPath);
            $output = ML::gi()->run();
            $sClientVersion = MLSetting::gi()->get('sClientBuild');
            $MLbodyClass = implode(' ', MLSetting::gi()->get('aBodyClasses'));
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Redgecko_Magnalister::menu');
        //$resultPage->getConfig()->getTitle()->prepend(__('magnalister'));

        $MLbodyClass =explode(" ", $MLbodyClass);

        foreach ($MLbodyClass as $value){
            $resultPage->getConfig()->addBodyClass($value);
        }
        /*  foreach(MLSetting::gi()->get('aCss') as $sFile){
              $resultPage->getConfig()->addRemotePageAsset(MLHttp::gi()->getResourceUrl('css/'.sprintf($sFile, $sClientVersion)), 'css', ['attributes' => 'rel="alternate" type="application/rss+xml" title="' . 'stylesheet' . '"']);
          }*/

        /* foreach (array_unique(MLSetting::gi()->get('aJs')) as $sFile) {
             $resultPage->getConfig()->addRemotePageAsset(MLHttp::gi()->getResourceUrl('js/'.sprintf($sFile, $sClientVersion), false), 'js', ['attributes' => 'charset="UTF-8"  crossorigin="*"']);
         }*/
        return $resultPage;
    }

    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Redgecko_Magnalister::menu');
    }

    private function prepareWritablePaths() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directory = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');

        $sWritablePath = $directory->getPath('cache').DIRECTORY_SEPARATOR.'magnalister'.DIRECTORY_SEPARATOR;
        $sLogPath = $directory->getPath('log').DIRECTORY_SEPARATOR.'RedMagnalisterMG2'.DIRECTORY_SEPARATOR;

        if (!is_dir($sWritablePath)) {
            @mkdir($sWritablePath, 0755, true);
        }
        if (is_dir($sWritablePath)) {
            define('MAGNALISTER_WRITABLE_DIRECTORY', $sWritablePath);
        }

        if (!is_dir($sLogPath)) {
            @mkdir($sLogPath, 0755, true);
        }

        if (is_dir($sLogPath)) {
            define('MAGNALISTER_LOG_DIRECTORY', $sLogPath);
        }

    }
}
