<?php
/**
 * @file plugins/generic/sliderContent/classes/SliderContentDAO.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief File implemeting the slider DAO object.
 */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.sliderHome.classes.SliderContent');

use Illuminate\Support\Facades\DB;
use PKP\db\DAOResultFactory;

 /**
 * @class SliderContentDAO
 * @brief Class implemeting the slider DAO object.
 */
class SliderHomeDAO extends DAO {

	function __construct() {
		parent::__construct();
	}

	function getById($sliderContentId, $contextId = null) {
		$row = DB::table('slider')
		->where('slider_content_id', $sliderContentId)
		->where('context_id', $contextId)
		->get()[0];

		return $row ? $this->_fromRow((array) $row) : null;
	}

	function getAllContent($contextId, $locale) {

		$rows = DB::table('slider')
		->where('context_id', $contextId)
		->where('show_content', true)
		->join('slider_settings', 'slider_settings.slider_content_id', '=', 'slider.slider_content_id')
		->whereIn('locale', [$locale,''])
		->orderBy('sequence')
		->get()
		->groupby('slider_content_id');

		$result = [];
		foreach ($rows as $group) {
			$data = [];
			foreach ($group as $row) {
				$data = array_merge($data, [
					$row->setting_name => $row->setting_value,
				]);
			}
			$result[] = $data;
		}
		
		return $result;
	}

	function getByContextId($contextId, $rangeInfo = null) {
		$result = DB::table('slider')
		->where('context_id', $contextId)
		->orderBy('sequence')	
		->get();
		return new DAOResultFactory($result, $this, '_fromRow');
	}

	function getMaxSequence($contextId) {
		return DB::table('slider')
		->where('context_id', $contextId)
		->max('sequence');
	}

	function insertObject($sliderContent) {	
		DB::table('slider')->insert([
			'context_id' => (int) $sliderContent->getContextId(),
			'sequence' => $sliderContent->getSequence(),
			'show_content' => $sliderContent->getShowContent()
		]);

		$sliderContent->setId($this->getInsertId());

		$this->updateDataObjectSettings('slider_settings', $sliderContent, [
			'slider_content_id' => $sliderContent->getId()
		]);		

		return $sliderContent->getId();
	}

	function updateObject($sliderContent) {
		DB::table('slider')
		->where('slider_content_id', $sliderContent->getId())
		->update([
			'context_id' => (int) $sliderContent->getContextId(),
			'sequence' => $sliderContent->getSequence(),
			'show_content' => $sliderContent->getShowContent()
		]);

		$this->updateDataObjectSettings('slider_settings', $sliderContent, [
			'slider_content_id' => $sliderContent->getId()
		]);
	}
	
	function deleteById($sliderContentId) {
		DB::table('slider')
		->where('slider_content_id', $sliderContentId)
		->delete();
		DB::table('slider_settings')
		->where('slider_content_id', $sliderContentId)
		->delete();
	}

	function deleteObject($sliderContent) {
		$this->deleteById($sliderContent->getId());
	}

	function newDataObject() {
		return new SliderContent();
	}

	function _fromRow($row) {
		$sliderContent = $this->newDataObject();
		$sliderContent->setId($row['slider_content_id']);
		$sliderContent->setContextId($row['context_id']);
		$sliderContent->setSequence($row['sequence']);
		$sliderContent->setShowContent($row['show_content']);

		$this->getDataObjectSettings('slider_settings', 'slider_content_id', $row['slider_content_id'], $sliderContent);

		return $sliderContent;
	}

	function getSliderSettings($sliderContent) {
		return [
			'name' =>  $sliderContent->getName(),
			'content' => $sliderContent->getContent(),
			'copyright' => $sliderContent->getCopyright(),
			'sliderImage' => $sliderContent->getSliderImage(),
			'sliderImageAltText' =>  $sliderContent->getSliderImageAltText()
		];
	}

	function getInsertId():int {
		return $this->_getInsertId('slider', 'slider_content_id');
	}

	/**
	 * Get field names for which data is localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return ['copyright','content','sliderImageAltText'];
	}

	/**
	 * @copydoc DAO::getAdditionalFieldNames()
	 */
	function getAdditionalFieldNames() {
		return array_merge(parent::getAdditionalFieldNames(), ['name','sliderImage','sliderImageLink']);
	}

}

?>
