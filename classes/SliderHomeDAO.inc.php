<?php

/**
 * @file plugins/generic/sliderContent/classes/SliderContentDAO.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SliderContentDAO
 */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.sliderHome.classes.SliderContent');

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

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	function getAllContent($contextId) {

		$result = $this->retrieve(
			'SELECT content FROM slider WHERE context_id ='.$contextId . ' and show_content=1 ORDER BY sequence'
		);
		$sliderContent = array();
		if ($result->RecordCount() == 0) {
			$result->Close();
		} else {			
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$sliderContent[]= $this->convertFromDB($row['content'],null);
				$result->MoveNext();
			}
			$result->Close();
		}
		return $sliderContent;
	}

	function getByContextId($contextId, $rangeInfo = null) {
		$result = $this->retrieveRange(
			'SELECT * FROM slider WHERE context_id = ? ORDER BY sequence',
			(int) $contextId,
			$rangeInfo
		);
		return new DAOResultFactory($result, $this, '_fromRow');
	}

	function getMaxSequence($contextId) {

		$result = $this->retrieve(
			'SELECT MAX(sequence) as maxseq FROM slider WHERE context_id ='.$contextId
		);

		if ($result->RecordCount() == 0) {
			$result->Close();
			return 0;
		} else {
			$row = $result->getRowAssoc(false);
			return $this->convertFromDB($row['maxseq'],null);
		}
	}

	function insertObject($sliderContent) {

$myfile = 'test.txt';
$newContentCF5344 = print_r($sliderContent->getShowContent(), true);
$contentCF2343 = file_get_contents($myfile);
$contentCF2343 .= "\n insert: " . $newContentCF5344 ;
file_put_contents($myfile, $contentCF2343 );
	
		$this->update(
			'INSERT INTO slider (context_id, name, content, sequence, show_content)
			VALUES (?,?,?,?,?)',
			array(
				(int) $sliderContent->getContextId(),
				$sliderContent->getName(),
				$sliderContent->getContent(),
				$sliderContent->getSequence(),
				$sliderContent->getShowContent()				
			)
		);
		$sliderContent->setId($this->getInsertId());
		return $sliderContent->getId();
	}

	function updateObject($sliderContent) {
$myfile = 'test.txt';
$newContentCF5344 = print_r((int)$sliderContent->getShowContent(), true);
$contentCF2343 = file_get_contents($myfile);
$contentCF2343 .= "\n update: " . $newContentCF5344 ;
file_put_contents($myfile, $contentCF2343 );
		$this->update(
			'UPDATE	slider
			SET	context_id = ?,
				name = ?,
				content = ?,
				sequence = ?,
				show_content = ?
			WHERE slider_content_id = ?',
			array(
				(int) $sliderContent->getContextId(),
				$sliderContent->getName(),
				$sliderContent->getContent(),
				$sliderContent->getSequence(),
				(int)$sliderContent->getShowContent(),				
				(int) $sliderContent->getId()
			)
		);
	}
	
	function deleteById($sliderContentId) {
		$this->update(
			'DELETE FROM langsci_slider_content WHERE slider_content_id = ?',
			(int) $sliderContentId
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
		$sliderContent->setContextId($row['context_id']);
		$sliderContent->setSequence($row['sequence']);
		$sliderContent->setShowContent($row['show_content']);		
		return $sliderContent;
	}

	function getInsertId() {
		return $this->_getInsertId('slider', 'slider_content_id');
	}

}

?>
