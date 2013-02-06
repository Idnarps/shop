	<tr><td colspan="<?=count($this->_columns)?>" >
		<span class='txt1'>Показано записей c <b><?=($this->_curPage)*$this->_rpp+1?></b> по <b><?=($this->_curPage)*$this->_rpp+count($this->_rows)?></b> из <b><?=$this->_recCnt?></b></span>
		<div style='float:right;margin-right:20px;'><?=$pages?></div>
	</td></tr>
