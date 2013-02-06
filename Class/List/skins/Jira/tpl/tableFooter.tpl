<?if($this->_paginal)echo $this->_showPages();?>
</table>
<script>
	function check_all(sender, name)
	{
		var arr = document.getElementsByName(name+"[]");
		for(var i=0;i<arr.length;i++)
		{
		  if (!arr[i].disabled) {
			  arr[i].checked=sender.checked;
		  }
		}
	}
</script>