<?php
/**
 * WhispyForum placeholder index file
 */

include("includes/load.php"); // Load webpage

if ( FREEUNI_PHASE == 1 )
{
	// If we're in the first phase
	// Give performer status info and info box
	
	$Ctemplate->useStaticTemplate("freeuni/index_perf_status_definition", FALSE); // Give definitions about different performer statuses
	
	$num_students = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users"));
	$num_performers = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM fu_performers"));
	
	$pending = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM fu_performers WHERE status='pending'"));
	$will_come = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM fu_performers WHERE status='agreed'"));
	$wont_come = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM fu_performers WHERE status='refused'"));
	$unallocated = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM fu_performers WHERE status='unallocated'"));
	
	$Ctemplate->useTemplate("freeuni/index_statistics", array(
		'NUM_STUDENTS'	=>	$num_students[0], // Number of students
		'NUM_PERFORMERS'	=>	$num_performers[0], // Number of performes (total)
		'PENDING'	=>	$pending[0], // Performers pending (waiting for response)
		'WILL_COME'	=>	$will_come[0], // Performers agreed
		'WONT_COME'	=>	$wont_come[0], // Performers rejected
		'UNALLOCATED'	=>	$unallocated[0], // Number of performers waiting to be allocated to a student
		'WIDTH'	=>	"100%" // Box width 
	), FALSE);
}

$Ctemplate->useStaticTemplate("wf_fu_info_begin", FALSE); // Free university information (beginning)

if ( FREEUNI_PHASE == 1 )
{
	$Ctemplate->useStaticTemplate("wf_fu_info_phase_1", FALSE); // Free university information (for phase one)
}

if ( FREEUNI_PHASE == 2 )
{
	$Ctemplate->useStaticTemplate("wf_fu_info_phase_2", FALSE); // Free university information (for phase two)
}

$Ctemplate->useStaticTemplate("wf_fu_info_end", FALSE); // Free university information (ending)
DoFooter();
?>