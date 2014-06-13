<form><input name="end">
</form>
<?
if (isset($_GET['end']))
{$end=$_GET['end'];}
else {$end=7;}

	for ($i=1; $i<=$end-7; $i+=7){
		echo $i.'<br>';
	} 
echo'<br>-->';
echo $end-$i;
?>