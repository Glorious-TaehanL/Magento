<?php

namespace Magento\CustomerExtendField\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Register implements ObserverInterface
{
    protected $_customerRepositoryInterface;
	protected $backendUrl;
	protected $_scopeConfig;
	protected $storeManager;
	protected $_transportBuilder ;
	protected $inlineTranslation ;
	protected $_settings;
    protected $coreRegistry ;

    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
		\Magento\Backend\Model\UrlInterface $backendUrl,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Registry $registry
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
		$this->backendUrl = $backendUrl;
		$this->_scopeConfig = $scopeConfig;
		$this->storeManager = $storeManager;
		$this->_transportBuilder = $transportBuilder;
		$this->inlineTranslation = $inlineTranslation;

        $this->coreRegistry = $registry;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$customer = $observer->getEvent()->getCustomer();
		$customer->setCustomAttribute('certificate_br',@$_POST['certificate_br'] );		

		$this->_customerRepositoryInterface->save($customer);

		// send email to admin
		try {
			$templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
			$templateVars = array(
								'store' => $this->storeManager->getStore(),
								'name' => $customer->getFirstname(),
								'email' => $customer->getEmail(),
								'url' => $this->backendUrl->getUrl('custom/index/edit', array('id' => $customer->getId())),
							);

			$from = [ 'email' => $this->_scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'name' => $this->_scopeConfig->getValue('trans_email/ident_sales/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ];

			$this->inlineTranslation->suspend();

            $to = $this->_scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

			$transport = $this->_transportBuilder->setTemplateIdentifier('customer_registered')
							->setTemplateOptions($templateOptions)
							->setTemplateVars($templateVars)
							->setFrom($from)
							->addTo($to)
							->getTransport();

			$transport->sendMessage();
			$this->inlineTranslation->resume();


		} catch (\Exception $e) {
			//var_dump($e->getMessage());
			//print 'done';
			//exit;
		}
		
    }
}