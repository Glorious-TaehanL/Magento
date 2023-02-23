<?php
  /**
   * Copyright © 2016 Magento. All rights reserved.
   * See COPYING.txt for license details.
   */
  namespace Magento\CustomerExtendField\Setup;


  use Magento\Framework\Setup\InstallDataInterface;
  use Magento\Framework\Setup\ModuleContextInterface;
  use Magento\Framework\Setup\ModuleDataSetupInterface;
  use Magento\Customer\Model\Customer;
  use Magento\Customer\Setup\CustomerSetupFactory;

  class InstallData implements InstallDataInterface
  {
  
      private $customerSetupFactory;
  
      /**
       * Constructor
       *
       * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
       * 
       */
      public function __construct(
          CustomerSetupFactory $customerSetupFactory
      ) {
          $this->customerSetupFactory = $customerSetupFactory;
      }
  
      /**
       * {@inheritdoc}
       */
      public function install(
          ModuleDataSetupInterface $setup,
          ModuleContextInterface $context
      ) {
          $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

          $customerSetup->addAttribute(Customer::ENTITY, 'certificate_br', [
              'type' => 'int',
              'label' => 'Certificate Business registration num#',
              'input' => 'text',
              'required' => false,
              'visible' => true,
              'position' => 0,
              'sort_order' => 0,
              'system' => false,
              'backend' => '',
              'is_used_in_grid' => false,
              'is_visible_in_grid' => false,
              'is_filterable_in_grid' => false,
              'is_searchable_in_grid' => false,
          ]);
          
          $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'certificate_br')
          ->addData(['used_in_forms' => [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]
    ]);
          $attribute->save();
    }
}
  