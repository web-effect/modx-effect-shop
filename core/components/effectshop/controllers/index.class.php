<?php
	
class effectshopIndexManagerController extends modExtraManagerController {
	

    public function getPageTitle() {
    	return 'Shop Manager';
    }
    public function getTemplateFile() {
        
        return __DIR__ . '/home.tpl';
    }
	
}