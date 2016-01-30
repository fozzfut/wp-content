<?php
class tablesControllerPts extends controllerPts {
	public function createFromTpl() {
		$res = new responsePts();
		if(($id = $this->getModel()->createFromTpl(reqPts::get('post'))) != false) {
			$res->addMessage(__('Done', PTS_LANG_CODE));
			$res->addData('edit_link', $this->getModule()->getEditLink( $id ));
		} else
			$res->pushError ($this->getModel()->getErrors());
		return $res->ajaxExec();
	}
	protected function _prepareListForTbl($data) {
		if(!empty($data)) {
			foreach($data as $i => $v) {
				$data[ $i ]['label'] = '<a class="" href="'. $this->getModule()->getEditLink($data[ $i ]['id']). '">'. $data[ $i ]['label']. '&nbsp;<i class="fa fa-fw fa-pencil" style="margin-top: 2px;"></i></a>';
			}
		}
		return $data;
	}
	protected function _prepareModelBeforeListSelect($model) {
		$where = 'original_id != 0';
		$model->addWhere( $where );
		return $model;
	}
	protected function _prepareTextLikeSearch($val) {
		$query = '(label LIKE "%'. $val. '%"';
		if(is_numeric($val)) {
			$query .= ' OR id LIKE "%'. (int) $val. '%"';
		}
		$query .= ')';
		return $query;
	}
	public function remove() {
		$res = new responsePts();
		if($this->getModel()->remove(reqPts::getVar('id', 'post'))) {
			$res->addMessage(__('Done', PTS_LANG_CODE));
		} else
			$res->pushError($this->getModel()->getErrors());
		$res->ajaxExec();
	}
	public function save() {
		$res = new responsePts();
		$data = reqPts::getVar('data', 'post');
		if($this->getModel()->save( $data )) {
			$res->addMessage(__('Done', PTS_LANG_CODE));
		} else
			$res->pushError($this->getModel()->getErrors());
		$res->ajaxExec();
	}
	public function changeTpl() {
		$res = new responsePts();
		if($this->getModel()->changeTpl(reqPts::get('post'))) {
			$res->addMessage(__('Done', PTS_LANG_CODE));
			$id = (int) reqPts::getVar('id', 'post');
			$res->addData('edit_link', $this->getModule()->getEditLink( $id ));
		} else
			$res->pushError ($this->getModel()->getErrors());
		return $res->ajaxExec();
	}
	public function exportForDb() {
		$forPro = (int) reqPts::getVar('for_pro', 'get');
		$tblsCols = array(
			'@__tables' => array('unique_id','label','original_id','params','html','css','img','sort_order','is_base','date_created','is_pro'),
		);
		if($forPro) {
			echo 'db_install=>';
			foreach($tblsCols as $tbl => $cols) {
				echo $this->_makeExportQueriesLogicForPro($tbl, $cols);
			}
		} else {
			foreach($tblsCols as $tbl => $cols) {
				echo $this->_makeExportQueriesLogic($tbl, $cols);
			}
		}
		exit();
	}
	private function _makeExportQueriesLogicForPro($table, $cols) {
		$octoList = $this->_getExportData($table, $cols, true);
		$res = array();

		foreach($octoList as $octo) {
			$uId = '';
			$rowData = array();
			foreach($octo as $k => $v) {
				if(!in_array($k, $cols)) continue;
				$val = mysql_real_escape_string($v);
				if($k == 'unique_id') $uId = $val;
				$rowData[ $k ] = $val;

			}
			$res[ $uId ] = $rowData;
		}
		echo str_replace(array('@__'), '', $table). '|'. base64_encode( utilsPts::serialize($res) );
	}
	private function _getExportData($table, $cols, $forPro = false) {
		return dbPts::get('SELECT '. implode(',', $cols). ' FROM '. $table. ' WHERE original_id = 0 and is_base = 1 and is_pro = '. ($forPro ? '1' : '0'));;
	}
	/**
	 * new usage
	 */
	private function _makeExportQueriesLogic($table, $cols) {
		$eol = "\r\n";
		$octoList = $this->_getExportData($table, $cols);
		$valuesArr = array();
		$allKeys = array();
		$uidIndx = 0;
		$i = 0;
		foreach($octoList as $octo) {
			$arr = array();
			$addToKeys = empty($allKeys);
			$i = 0;
			foreach($octo as $k => $v) {
				if(!in_array($k, $cols)) continue;
				if($addToKeys) {
					$allKeys[] = $k;
					if($k == 'unique_id') {
						$uidIndx = $i;
					}
				}
				$arr[] = ''. mysql_real_escape_string($v). '';
				$i++;
			}
			$valuesArr[] = $arr;
		}
		$out = '';
		//$out .= "\$cols = array('". implode("','", $allKeys). "');". $eol;
		$out .= "\$data = array(". $eol;
		foreach($valuesArr as $row) {
			$uid = str_replace(array('"'), '', $row[ $uidIndx ]);
			$installData = array();
			foreach($row as $i => $v) {
				$installData[] = "'{$allKeys[ $i ]}' => '{$v}'";
			}
			$out .= "'$uid' => array(". implode(',', $installData). "),". $eol;
		}
		$out .= ");". $eol;
		return $out;
	}
	public function saveAsCopy() {
		$res = new responsePts();
		if(($id = $this->getModel()->saveAsCopy(reqPts::get('post'))) != false) {
			$res->addMessage(__('Done, redirecting to new Table...', PTS_LANG_CODE));
			$res->addData('edit_link', $this->getModule()->getEditLink( $id ));
		} else
			$res->pushError ($this->getModel()->getErrors());
		return $res->ajaxExec();
	}
	public function updateLabel() {
		$res = new responsePts();
		if($this->getModel()->updateLabel(reqPts::get('post'))) {
			$res->addMessage(__('Done', PTS_LANG_CODE));
		} else
			$res->pushError ($this->getModel()->getErrors());
		return $res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			PTS_USERLEVELS => array(
				PTS_ADMIN => array('getListForTbl', 'remove', 'removeGroup', 'clear', 
					'save', 'exportForDb', 'updateLabel', 'changeTpl', 'saveAsCopy')
			),
		);
	}
}

