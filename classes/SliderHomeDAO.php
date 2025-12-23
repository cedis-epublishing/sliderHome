<?php
/**
 * @file plugins/generic/sliderContent/classes/SliderContentDAO.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief File implemeting the slider DAO object.
 */

namespace APP\plugins\generic\sliderHome\classes;

use PKP\db\SchemaDAO;
use Illuminate\Support\Facades\DB;
use PKP\db\DAOResultFactory;
use APP\plugins\generic\sliderHome\classes\SliderContent;


 /**
 * @class SliderContentDAO
 * @brief Class implemeting the slider DAO object.
 */
class SliderHomeDAO extends SchemaDAO {

	public $schema = 'sliderHome';
	public $schemaName = 'sliderHome';
    public $tableName = 'slider';
    public $settingsTableName = 'slider_settings';
    public $primaryKeyColumn = 'slider_content_id';
    public $primaryTableColumns = [
		'id' => 'slider_content_id',
        'contextId' => 'context_id',
        'sequence' => 'sequence',
        'show_content' => 'show_content'
    ];

	function __construct() {
		parent::__construct();
	}

	function getById($sliderContentId, $contextId = null): ?SliderContent {
		$row = DB::table('slider')
		->where('slider_content_id', $sliderContentId)
		->where('context_id', $contextId)
		->get()[0];

		return $row ? $this->_fromRow((array) $row) : null;
	}

	function getAllContent($contextId, $locale): array {

		$rows = DB::table('slider')
		->where('context_id', $contextId)
		->where('show_content', true)
		->leftjoin('slider_settings', 'slider_settings.slider_content_id', '=', 'slider.slider_content_id')
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

	function getByContextId($contextId, $rangeInfo = null): DAOResultFactory {
		$result = DB::table('slider')
		->where('context_id', $contextId)
		->orderBy('sequence')	
		->get();
		return new DAOResultFactory($result, $this, '_fromRow');
	}

	function getMaxSequence($contextId): int {
		return DB::table('slider')
		->where('context_id', $contextId)
		->max('sequence') || 0;
	}
	
	function deleteById(int $sliderContentId): int {
		return DB::table('slider')
		->where('slider_content_id', $sliderContentId)
		->delete() && DB::table('slider_settings')
		->where('slider_content_id', $sliderContentId)
		->delete();
	}

	function deleteObject($sliderContent): int {
		return $this->deleteById($sliderContent->getId());
	}

	function newDataObject(): SliderContent {
		return new SliderContent();
	}

	function getSliderSettings($sliderContent): array {
		return [
			'name' =>  $sliderContent->getName(),
			'content' => $sliderContent->getContent(),
			'copyright' => $sliderContent->getCopyright(),
			'sliderImage' => $sliderContent->getSliderImage(),
			'sliderImageAltText' =>  $sliderContent->getSliderImageAltText()
		];
	}
}

?>
