<?php
 /**
 * WhispyForum tool - template tester
 * 
 * Helps template desinging and debugging.
 * 
 * WhispyForum
 */

 include('includes/load.php'); // We load the environment as usual
 
 if ( isset($_POST['mode']) )
 {
 	// If we get the mode variable from the frame
 	// we run template testing.
 	echo '<a href="tool.template-test.php">Back to forms</a>';
 	
 	echo '<h3>Input was:</h3>';
 	// Basically we give the previous form (below after } else {) with the set data.
 	print('Template name: ' .$_POST['tname']. '<br>
 	<table border="1">
		<tr>
			<td>Variable (<tt>{VARIABLE}</tt> in template)</td>

			<td>Value (value to be shown where <tt>{VARIABLE}</tt> is in template file)</td>
		</tr>
		<tr>
			<td>' .$_POST['variable0']. '</td>
			<td>' .$_POST['value0']. '</td>
		</tr>
		<tr>
			<td>' .$_POST['variable1']. '</td>
			<td>' .$_POST['value1']. '</td>
		</tr>
		<tr>
			<td>' .$_POST['variable2']. '</td>
			<td>' .$_POST['value2']. '</td>
		</tr>
		<tr>
			<td>' .$_POST['variable3']. '</td>
			<td>' .$_POST['value3']. '</td>
		</tr>
		<tr>
			<td>' .$_POST['variable4']. '</td>
			<td>' .$_POST['value4']. '</td>
		</tr>
		<tr>
			<td>' .$_POST['variable5']. '</td>
			<td>' .$_POST['value5']. '</td>
		</tr>
	</table>');
	
	// We generate the template (with replacing)
	echo '<hr><h3>Template <tt>' .$_POST['tname']. '</tt> parse mode (with replacing)</h3>';
	$Ctemplate->useTemplate($_POST['tname'], array(
		$_POST['variable0']	=>	$_POST['value0'],
		$_POST['variable1']	=>	$_POST['value1'],
		$_POST['variable2']	=>	$_POST['value2'],
		$_POST['variable3']	=>	$_POST['value3'],
		$_POST['variable4']	=>	$_POST['value4'],
		$_POST['variable5']	=>	$_POST['value5'],
		), FALSE);
	
	// We generate the template (static mode)
	echo '<hr><h3>Template <tt>' .$_POST['tname']. '</tt> static mode (without replacing)</h3><tt>';
	$Ctemplate->useStaticTemplate($_POST['tname'], FALSE);
	echo '</tt>';
 } else {
 	// If not, we output a form (using time() and md5() to generate token for $_POST['mode']
	?>
	<h3>Specify template name and other values to generate custom template</h3>
	<!--- Modifiable form --->
	<form method="POST" action="tool.template-test.php"> <!--- form header -->
	Template name: <input type="text" name="tname" value="" size="40"><br> <!--- template name -->
	
	<!--- Template variable - value table -->
	<table border="1">
		<tr>
			<td>Variable (<tt>{VARIABLE}</tt> in template)</td>
			<td>Value (value to be shown where <tt>{VARIABLE}</tt> is in template file)</td>
		</tr>
		<?php
			// Generate amount of variables and values with for()
			for ($i = 0; $i <= 5; $i++)
			{
				print('<tr>
					<td><input type="text" name="variable' .$i. '" size="35"></td>
					<td><input type="text" name="value' .$i. '" size="35"></td>
				</tr>');
			}
		?>
	</table>
	<!--- End table -->
	
	<input type="hidden" name="mode" <? echo 'value="' .md5(time()). '"'; ?> > <!--- mode with generated token -->

	<br>
	<input type="submit" value="Test!"> <!--- submit -->
	</form>
	<!--- End form -->
	
	<h3>Or you can use the example template with example data.</h3>
	<!--- Example template example data template test form -->
	<form method="POST" action="tool.template-test.php"> <!--- form header -->
	Template name: example_template<input type="hidden" name="tname" value="example_template"><br> <!--- template name -->
	<!--- Template variable - value table -->
	<table border="1">
		<tr>
			<td>Variable (<tt>{VARIABLE}</tt> in template)</td>
			<td>Value (value to be shown where <tt>{VARIABLE}</tt> is in template file)</td>
		</tr>
		<tr>
			<td>TITLE<input type="hidden" name="variable0" value="TITLE"></td>
			<td>Example template<input type="hidden" name="value0" value="Example template"></td>
		</tr>
		<tr>
			<td>SITENAME<input type="hidden" name="variable1" value="SITENAME"></td>
			<td>WhispyForum template tester<input type="hidden" name="value1" value="WhispyForum template tester"></td>
		</tr>
		<tr>
			<td>WORKING_COLOR<input type="hidden" name="variable2" value="WORKING_COLOR"></td>
			<td>#AB1852<input type="hidden" name="value2" value="#AB1852"></td>
		</tr>
		<tr>
			<td>WEIGHT<input type="hidden" name="variable3" value="WEIGHT"></td>
			<td>bold<input type="hidden" name="value3" value="bold"></td>
		</tr>
	</table>
	<!--- We must add NULL variables and values to prevent errors -->
	<input type="hidden" name="variable4" value="">
	<input type="hidden" name="value4" value="">
	
	<input type="hidden" name="variable5" value="">
	<input type="hidden" name="value5" value="">
	
	<!--- End table -->
	<input type="hidden" name="mode" value="example"> <!--- mode with static token -->

	<br>
	<input type="submit" value="Test example!"> <!--- submit -->
	</form>
	<!--- End form -->
	<?php
 }
 
 DoFooter();
?>