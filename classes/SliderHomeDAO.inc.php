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

 /**
 * @class SliderContentDAO
 * @brief Class implemeting the slider DAO object.
 */
class SliderHomeDAO extends DAO {

	function __construct() {
		parent::__construct();
	}

	function getById($sliderContentId, $contextId = null) {
		$params = array((int) $sliderContentId);
		if ($contextId) $params[] = $contextId;

		$result = $this->retrieve(
			'SELECT * FROM slider WHERE slider_content_id = ?'
			. ($contextId?' AND context_id = ?':''),
			$params
		);

		$row = $result->current();
		return $row ? $this->_fromRow((array) $row) : null;
	}

	function getAllContent($contextId) {

		$result = $this->retrieve(
			'SELECT content, copyright, sliderImage, sliderImageAltText FROM slider WHERE context_id ='.$contextId . ' and show_content=1 ORDER BY sequence'
		);
		return iterator_to_array($result);
	}

	function getByContextId($contextId, $rangeInfo = null) {
		$result = $this->retrieveRange(
			'SELECT * FROM slider WHERE context_id = ? ORDER BY sequence',
			(array) $contextId,
			$rangeInfo
		);
		return new DAOResultFactory($result, $this, '_fromRow');
	}

	function getMaxSequence($contextId) {

		$result = $this->retrieve(
			'SELECT MAX(sequence) as maxseq FROM slider WHERE context_id ='.$contextId
		);

		$row = $result->current();
		return $row->maxseq;
	}

	function insertObject($sliderContent) {	
		$this->update(
			'INSERT INTO slider (context_id, name, content, sequence, show_content, copyright, sliderImage, sliderImageAltText)
			VALUES (?,?,?,?,?,?,?,?)',
			array(
				(int) $sliderContent->getContextId(),
				$sliderContent->getName(),
				$sliderContent->getContent(),
				$sliderContent->getSequence(),
				$sliderContent->getShowContent(),
				$sliderContent->getCopyright(),
				$sliderContent->getSliderImage(),
				$sliderContent->getSliderImageAltText()	
			)
		);
		$sliderContent->setId($this->getInsertId());
		return $sliderContent->getId();
	}

	function updateObject($sliderContent) {
		$this->update(
			'UPDATE	slider
			SET	context_id = ?,
				name = ?,
				content = ?,
				sequence = ?,
				show_content = ?,
				copyright = ?,
				sliderImage = ?,
				sliderImageAltText = ?
			WHERE slider_content_id = ?',
			array(
				(int) $sliderContent->getContextId(),
				$sliderContent->getName(),
				$sliderContent->getContent(),
				$sliderContent->getSequence(),
				(int) $sliderContent->getShowContent(),	
				$sliderContent->getCopyright(),
				$sliderContent->getSliderImage(),
				$sliderContent->getSliderImageAltText(),			
				(int) $sliderContent->getId()
			)
		);
	}
	
	function deleteById($sliderContentId) {
		$this->update(
			'DELETE FROM slider WHERE slider_content_id = ?',
			(array) $sliderContentId
		);
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
		$sliderContent->setName($row['name']);
		$sliderContent->setContent($row['content']);
		$sliderContent->setCopyright($row['copyright']);
		$sliderContent->setContextId($row['context_id']);
		$sliderContent->setSequence($row['sequence']);
		$sliderContent->setShowContent($row['show_content']);
		$sliderContent->setSliderImage($row['sliderImage']);
		$sliderContent->setSliderImageAltText($row['sliderImageAltText']);
		return $sliderContent;
	}

	function getInsertId() {
		return $this->_getInsertId('slider', 'slider_content_id');
	}

}

?>
