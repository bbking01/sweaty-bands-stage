<?php

class Magestore_Gallery_Admin_ExportdesignController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout();
		return $this;
	}   
 
	public function exportcategoryAction() {
		$request        = $this->getRequest();
		$shipmentIds    = $request->getPost('shipment_ids', array());
		$fileName       = 'design_idea'.Mage::getSingleton('core/date')->date('d-m-Y_H-i-s').'_csv.csv';
		
		//prepare csv contents
		#prepare header
		$csv = '';
				
		$_columns = array(
			"Name",			
			"Image",			
			"Status",			
		);
		$designIdeaModel = Mage::getModel('gallery/album');
		$designIdeas = Mage::getModel('gallery/album')->getCollection();
		$data = array();
		foreach ($_columns as $column) {
			$data[] = '"'.$column.'"';
		}
		$csv .= implode(',', $data)."\n";
		#prepare data
		foreach($designIdeas as $designIdea){ 
			$idea = $designIdeaModel->load($designIdea->getAlbumId());
			$data = array();
			$data['title'] = $idea->getTitle();
			$data['filename'] = $idea->getFilename();		
			$data['status'] = $idea->getStatus();	
			$csv .= implode(',', $data)."\n";
		}
		//now $csv varaible has csv data as string

		$this->_prepareDownloadResponse($fileName, $csv); 
	}
	
	public function exportdesignAction() {
		$request        = $this->getRequest();
		$shipmentIds    = $request->getPost('shipment_ids', array());
		$fileName       = 'design_idea'.Mage::getSingleton('core/date')->date('d-m-Y_H-i-s').'_csv.csv';

		//prepare csv contents
		#prepare header
		$csv = '';
				
		$_columns = array(
			"Name",
			"Category",
			"Image",
			"Designdata",			
			"Status",			
		);
		$designIdeaModel = Mage::getModel('gallery/gallery');
		$designIdeas = Mage::getModel('gallery/gallery')->getCollection();
		$data = array();
		foreach ($_columns as $column) {
			$data[] = '"'.$column.'"';
		}
		$csv .= implode(',', $data)."\n";
		#prepare data
		foreach($designIdeas as $designIdea){ 
			$idea = $designIdeaModel->load($designIdea->getGalleryId());
			$data = array();
			//$data['gallery_id'] = $idea->getGalleryId();
			$data['title'] = $idea->getTitle();
			$data['album_id'] = $idea->getAlbumId();
			$data['filename'] = $idea->getFilename();
			$data['designdata'] = $idea->getDesigndata();			
			$data['status'] = $idea->getStatus();	
			$csv .= implode(',', $data)."\n";
		}
		//now $csv varaible has csv data as string

		$this->_prepareDownloadResponse($fileName, $csv); 
}
	
	
}
